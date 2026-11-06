<?php

namespace App\Support;

use App\Models\ProductDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Webkul\Attribute\Repositories\AttributeOptionRepository;
use Webkul\Product\Helpers\View as ProductViewHelper;
use Webkul\Product\Models\Product;

class ProductDocumentationManager
{
    public const PRIVATE_DISK = 'private';

    public const STORAGE_DIRECTORY = 'product_documents';

    public const MAX_FILE_SIZE_KB = 10240; // 10 MB

    public const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    public const ALLOWED_EXTENSIONS = [
        'pdf',
        'jpg',
        'jpeg',
        'png',
        'webp',
    ];

    /**
     * Store a new product document and save its physical file on private storage.
     */
    public static function storeDocument(array $data, UploadedFile $file): ProductDocument
    {
        self::validateFile($file);

        $storedPath = self::storeFile($file);

        return ProductDocument::create([
            'product_id'        => (int) $data['product_id'],
            'title'             => trim($data['title']),
            'document_type'     => $data['document_type'],
            'description'       => ! empty($data['description']) ? trim($data['description']) : null,
            'file_path'         => $storedPath,
            'original_filename' => self::sanitizeOriginalFilename($file->getClientOriginalName()),
            'mime_type'         => $file->getMimeType() ?: 'application/octet-stream',
            'file_size'         => $file->getSize(),
            'version'           => ! empty($data['version']) ? trim($data['version']) : null,
            'issue_date'        => ! empty($data['issue_date']) ? $data['issue_date'] : null,
            'expiry_date'       => ! empty($data['expiry_date']) ? $data['expiry_date'] : null,
            'status'            => $data['status'] ?? ProductDocument::STATUS_PUBLISHED,
            'sort_order'        => isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
        ]);
    }

    /**
     * Update an existing product document, optionally replacing the stored physical file.
     */
    public static function updateDocument(ProductDocument $document, array $data, ?UploadedFile $newFile = null): ProductDocument
    {
        $updateData = [
            'product_id'    => (int) ($data['product_id'] ?? $document->product_id),
            'title'         => trim($data['title']),
            'document_type' => $data['document_type'],
            'description'   => ! empty($data['description']) ? trim($data['description']) : null,
            'version'       => ! empty($data['version']) ? trim($data['version']) : null,
            'issue_date'    => ! empty($data['issue_date']) ? $data['issue_date'] : null,
            'expiry_date'   => ! empty($data['expiry_date']) ? $data['expiry_date'] : null,
            'status'        => $data['status'] ?? $document->status,
            'sort_order'    => isset($data['sort_order']) ? (int) $data['sort_order'] : $document->sort_order,
        ];

        if ($newFile instanceof UploadedFile) {
            self::validateFile($newFile);

            // Safely delete old physical file
            self::deletePhysicalFile($document->file_path);

            // Store new file
            $newStoredPath = self::storeFile($newFile);

            $updateData['file_path'] = $newStoredPath;
            $updateData['original_filename'] = self::sanitizeOriginalFilename($newFile->getClientOriginalName());
            $updateData['mime_type'] = $newFile->getMimeType() ?: 'application/octet-stream';
            $updateData['file_size'] = $newFile->getSize();
        }

        $document->update($updateData);

        return $document->fresh();
    }

    /**
     * Delete a product document and safely remove its physical file from private storage.
     */
    public static function deleteDocument(ProductDocument $document): bool
    {
        self::deletePhysicalFile($document->file_path);

        return (bool) $document->delete();
    }

    /**
     * Stream or download a document from private storage with security response headers.
     */
    public static function streamDocument(ProductDocument $document, bool $asDownload = false): StreamedResponse
    {
        $disk = Storage::disk(self::PRIVATE_DISK);

        if (! $disk->exists($document->file_path)) {
            abort(404, 'Document file not found in private storage.');
        }

        $downloadFilename = self::sanitizeOriginalFilename($document->original_filename ?: 'document.pdf');
        $mimeType = $document->mime_type ?: 'application/octet-stream';
        $dispositionType = $asDownload ? 'attachment' : 'inline';

        $headers = [
            'Content-Type'              => $mimeType,
            'Content-Disposition'       => "{$dispositionType}; filename=\"{$downloadFilename}\"",
            'X-Content-Type-Options'    => 'nosniff',
            'Cache-Control'             => 'private, no-cache, no-store, must-revalidate',
            'Pragma'                    => 'no-cache',
            'Expires'                   => '0',
            'Content-Security-Policy'   => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
        ];

        return response()->stream(function () use ($disk, $document) {
            $stream = $disk->readStream($document->file_path);

            if (is_resource($stream)) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, $headers);
    }

    /**
     * Validate an uploaded file for MIME type, extension, and file size.
     */
    public static function validateFile(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new HttpException(422, 'Uploaded document file is invalid or corrupted.');
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        $mimeType = strtolower((string) $file->getMimeType());

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new HttpException(422, "File extension '{$extension}' is not permitted. Permitted formats: PDF, JPG, JPEG, PNG, WEBP.");
        }

        if (! in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new HttpException(422, "Detected file MIME type '{$mimeType}' is not allowed.");
        }

        $sizeKb = $file->getSize() / 1024;
        if ($sizeKb > self::MAX_FILE_SIZE_KB) {
            throw new HttpException(422, 'Document exceeds the maximum allowed file size of 10 MB.');
        }
    }

    /**
     * Store file securely on the private disk using a randomly generated unique filename.
     */
    protected static function storeFile(UploadedFile $file): string
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $safeFilename = Str::uuid()->toString() . '.' . $extension;

        $path = Storage::disk(self::PRIVATE_DISK)->putFileAs(
            self::STORAGE_DIRECTORY,
            $file,
            $safeFilename
        );

        if (! $path) {
            throw new \RuntimeException('Failed to store document file on private storage disk.');
        }

        return $path;
    }

    /**
     * Delete a physical file safely from the private disk.
     */
    public static function deletePhysicalFile(?string $filePath): void
    {
        if (empty($filePath)) {
            return;
        }

        $disk = Storage::disk(self::PRIVATE_DISK);

        if ($disk->exists($filePath)) {
            $disk->delete($filePath);
        }
    }

    /**
     * Sanitize original filename to prevent header injection or directory traversal.
     */
    public static function sanitizeOriginalFilename(string $filename): string
    {
        $clean = basename($filename);
        $clean = preg_replace('/[^\w\.\-\(\)\s]/u', '_', $clean);

        return $clean ?: 'document.pdf';
    }

    /**
     * Get clean, structured product specifications for member PDP display.
     * Reuses native Bagisto product attributes and skips empty or hidden pricing fields.
     */
    public static function getProductSpecifications(Product $product): Collection
    {
        $specs = collect();

        // 1. SKU
        if (filled($product->sku)) {
            $specs->push([
                'code'  => 'sku',
                'label' => 'SKU / Product Code',
                'value' => $product->sku,
            ]);
        }

        // 2. Product Number (if configured)
        if (filled($product->product_number)) {
            $specs->push([
                'code'  => 'product_number',
                'label' => 'Product Number',
                'value' => $product->product_number,
            ]);
        }

        // 3. Physical Weight
        if (filled($product->weight) && (float) $product->weight > 0) {
            $weightVal = (float) $product->weight;
            $formattedWeight = rtrim(rtrim(number_format($weightVal, 3), '0'), '.');
            $specs->push([
                'code'  => 'weight',
                'label' => 'Net Weight',
                'value' => $formattedWeight . ' kg',
            ]);
        }

        // 4. Dimensions (Length, Width, Height)
        $hasDimensions = filled($product->length) || filled($product->width) || filled($product->height);
        if ($hasDimensions) {
            $dimParts = [];
            if (filled($product->length)) {
                $dimParts[] = 'L: ' . rtrim(rtrim(number_format((float) $product->length, 2), '0'), '.') . ' cm';
            }
            if (filled($product->width)) {
                $dimParts[] = 'W: ' . rtrim(rtrim(number_format((float) $product->width, 2), '0'), '.') . ' cm';
            }
            if (filled($product->height)) {
                $dimParts[] = 'H: ' . rtrim(rtrim(number_format((float) $product->height, 2), '0'), '.') . ' cm';
            }

            if (! empty($dimParts)) {
                $specs->push([
                    'code'  => 'dimensions',
                    'label' => 'Package Dimensions',
                    'value' => implode(' × ', $dimParts),
                ]);
            }
        }

        // 5. Resolving Brand, Color, Size if present
        $optionRepo = app(AttributeOptionRepository::class);

        if (filled($product->brand)) {
            $brandOption = is_numeric($product->brand) ? $optionRepo->find($product->brand) : null;
            $brandVal = $brandOption ? ($brandOption->label ?: $brandOption->admin_name) : $product->brand;
            if (filled($brandVal)) {
                $specs->push([
                    'code'  => 'brand',
                    'label' => 'Brand / Origin',
                    'value' => $brandVal,
                ]);
            }
        }

        if (filled($product->color)) {
            $colorOption = is_numeric($product->color) ? $optionRepo->find($product->color) : null;
            $colorVal = $colorOption ? ($colorOption->label ?: $colorOption->admin_name) : $product->color;
            if (filled($colorVal)) {
                $specs->push([
                    'code'  => 'color',
                    'label' => 'Color / Appearance',
                    'value' => $colorVal,
                ]);
            }
        }

        if (filled($product->size)) {
            $sizeOption = is_numeric($product->size) ? $optionRepo->find($product->size) : null;
            $sizeVal = $sizeOption ? ($sizeOption->label ?: $sizeOption->admin_name) : $product->size;
            if (filled($sizeVal)) {
                $specs->push([
                    'code'  => 'size',
                    'label' => 'Pack Size / Grade',
                    'value' => $sizeVal,
                ]);
            }
        }

        // 6. Custom front-facing attributes from Bagisto ProductViewHelper
        try {
            $viewHelper = app(ProductViewHelper::class);
            $customData = (array) $viewHelper->getAdditionalData($product);

            $alreadyIncludedCodes = $specs->pluck('code')->all();
            $hiddenCodes = WholesalePricingManager::HIDDEN_SPEC_ATTRIBUTE_CODES;

            foreach ($customData as $item) {
                $code = $item['code'] ?? '';
                $val = $item['value'] ?? null;

                if (
                    empty($val)
                    || in_array($code, $alreadyIncludedCodes, true)
                    || in_array($code, $hiddenCodes, true)
                ) {
                    continue;
                }

                $specs->push([
                    'code'  => $code,
                    'label' => $item['label'] ?? ucfirst($code),
                    'value' => $val,
                ]);
            }
        } catch (\Throwable $e) {
            // Graceful fallback if helper fails
        }

        return $specs;
    }

    /**
     * Retrieve published documents for a product grouped by category.
     */
    public static function getGroupedProductDocuments(int $productId): array
    {
        $documents = ProductDocument::query()
            ->forProduct($productId)
            ->published()
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $grouped = [];

        foreach (ProductDocument::CATEGORY_GROUPS as $key => $meta) {
            $grouped[$key] = [
                'title'     => $meta['title'],
                'types'     => $meta['types'],
                'documents' => $documents->whereIn('document_type', $meta['types'])->values(),
            ];
        }

        return $grouped;
    }
}


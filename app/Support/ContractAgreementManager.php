<?php

namespace App\Support;

use App\Mail\MemberContractAccepted;
use App\Mail\MemberContractRejected;
use App\Mail\MemberContractSent;
use App\Models\ContractTemplate;
use App\Models\MemberContract;
use App\Models\MemberContractEvent;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Webkul\Customer\Models\Customer;
use Webkul\User\Models\Admin;

class ContractAgreementManager
{
    public const PRIVATE_DISK = 'private';

    public const TEMPLATES_DIRECTORY = 'contracts/templates';

    public const INDIVIDUAL_DIRECTORY = 'contracts/individual';

    public const MAX_FILE_SIZE_KB = 15360; // 15 MB

    public const ALLOWED_EXTENSIONS = [
        'pdf',
        'docx',
        'doc',
    ];

    public const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/msword',
        'application/zip',
        'application/octet-stream',
    ];

    /**
     * Store a new contract template.
     */
    public static function storeTemplate(array $data, ?UploadedFile $file = null, ?Admin $admin = null): ContractTemplate
    {
        $filePath = null;
        $originalFilename = null;
        $mimeType = null;
        $fileSize = null;

        if ($file instanceof UploadedFile) {
            self::validateFile($file);
            $filePath = self::storePhysicalFile($file, self::TEMPLATES_DIRECTORY);
            $originalFilename = self::sanitizeOriginalFilename($file->getClientOriginalName());
            $mimeType = $file->getMimeType() ?: 'application/octet-stream';
            $fileSize = $file->getSize();
        }

        $identifier = !empty($data['identifier'])
            ? Str::slug($data['identifier'], '_')
            : Str::slug($data['title'], '_');

        return ContractTemplate::create([
            'title'             => trim($data['title']),
            'identifier'        => $identifier,
            'category'          => $data['category'] ?? ContractTemplate::CATEGORY_CUSTOM,
            'description'       => !empty($data['description']) ? trim($data['description']) : null,
            'version'           => !empty($data['version']) ? trim($data['version']) : '1.0',
            'original_filename' => $originalFilename,
            'storage_path'      => $filePath,
            'mime_type'         => $mimeType,
            'file_size'         => $fileSize,
            'status'            => $data['status'] ?? ContractTemplate::STATUS_DRAFT,
            'member_accessible' => !empty($data['member_accessible']),
            'effective_date'    => !empty($data['effective_date']) ? $data['effective_date'] : null,
            'created_by'        => $admin?->id,
            'updated_by'        => $admin?->id,
        ]);
    }

    /**
     * Update an existing contract template.
     */
    public static function updateTemplate(ContractTemplate $template, array $data, ?UploadedFile $newFile = null, ?Admin $admin = null): ContractTemplate
    {
        $updateData = [
            'title'             => trim($data['title']),
            'category'          => $data['category'] ?? $template->category,
            'description'       => !empty($data['description']) ? trim($data['description']) : null,
            'version'           => !empty($data['version']) ? trim($data['version']) : $template->version,
            'status'            => $data['status'] ?? $template->status,
            'member_accessible' => isset($data['member_accessible']) ? (bool) $data['member_accessible'] : $template->member_accessible,
            'effective_date'    => !empty($data['effective_date']) ? $data['effective_date'] : null,
            'updated_by'        => $admin?->id,
        ];

        if (!empty($data['identifier'])) {
            $updateData['identifier'] = Str::slug($data['identifier'], '_');
        }

        if ($newFile instanceof UploadedFile) {
            self::validateFile($newFile);

            if ($template->storage_path) {
                self::deletePhysicalFile($template->storage_path);
            }

            $updateData['storage_path'] = self::storePhysicalFile($newFile, self::TEMPLATES_DIRECTORY);
            $updateData['original_filename'] = self::sanitizeOriginalFilename($newFile->getClientOriginalName());
            $updateData['mime_type'] = $newFile->getMimeType() ?: 'application/octet-stream';
            $updateData['file_size'] = $newFile->getSize();
        }

        $template->update($updateData);

        return $template->fresh();
    }

    /**
     * Delete a template and remove its physical file.
     */
    public static function deleteTemplate(ContractTemplate $template): bool
    {
        if ($template->storage_path) {
            self::deletePhysicalFile($template->storage_path);
        }

        return (bool) $template->delete();
    }

    /**
     * Stream or download a template file with strict security response headers.
     */
    public static function streamTemplateFile(ContractTemplate $template, bool $asDownload = true): StreamedResponse
    {
        if (!$template->hasFile()) {
            abort(404, 'No file attached to this contract template.');
        }

        $disk = Storage::disk(self::PRIVATE_DISK);

        if (!$disk->exists($template->storage_path)) {
            abort(404, 'Template file not found in private storage.');
        }

        $filename = self::sanitizeOriginalFilename($template->original_filename ?: ($template->identifier . '.pdf'));
        $mimeType = $template->mime_type ?: 'application/octet-stream';
        $disposition = $asDownload ? 'attachment' : 'inline';

        $headers = [
            'Content-Type'            => $mimeType,
            'Content-Disposition'     => "{$disposition}; filename=\"{$filename}\"",
            'X-Content-Type-Options'  => 'nosniff',
            'Cache-Control'           => 'private, no-cache, no-store, must-revalidate',
            'Pragma'                  => 'no-cache',
            'Expires'                 => '0',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
        ];

        return response()->stream(function () use ($disk, $template) {
            $stream = $disk->readStream($template->storage_path);
            if (is_resource($stream)) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, $headers);
    }

    /**
     * Generate the next sequential reference number: CT-YYYY-XXXXX.
     */
    public static function generateReferenceNumber(?int $year = null): string
    {
        $year = $year ?: (int) date('Y');
        $prefix = "CT-{$year}-";

        return DB::transaction(function () use ($prefix, $year) {
            $latest = MemberContract::where('reference_number', 'LIKE', "{$prefix}%")
                ->lockForUpdate()
                ->orderBy('reference_number', 'desc')
                ->value('reference_number');

            $nextSequence = 1;
            if ($latest && preg_match('/^CT-\d{4}-(\d{5})$/', $latest, $matches)) {
                $nextSequence = ((int) $matches[1]) + 1;
            }

            return sprintf('CT-%d-%05d', $year, $nextSequence);
        });
    }

    /**
     * Create an individual contract.
     */
    public static function createContract(array $data, UploadedFile $file, Admin $admin): MemberContract
    {
        self::validateFile($file);

        $storedPath = self::storePhysicalFile($file, self::INDIVIDUAL_DIRECTORY);
        $originalFilename = self::sanitizeOriginalFilename($file->getClientOriginalName());
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $fileSize = $file->getSize();
        $fileHash = hash_file('sha256', $file->getRealPath());

        $templateVersion = null;
        if (!empty($data['template_id'])) {
            $template = ContractTemplate::find($data['template_id']);
            if ($template) {
                $templateVersion = $template->version;
            }
        }

        $referenceNumber = self::generateReferenceNumber();
        $initialStatus = $data['status'] ?? MemberContract::STATUS_DRAFT;
        $sentAt = ($initialStatus === MemberContract::STATUS_SENT) ? Carbon::now() : null;

        $contract = MemberContract::create([
            'reference_number'  => $referenceNumber,
            'title'             => trim($data['title']),
            'customer_id'       => (int) $data['customer_id'],
            'template_id'       => !empty($data['template_id']) ? (int) $data['template_id'] : null,
            'template_version'  => $templateVersion,
            'original_filename' => $originalFilename,
            'storage_path'      => $storedPath,
            'mime_type'         => $mimeType,
            'file_size'         => $fileSize,
            'file_hash'         => $fileHash,
            'status'            => $initialStatus,
            'issued_date'       => !empty($data['issued_date']) ? $data['issued_date'] : Carbon::today(),
            'effective_date'    => !empty($data['effective_date']) ? $data['effective_date'] : null,
            'expires_at'        => !empty($data['expires_at']) ? Carbon::parse($data['expires_at']) : null,
            'sent_at'           => $sentAt,
            'admin_notes'       => !empty($data['admin_notes']) ? trim($data['admin_notes']) : null,
            'created_by'        => $admin->id,
            'updated_by'        => $admin->id,
        ]);

        // Audit event: Created
        $contract->events()->create([
            'event'       => MemberContractEvent::EVENT_CREATED,
            'actor_type'  => MemberContractEvent::ACTOR_ADMIN,
            'actor_id'    => $admin->id,
            'actor_name'  => $admin->name,
            'actor_email' => $admin->email,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'file_hash'   => $fileHash,
            'notes'       => 'Contract document uploaded and record created.',
            'created_at'  => Carbon::now(),
        ]);

        if ($initialStatus === MemberContract::STATUS_SENT) {
            $contract->events()->create([
                'event'       => MemberContractEvent::EVENT_SENT,
                'actor_type'  => MemberContractEvent::ACTOR_ADMIN,
                'actor_id'    => $admin->id,
                'actor_name'  => $admin->name,
                'actor_email' => $admin->email,
                'ip_address'  => request()->ip(),
                'user_agent'  => request()->userAgent(),
                'file_hash'   => $fileHash,
                'notes'       => 'Contract issued and sent to member for review.',
                'created_at'  => Carbon::now(),
            ]);

            self::sendContractNotification($contract);
        }

        return $contract->fresh(['customer', 'template', 'events']);
    }

    /**
     * Send an existing draft contract to the member.
     */
    public static function sendContract(MemberContract $contract, Admin $admin): MemberContract
    {
        if ($contract->status !== MemberContract::STATUS_DRAFT) {
            throw new HttpException(422, 'Only draft contracts can be sent to members.');
        }

        $contract->status = MemberContract::STATUS_SENT;
        $contract->sent_at = Carbon::now();
        $contract->updated_by = $admin->id;
        $contract->save();

        $contract->events()->create([
            'event'       => MemberContractEvent::EVENT_SENT,
            'actor_type'  => MemberContractEvent::ACTOR_ADMIN,
            'actor_id'    => $admin->id,
            'actor_name'  => $admin->name,
            'actor_email' => $admin->email,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'file_hash'   => $contract->file_hash,
            'notes'       => 'Contract issued and sent to member for review.',
            'created_at'  => Carbon::now(),
        ]);

        self::sendContractNotification($contract);

        return $contract->fresh();
    }

    /**
     * Member accepts an issued contract.
     */
    public static function acceptContract(MemberContract $contract, Customer $customer, array $meta = []): MemberContract
    {
        if ($contract->customer_id !== $customer->id) {
            abort(404, 'Contract not found.');
        }

        if ($contract->evaluateExpiry()) {
            throw new HttpException(422, 'This contract has expired and can no longer be accepted.');
        }

        if ($contract->status !== MemberContract::STATUS_SENT) {
            throw new HttpException(422, 'This contract is not currently pending your acknowledgment.');
        }

        $now = Carbon::now();
        $notes = !empty($meta['notes']) ? trim($meta['notes']) : null;

        $contract->status = MemberContract::STATUS_ACCEPTED;
        $contract->responded_at = $now;
        if ($notes) {
            $contract->member_notes = $notes;
        }
        $contract->save();

        $contract->events()->create([
            'event'       => MemberContractEvent::EVENT_ACCEPTED,
            'actor_type'  => MemberContractEvent::ACTOR_CUSTOMER,
            'actor_id'    => $customer->id,
            'actor_name'  => trim($customer->first_name . ' ' . $customer->last_name),
            'actor_email' => $customer->email,
            'ip_address'  => $meta['ip'] ?? request()->ip(),
            'user_agent'  => $meta['user_agent'] ?? request()->userAgent(),
            'file_hash'   => $contract->file_hash,
            'notes'       => $notes ?: 'Member electronically accepted the agreement terms.',
            'metadata'    => [
                'disclaimer'   => 'Application acknowledgment accepted electronically by wholesale member.',
                'accepted_at'  => $now->toIso8601String(),
            ],
            'created_at'  => $now,
        ]);

        self::sendAcceptanceNotification($contract, $notes);

        return $contract->fresh();
    }

    /**
     * Member rejects an issued contract.
     */
    public static function rejectContract(MemberContract $contract, Customer $customer, ?string $notes = null, array $meta = []): MemberContract
    {
        if ($contract->customer_id !== $customer->id) {
            abort(404, 'Contract not found.');
        }

        if ($contract->evaluateExpiry()) {
            throw new HttpException(422, 'This contract has expired.');
        }

        if ($contract->status !== MemberContract::STATUS_SENT) {
            throw new HttpException(422, 'This contract is not currently pending your acknowledgment.');
        }

        $now = Carbon::now();
        $notes = !empty($notes) ? trim($notes) : null;

        $contract->status = MemberContract::STATUS_REJECTED;
        $contract->responded_at = $now;
        if ($notes) {
            $contract->member_notes = $notes;
        }
        $contract->save();

        $contract->events()->create([
            'event'       => MemberContractEvent::EVENT_REJECTED,
            'actor_type'  => MemberContractEvent::ACTOR_CUSTOMER,
            'actor_id'    => $customer->id,
            'actor_name'  => trim($customer->first_name . ' ' . $customer->last_name),
            'actor_email' => $customer->email,
            'ip_address'  => $meta['ip'] ?? request()->ip(),
            'user_agent'  => $meta['user_agent'] ?? request()->userAgent(),
            'file_hash'   => $contract->file_hash,
            'notes'       => $notes ?: 'Member declined the agreement terms.',
            'metadata'    => [
                'rejected_at' => $now->toIso8601String(),
            ],
            'created_at'  => $now,
        ]);

        self::sendRejectionNotification($contract, $notes);

        return $contract->fresh();
    }

    /**
     * Admin cancels an active contract.
     */
    public static function cancelContract(MemberContract $contract, Admin $admin, ?string $reason = null): MemberContract
    {
        $contract->status = MemberContract::STATUS_CANCELLED;
        $contract->updated_by = $admin->id;
        $contract->save();

        $contract->events()->create([
            'event'       => MemberContractEvent::EVENT_CANCELLED,
            'actor_type'  => MemberContractEvent::ACTOR_ADMIN,
            'actor_id'    => $admin->id,
            'actor_name'  => $admin->name,
            'actor_email' => $admin->email,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'file_hash'   => $contract->file_hash,
            'notes'       => $reason ?: 'Contract cancelled by administrator.',
            'created_at'  => Carbon::now(),
        ]);

        return $contract->fresh();
    }

    /**
     * Admin supersedes an active contract.
     */
    public static function supersedeContract(MemberContract $contract, Admin $admin, ?string $notes = null): MemberContract
    {
        $contract->status = MemberContract::STATUS_SUPERSEDED;
        $contract->updated_by = $admin->id;
        $contract->save();

        $contract->events()->create([
            'event'       => MemberContractEvent::EVENT_SUPERSEDED,
            'actor_type'  => MemberContractEvent::ACTOR_ADMIN,
            'actor_id'    => $admin->id,
            'actor_name'  => $admin->name,
            'actor_email' => $admin->email,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'file_hash'   => $contract->file_hash,
            'notes'       => $notes ?: 'Contract superseded by newer agreement version.',
            'created_at'  => Carbon::now(),
        ]);

        return $contract->fresh();
    }

    /**
     * Record viewed/downloaded event.
     */
    public static function recordViewEvent(MemberContract $contract, $actor, string $actorType = MemberContractEvent::ACTOR_CUSTOMER, ?string $ip = null, ?string $userAgent = null): void
    {
        $actorId = $actor ? $actor->id : null;
        $actorName = $actor ? (trim(($actor->first_name ?? $actor->name ?? '') . ' ' . ($actor->last_name ?? ''))) : null;
        $actorEmail = $actor ? $actor->email : null;

        $contract->events()->create([
            'event'       => MemberContractEvent::EVENT_VIEWED,
            'actor_type'  => $actorType,
            'actor_id'    => $actorId,
            'actor_name'  => $actorName,
            'actor_email' => $actorEmail,
            'ip_address'  => $ip ?? request()->ip(),
            'user_agent'  => $userAgent ?? request()->userAgent(),
            'file_hash'   => $contract->file_hash,
            'notes'       => 'Contract document viewed / downloaded.',
            'created_at'  => Carbon::now(),
        ]);
    }

    /**
     * Stream or download an individual contract file with security headers.
     */
    public static function streamContractFile(MemberContract $contract, bool $asDownload = true): StreamedResponse
    {
        $disk = Storage::disk(self::PRIVATE_DISK);

        if (!$disk->exists($contract->storage_path)) {
            abort(404, 'Contract file not found in private storage.');
        }

        $filename = self::sanitizeOriginalFilename($contract->original_filename ?: ($contract->reference_number . '.pdf'));
        $mimeType = $contract->mime_type ?: 'application/octet-stream';
        $disposition = $asDownload ? 'attachment' : 'inline';

        $headers = [
            'Content-Type'            => $mimeType,
            'Content-Disposition'     => "{$disposition}; filename=\"{$filename}\"",
            'X-Content-Type-Options'  => 'nosniff',
            'Cache-Control'           => 'private, no-cache, no-store, must-revalidate',
            'Pragma'                  => 'no-cache',
            'Expires'                 => '0',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
        ];

        return response()->stream(function () use ($disk, $contract) {
            $stream = $disk->readStream($contract->storage_path);
            if (is_resource($stream)) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, $headers);
    }

    /**
     * Validate an uploaded contract/template file.
     */
    public static function validateFile(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new HttpException(422, 'Uploaded agreement file is invalid or corrupted.');
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        $detectedMime = strtolower((string) $file->getMimeType());
        $clientMime = strtolower((string) $file->getClientMimeType());

        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new HttpException(422, "File extension '{$extension}' is not permitted. Permitted formats: PDF, DOCX, DOC.");
        }

        $validMime = in_array($detectedMime, self::ALLOWED_MIME_TYPES, true) || in_array($clientMime, self::ALLOWED_MIME_TYPES, true);
        if (!$validMime) {
            throw new HttpException(422, "Detected file MIME type '{$detectedMime}' is not allowed for agreement documents.");
        }

        $sizeKb = $file->getSize() / 1024;
        if ($sizeKb > self::MAX_FILE_SIZE_KB) {
            throw new HttpException(422, 'Document exceeds the maximum allowed file size of 15 MB.');
        }
    }

    /**
     * Store file securely on private disk.
     */
    protected static function storePhysicalFile(UploadedFile $file, string $directory): string
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $safeFilename = Str::uuid()->toString() . '.' . $extension;
        $path = $directory . '/' . $safeFilename;

        Storage::disk(self::PRIVATE_DISK)->put($path, file_get_contents($file->getRealPath()));

        return $path;
    }

    /**
     * Delete physical file from private disk.
     */
    public static function deletePhysicalFile(?string $path): void
    {
        if (!empty($path) && Storage::disk(self::PRIVATE_DISK)->exists($path)) {
            Storage::disk(self::PRIVATE_DISK)->delete($path);
        }
    }

    /**
     * Sanitize original filename.
     */
    public static function sanitizeOriginalFilename(string $filename): string
    {
        $basename = basename($filename);
        $clean = preg_replace('/[^A-Za-z0-9_\-\. ]/', '_', $basename);

        return $clean ?: 'document.pdf';
    }

    /**
     * Send email notification to member with try/catch.
     */
    protected static function sendContractNotification(MemberContract $contract): void
    {
        try {
            if ($contract->customer && !empty($contract->customer->email)) {
                Mail::to($contract->customer->email)->send(new MemberContractSent($contract));
            }
        } catch (Exception $e) {
            Log::warning('[ContractAgreementManager] Failed to send contract email to member: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
            ]);
        }
    }

    /**
     * Send acceptance email to admin with try/catch.
     */
    protected static function sendAcceptanceNotification(MemberContract $contract, ?string $notes = null): void
    {
        try {
            $adminEmail = $contract->creator?->email ?: (config('mail.from.address') ?: 'admin@purnaturek.com');
            Mail::to($adminEmail)->send(new MemberContractAccepted($contract, $notes));
        } catch (Exception $e) {
            Log::warning('[ContractAgreementManager] Failed to send acceptance email to admin: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
            ]);
        }
    }

    /**
     * Send rejection email to admin with try/catch.
     */
    protected static function sendRejectionNotification(MemberContract $contract, ?string $notes = null): void
    {
        try {
            $adminEmail = $contract->creator?->email ?: (config('mail.from.address') ?: 'admin@purnaturek.com');
            Mail::to($adminEmail)->send(new MemberContractRejected($contract, $notes));
        } catch (Exception $e) {
            Log::warning('[ContractAgreementManager] Failed to send rejection email to admin: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
            ]);
        }
    }
}

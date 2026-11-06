<?php

namespace App\Http\Controllers;

use App\Models\ProductDocument;
use App\Support\ProductDocumentationManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Webkul\Shop\Http\Controllers\Controller;

class WholesaleProductDocumentController extends Controller
{
    /**
     * Download a published product document (Authenticated Member).
     */
    public function download(Request $request, int $productId, int $documentId): StreamedResponse
    {
        $document = ProductDocument::query()
            ->where('id', $documentId)
            ->where('product_id', $productId)
            ->published()
            ->first();

        if (! $document) {
            abort(404, 'The requested product document was not found or is not published.');
        }

        return ProductDocumentationManager::streamDocument($document, true);
    }

    /**
     * Preview a published product document inline in browser (Authenticated Member).
     */
    public function preview(Request $request, int $productId, int $documentId): StreamedResponse
    {
        $document = ProductDocument::query()
            ->where('id', $documentId)
            ->where('product_id', $productId)
            ->published()
            ->first();

        if (! $document) {
            abort(404, 'The requested product document was not found or is not published.');
        }

        return ProductDocumentationManager::streamDocument($document, false);
    }
}


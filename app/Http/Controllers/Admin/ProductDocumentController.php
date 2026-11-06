<?php

namespace App\Http\Controllers\Admin;

use App\DataGrids\Admin\ProductDocumentDataGrid;
use App\Http\Requests\Admin\ProductDocumentRequest;
use App\Models\ProductDocument;
use App\Support\ProductDocumentationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Product\Repositories\ProductRepository;

class ProductDocumentController extends Controller
{
    /**
     * Display a listing of product documents.
     */
    public function index()
    {
        if (request()->ajax()) {
            return app(ProductDocumentDataGrid::class)->process();
        }

        return view('admin.catalog.product_documents.index');
    }

    /**
     * Show form for uploading and creating a new product document.
     */
    public function create(): View
    {
        $products = app(ProductRepository::class)
            ->with(['translations'])
            ->all(['id', 'sku', 'type']);

        $documentTypes = ProductDocument::DOCUMENT_TYPES;
        $statuses = ProductDocument::STATUSES;

        return view('admin.catalog.product_documents.create', compact('products', 'documentTypes', 'statuses'));
    }

    /**
     * Store a newly created product document.
     */
    public function store(ProductDocumentRequest $request): RedirectResponse
    {
        $document = ProductDocumentationManager::storeDocument(
            $request->validated(),
            $request->file('file')
        );

        session()->flash('success', "Product document '{$document->title}' uploaded and created successfully.");

        return redirect()->route('admin.catalog.product_documents.index');
    }

    /**
     * Show the form for editing the specified product document.
     */
    public function edit(int $id): View
    {
        $document = ProductDocument::with('product')->findOrFail($id);

        $products = app(ProductRepository::class)
            ->with(['translations'])
            ->all(['id', 'sku', 'type']);

        $documentTypes = ProductDocument::DOCUMENT_TYPES;
        $statuses = ProductDocument::STATUSES;

        return view('admin.catalog.product_documents.edit', compact('document', 'products', 'documentTypes', 'statuses'));
    }

    /**
     * Update the specified product document.
     */
    public function update(ProductDocumentRequest $request, int $id): RedirectResponse
    {
        $document = ProductDocument::findOrFail($id);

        ProductDocumentationManager::updateDocument(
            $document,
            $request->validated(),
            $request->file('file')
        );

        session()->flash('success', "Product document '{$document->title}' updated successfully.");

        return redirect()->route('admin.catalog.product_documents.index');
    }

    /**
     * Remove the specified product document and its stored private file.
     */
    public function destroy(int $id): RedirectResponse
    {
        $document = ProductDocument::findOrFail($id);
        $title = $document->title;

        ProductDocumentationManager::deleteDocument($document);

        session()->flash('success', "Product document '{$title}' and its associated file have been permanently deleted.");

        return redirect()->route('admin.catalog.product_documents.index');
    }

    /**
     * Download document file from private storage (Admin).
     */
    public function download(int $id): StreamedResponse
    {
        $document = ProductDocument::findOrFail($id);

        return ProductDocumentationManager::streamDocument($document, true);
    }

    /**
     * Preview document file from private storage (Admin).
     */
    public function preview(int $id): StreamedResponse
    {
        $document = ProductDocument::findOrFail($id);

        return ProductDocumentationManager::streamDocument($document, false);
    }
}


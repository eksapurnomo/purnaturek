<?php

namespace App\Http\Controllers;

use App\Http\Requests\BuyerVerificationRequest;
use App\Models\BuyerVerificationDocument;
use App\Support\BuyerVerificationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Webkul\Shop\Http\Controllers\Controller;

class WholesaleBuyerVerificationController extends Controller
{
    /**
     * Show buyer verification status and submission form.
     */
    public function index(): View
    {
        $customer = auth()->guard('customer')->user();
        $status = BuyerVerificationManager::getVerificationStatus($customer);
        $latestVerification = BuyerVerificationManager::getLatestVerification($customer);

        return view('shop::customers.account.buyer_verification.index', compact(
            'customer',
            'status',
            'latestVerification'
        ));
    }

    /**
     * Submit business credentials and verification documents.
     */
    public function store(BuyerVerificationRequest $request): RedirectResponse
    {
        $customer = auth()->guard('customer')->user();

        if (! BuyerVerificationManager::canSubmitApplication($customer)) {
            session()->flash('warning', 'You already have an active or pending verification application.');

            return redirect()->route('shop.customers.account.buyer_verification.index');
        }

        $validated = $request->validated();
        $uploadedFiles = $request->file('documents', []);
        $documentTypes = $request->input('document_types', []);

        $filesByType = [];
        foreach ($uploadedFiles as $index => $file) {
            $type = $documentTypes[$index] ?? BuyerVerificationDocument::TYPE_OTHER;
            $filesByType[$type][] = $file;
        }

        BuyerVerificationManager::submitApplication($customer, $validated, $filesByType);

        session()->flash('success', 'Your business verification application has been submitted successfully. Our commercial team will review your credentials.');

        return redirect()->route('shop.customers.account.buyer_verification.index');
    }

    /**
     * List all submitted business credentials for the authenticated member.
     */
    public function documents(): View
    {
        $customer = auth()->guard('customer')->user();

        $documents = BuyerVerificationDocument::whereHas('buyerVerification', function ($query) use ($customer) {
            $query->where('customer_id', $customer->id);
        })
            ->with('buyerVerification')
            ->latest()
            ->get();

        return view('shop::customers.account.buyer_verification.documents', compact('customer', 'documents'));
    }

    /**
     * Securely download a business credential document.
     */
    public function downloadDocument(int $id): StreamedResponse
    {
        $customer = auth()->guard('customer')->user();

        $document = BuyerVerificationDocument::with('buyerVerification')->findOrFail($id);

        return BuyerVerificationManager::streamDocument($document, $customer, false);
    }
}


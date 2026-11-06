<?php

namespace App\Http\Controllers\Admin;

use App\DataGrids\Admin\BuyerVerificationDataGrid;
use App\Http\Requests\AdminBuyerVerificationActionRequest;
use App\Models\BuyerVerification;
use App\Models\BuyerVerificationDocument;
use App\Support\BuyerVerificationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Webkul\Admin\Http\Controllers\Controller;

class BuyerVerificationController extends Controller
{
    /**
     * Display a listing of buyer verification requests.
     */
    public function index()
    {
        if (request()->ajax()) {
            return app(BuyerVerificationDataGrid::class)->process();
        }

        return view('admin.buyer_verifications.index');
    }

    /**
     * Show detailed review screen for a verification request.
     */
    public function view(int $id): View
    {
        $verification = BuyerVerification::with(['documents', 'customer', 'reviewer'])->findOrFail($id);

        return view('admin.buyer_verifications.view', compact('verification'));
    }

    /**
     * Approve a verification request.
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $verification = BuyerVerification::findOrFail($id);

        BuyerVerificationManager::approveApplication($verification, auth()->guard('admin')->user());

        session()->flash('success', 'Buyer verification application approved successfully. Member is now recognized as a Verified Wholesale Buyer.');

        return redirect()->route('admin.b2b.buyer_verifications.view', $id);
    }

    /**
     * Reject a verification request with a recorded reason.
     */
    public function reject(AdminBuyerVerificationActionRequest $request, int $id): RedirectResponse
    {
        $verification = BuyerVerification::findOrFail($id);

        BuyerVerificationManager::rejectApplication(
            $verification,
            auth()->guard('admin')->user(),
            $request->input('rejection_reason')
        );

        session()->flash('success', 'Buyer verification application has been rejected and the member notified with the recorded reason.');

        return redirect()->route('admin.b2b.buyer_verifications.view', $id);
    }

    /**
     * Securely download a business credential document for admin inspection.
     */
    public function downloadDocument(int $id): StreamedResponse
    {
        $document = BuyerVerificationDocument::with('buyerVerification')->findOrFail($id);

        return BuyerVerificationManager::streamDocument($document, auth()->guard('admin')->user(), true);
    }
}


<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberContractActionRequest;
use App\Models\ContractTemplate;
use App\Models\MemberContract;
use App\Models\MemberContractEvent;
use App\Support\ContractAgreementManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Webkul\Shop\Http\Controllers\Controller;

class WholesaleContractController extends Controller
{
    /**
     * Display listing of contracts and member-accessible templates for the authenticated member.
     */
    public function index(): View
    {
        $customer = auth()->guard('customer')->user();

        $contracts = MemberContract::query()
            ->forCustomer($customer->id)
            ->with('template')
            ->orderBy('id', 'desc')
            ->paginate(15);

        foreach ($contracts as $contract) {
            $contract->evaluateExpiry();
        }

        $templates = ContractTemplate::query()
            ->published()
            ->memberAccessible()
            ->orderBy('title')
            ->get();

        $pendingActionCount = MemberContract::query()
            ->forCustomer($customer->id)
            ->where('status', MemberContract::STATUS_SENT)
            ->count();

        return view('shop::customers.account.contracts.index', compact('contracts', 'templates', 'pendingActionCount'));
    }

    /**
     * Show detail of an individual issued contract.
     */
    public function view(int $id): View
    {
        $customer = auth()->guard('customer')->user();

        $contract = MemberContract::query()
            ->forCustomer($customer->id)
            ->with(['template', 'events'])
            ->findOrFail($id);

        $contract->evaluateExpiry();

        return view('shop::customers.account.contracts.view', compact('contract'));
    }

    /**
     * Process member acknowledgment (Accept or Reject).
     */
    public function action(MemberContractActionRequest $request, int $id): RedirectResponse
    {
        $customer = auth()->guard('customer')->user();

        $contract = MemberContract::query()
            ->forCustomer($customer->id)
            ->findOrFail($id);

        $action = $request->input('action');
        $notes = $request->input('notes');

        if ($action === 'accept') {
            ContractAgreementManager::acceptContract($contract, $customer, [
                'notes'      => $notes,
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            session()->flash('success', "Thank you. You have successfully accepted commercial agreement {$contract->reference_number}.");
        } else {
            ContractAgreementManager::rejectContract(
                $contract,
                $customer,
                $notes,
                [
                    'ip'         => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            session()->flash('info', "You have declined agreement {$contract->reference_number}. Administration has been notified.");
        }

        return redirect()->route('shop.customers.account.contracts.view', $contract->id);
    }

    /**
     * Download contract file.
     */
    public function download(Request $request, int $id): StreamedResponse
    {
        $customer = auth()->guard('customer')->user();

        $contract = MemberContract::query()
            ->forCustomer($customer->id)
            ->findOrFail($id);

        ContractAgreementManager::recordViewEvent(
            $contract,
            $customer,
            MemberContractEvent::ACTOR_CUSTOMER,
            $request->ip(),
            $request->userAgent()
        );

        return ContractAgreementManager::streamContractFile($contract, true);
    }

    /**
     * Display general contract templates library.
     */
    public function templates(): View
    {
        $templates = ContractTemplate::query()
            ->published()
            ->memberAccessible()
            ->orderBy('title')
            ->get();

        return view('shop::customers.account.contracts.templates', compact('templates'));
    }

    /**
     * Download a member-accessible contract template.
     */
    public function downloadTemplate(int $id): StreamedResponse
    {
        $template = ContractTemplate::query()
            ->published()
            ->memberAccessible()
            ->findOrFail($id);

        return ContractAgreementManager::streamTemplateFile($template, true);
    }
}


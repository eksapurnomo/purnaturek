<?php

namespace App\Http\Controllers\Admin;

use App\DataGrids\Admin\MemberContractDataGrid;
use App\Http\Requests\Admin\MemberContractCreateRequest;
use App\Models\ContractTemplate;
use App\Models\MemberContract;
use App\Models\MemberContractEvent;
use App\Support\ContractAgreementManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Customer\Models\Customer;

class MemberContractController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            return app(MemberContractDataGrid::class)->process();
        }

        return view('admin.contracts.individual.index');
    }

    public function create(): View
    {
        $customers = Customer::query()
            ->select('id', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')
            ->get();

        $templates = ContractTemplate::query()
            ->select('id', 'title', 'version', 'status')
            ->orderBy('title')
            ->get();

        return view('admin.contracts.individual.create', compact('customers', 'templates'));
    }

    public function store(MemberContractCreateRequest $request): RedirectResponse
    {
        $admin = auth()->guard('admin')->user();

        $contract = ContractAgreementManager::createContract(
            $request->validated(),
            $request->file('file'),
            $admin
        );

        session()->flash('success', "Member contract '{$contract->reference_number}' created successfully.");

        return redirect()->route('admin.b2b.member-contracts.view', $contract->id);
    }

    public function view(int $id): View
    {
        $contract = MemberContract::with(['customer', 'template', 'events', 'creator', 'updater'])->findOrFail($id);

        $contract->evaluateExpiry();

        return view('admin.contracts.individual.view', compact('contract'));
    }

    public function send(int $id): RedirectResponse
    {
        $contract = MemberContract::findOrFail($id);
        $admin = auth()->guard('admin')->user();

        ContractAgreementManager::sendContract($contract, $admin);

        session()->flash('success', "Contract '{$contract->reference_number}' has been sent to the member for acknowledgment.");

        return redirect()->route('admin.b2b.member-contracts.view', $contract->id);
    }

    public function cancel(Request $request, int $id): RedirectResponse
    {
        $contract = MemberContract::findOrFail($id);
        $admin = auth()->guard('admin')->user();

        ContractAgreementManager::cancelContract($contract, $admin, $request->input('reason'));

        session()->flash('success', "Contract '{$contract->reference_number}' has been cancelled.");

        return redirect()->route('admin.b2b.member-contracts.view', $contract->id);
    }

    public function download(int $id): StreamedResponse
    {
        $contract = MemberContract::findOrFail($id);
        $admin = auth()->guard('admin')->user();

        ContractAgreementManager::recordViewEvent(
            $contract,
            $admin,
            MemberContractEvent::ACTOR_ADMIN,
            request()->ip(),
            request()->userAgent()
        );

        return ContractAgreementManager::streamContractFile($contract, true);
    }
}


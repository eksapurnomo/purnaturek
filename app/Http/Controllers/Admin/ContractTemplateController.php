<?php

namespace App\Http\Controllers\Admin;

use App\DataGrids\Admin\ContractTemplateDataGrid;
use App\Http\Requests\Admin\ContractTemplateRequest;
use App\Models\ContractTemplate;
use App\Support\ContractAgreementManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Webkul\Admin\Http\Controllers\Controller;

class ContractTemplateController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            return app(ContractTemplateDataGrid::class)->process();
        }

        return view('admin.contracts.templates.index');
    }

    public function create(): View
    {
        $categories = ContractTemplate::CATEGORIES;
        $statuses = ContractTemplate::STATUSES;

        return view('admin.contracts.templates.create', compact('categories', 'statuses'));
    }

    public function store(ContractTemplateRequest $request): RedirectResponse
    {
        $admin = auth()->guard('admin')->user();

        $template = ContractAgreementManager::storeTemplate(
            $request->validated(),
            $request->file('file'),
            $admin
        );

        session()->flash('success', "Contract template '{$template->title}' created successfully.");

        return redirect()->route('admin.b2b.contract-templates.index');
    }

    public function edit(int $id): View
    {
        $template = ContractTemplate::findOrFail($id);
        $categories = ContractTemplate::CATEGORIES;
        $statuses = ContractTemplate::STATUSES;

        return view('admin.contracts.templates.edit', compact('template', 'categories', 'statuses'));
    }

    public function update(ContractTemplateRequest $request, int $id): RedirectResponse
    {
        $template = ContractTemplate::findOrFail($id);
        $admin = auth()->guard('admin')->user();

        ContractAgreementManager::updateTemplate(
            $template,
            $request->validated(),
            $request->file('file'),
            $admin
        );

        session()->flash('success', "Contract template '{$template->title}' updated successfully.");

        return redirect()->route('admin.b2b.contract-templates.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $template = ContractTemplate::findOrFail($id);
        $title = $template->title;

        ContractAgreementManager::deleteTemplate($template);

        session()->flash('success', "Contract template '{$title}' deleted successfully.");

        return redirect()->route('admin.b2b.contract-templates.index');
    }

    public function download(int $id): StreamedResponse
    {
        $template = ContractTemplate::findOrFail($id);

        return ContractAgreementManager::streamTemplateFile($template, true);
    }
}


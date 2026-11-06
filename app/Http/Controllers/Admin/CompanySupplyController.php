<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CompanySupplyPageRequest;
use App\Models\CompanySupplyPage;
use App\Support\CompanySupplyManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;

class CompanySupplyController extends Controller
{
    /**
     * Display the Company & Supply Information editor.
     */
    public function edit(): View
    {
        $page = CompanySupplyManager::getPage();
        $statuses = CompanySupplyPage::STATUSES;

        return view('admin.company_supply.edit', compact('page', 'statuses'));
    }

    /**
     * Update page settings and all section contents.
     */
    public function update(CompanySupplyPageRequest $request): RedirectResponse
    {
        $admin = auth()->guard('admin')->user();

        $page = CompanySupplyManager::updatePage($request->validated(), $admin);

        session()->flash('success', 'Company & Supply Information updated successfully.');

        return redirect()->route('admin.b2b.company_supply.edit');
    }

    /**
     * Administrative preview of member-facing page.
     */
    public function preview(): View
    {
        $page = CompanySupplyManager::getPage();
        $sections = $page->sections()->where('is_enabled', true)->orderBy('sort_order', 'asc')->get();

        return view('admin.company_supply.preview', compact('page', 'sections'));
    }
}


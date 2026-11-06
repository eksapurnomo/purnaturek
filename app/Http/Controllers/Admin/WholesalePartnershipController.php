<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\WholesalePartnershipPageRequest;
use App\Models\WholesalePartnershipPage;
use App\Support\WholesalePartnershipManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;

class WholesalePartnershipController extends Controller
{
    /**
     * Display the Wholesale Partnership Information editor.
     */
    public function edit(): View
    {
        $page = WholesalePartnershipManager::getPage();
        $statuses = WholesalePartnershipPage::STATUSES;

        return view('admin.partnership_information.edit', compact('page', 'statuses'));
    }

    /**
     * Update page settings and all section contents.
     */
    public function update(WholesalePartnershipPageRequest $request): RedirectResponse
    {
        $admin = auth()->guard('admin')->user();

        $page = WholesalePartnershipManager::updatePage($request->validated(), $admin);

        session()->flash('success', 'Wholesale Partnership Information updated successfully.');

        return redirect()->route('admin.b2b.partnership_information.edit');
    }

    /**
     * Administrative preview of member-facing page.
     */
    public function preview(): View
    {
        $page = WholesalePartnershipManager::getPage();
        $sections = $page->sections()->where('is_enabled', true)->orderBy('sort_order', 'asc')->get();

        return view('admin.partnership_information.preview', compact('page', 'sections'));
    }
}


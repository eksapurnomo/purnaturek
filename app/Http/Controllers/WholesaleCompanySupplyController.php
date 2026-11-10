<?php

namespace App\Http\Controllers;

use App\Models\CompanySupplyPage;
use App\Support\CompanySupplyManager;
use Illuminate\View\View;
use Webkul\Shop\Http\Controllers\Controller;

class WholesaleCompanySupplyController extends Controller
{
    /**
     * Display the member-facing Company & Supply Information page.
     */
    public function index(): View
    {
        $page = CompanySupplyManager::getPage();
        $isPublished = $page->isPublished();

        $sections = $isPublished
            ? $page->sections()
                ->where('is_enabled', true)
                ->whereNotNull('content')
                ->where('content', '!=', '')
                ->orderBy('sort_order', 'asc')
                ->get()
            : collect([]);

        return view('shop::customers.account.company_supply.index', compact('page', 'sections', 'isPublished'));
    }
}


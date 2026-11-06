<?php

namespace App\Http\Controllers;

use App\Support\WholesalePartnershipManager;
use Illuminate\View\View;
use Webkul\Shop\Http\Controllers\Controller;

class WholesalePartnershipController extends Controller
{
    /**
     * Display the member-facing Wholesale Partnership Information page.
     */
    public function index(): View
    {
        $page = WholesalePartnershipManager::getPage();
        $isPublished = $page->isPublished();

        $sections = $isPublished
            ? $page->sections()
                ->where('is_enabled', true)
                ->whereNotNull('content')
                ->where('content', '!=', '')
                ->orderBy('sort_order', 'asc')
                ->get()
            : collect([]);

        return view('vendor.shop.customers.account.partnership_information.index', compact('page', 'sections', 'isPublished'));
    }
}


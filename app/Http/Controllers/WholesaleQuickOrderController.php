<?php

namespace App\Http\Controllers;

use App\Http\Resources\WholesaleQuickOrderProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;
use Webkul\B2BSuite\Http\Controllers\Shop\QuickOrderController;

/**
 * Application-level override of the B2B Suite Quick Order controller (bound in AppServiceProvider).
 *
 * Search and fetch-by-SKU delegate to the vendor implementation (catalog visibility and search
 * behaviour are unchanged) and only swap the JSON resource so Model B products never leak
 * their native or indicative price. `store()` is inherited unchanged: Model B items may still
 * enter the cart for the RFQ workflow, but the checkout guards block ordering them without an
 * approved quotation.
 */
class WholesaleQuickOrderController extends QuickOrderController
{
    public function search()
    {
        return $this->sanitize(parent::search());
    }

    public function fetchBySkus(Request $request)
    {
        return $this->sanitize(parent::fetchBySkus($request));
    }

    /**
     * Re-wrap the vendor resource collection with the wholesale-safe resource, preserving
     * pagination meta/links for the infinite-scroll search.
     */
    protected function sanitize($result)
    {
        if (! $result instanceof ResourceCollection) {
            return $result;
        }

        $products = $result->collection->map(fn ($resource) => $resource->resource);

        if ($result->resource instanceof AbstractPaginator) {
            return WholesaleQuickOrderProductResource::collection($result->resource->setCollection($products));
        }

        return WholesaleQuickOrderProductResource::collection($products);
    }
}


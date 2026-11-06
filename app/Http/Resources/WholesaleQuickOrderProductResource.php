<?php

namespace App\Http\Resources;

use App\Support\WholesalePricingManager;
use Webkul\Admin\Http\Resources\ProductResource;

/**
 * Quick Order product payload.
 *
 * Identical to the B2B Suite response (same keys, so the existing Vue component keeps working),
 * except that Model B (wholesale indicative pricing) products never disclose the native price:
 * `price` is null and `formatted_price` carries the safe wholesale label instead.
 * Model A (native Bagisto pricing) products are returned untouched.
 */
class WholesaleQuickOrderProductResource extends ProductResource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);

        if (! WholesalePricingManager::usesWholesalePricing($this->resource)) {
            return $data;
        }

        $display = WholesalePricingManager::formatForMember($this->resource);

        $data['price'] = null;
        $data['formatted_price'] = $display['price_display'];
        $data['is_wholesale'] = true;
        $data['requires_quotation'] = true;
        $data['wholesale'] = [
            'mode'                => $display['mode'],
            'price_display'       => $display['price_display'],
            'moq'                 => $display['moq_display'],
            'supply_availability' => $display['supply_availability'],
        ];

        return $data;
    }
}


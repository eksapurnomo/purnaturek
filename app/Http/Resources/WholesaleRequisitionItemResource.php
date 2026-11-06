<?php

namespace App\Http\Resources;

use App\Support\WholesalePricingManager;
use Webkul\B2BSuite\Http\Resources\RequisitionItemResource;

/**
 * Requisition List item payload.
 *
 * For Model B products (wholesale indicative pricing), the internal Bagisto native price
 * and computed total are never disclosed: `price` and `total` are null, and `formatted_price`
 * and `formatted_total` carry the wholesale indicative label (Price Range or Contact for Price).
 *
 * For Model A products (native Bagisto pricing), all original native prices and fields are preserved.
 */
class WholesaleRequisitionItemResource extends RequisitionItemResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = parent::toArray($request);

        $product = $this->variant_id ? ($this->variant ?? $this->product) : $this->product;

        if (! WholesalePricingManager::usesWholesalePricing($product)) {
            return $data;
        }

        $pricingProduct = ($product && ! empty($product->parent_id) && $product->parent && empty($product->price_display_mode))
            ? $product->parent
            : $product;

        $display = WholesalePricingManager::formatForMember($pricingProduct);

        $data['price'] = null;
        $data['total'] = null;
        $data['formatted_price'] = $display['price_display'];
        $data['formatted_total'] = $display['price_display'];

        return $data;
    }
}


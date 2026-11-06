<?php

namespace App\Http\Requests;

use App\Support\WholesalePricingManager;
use Illuminate\Validation\Rule;
use Webkul\Admin\Http\Requests\ProductForm as BaseProductForm;
use Webkul\Core\Rules\Decimal;

class WholesaleProductForm extends BaseProductForm
{
    /**
     * Normalize wholesale pricing inputs before validation.
     *
     * The native Bagisto `price` is never derived from wholesale values: wholesale prices are
     * indicative display data only and must never become an implicit transaction price.
     * An empty / unknown `price_display_mode` means Model A (native Bagisto pricing).
     */
    protected function prepareForValidation()
    {
        WholesalePricingManager::ensureAttributesProvisioned();

        parent::prepareForValidation();

        $basePrice = $this->input('price');

        if (($basePrice === null || $basePrice === '') && $this->id) {
            $existingPrice = $this->productRepository->find($this->id)?->price;

            if ($existingPrice !== null && $existingPrice !== '') {
                $this->merge([
                    'price' => number_format((float) $existingPrice, 2, '.', ''),
                ]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $mode = $this->input('price_display_mode');
        $isRange = $mode === WholesalePricingManager::MODE_RANGE;

        $rules['price_display_mode'] = [
            'sometimes',
            'nullable',
            'string',
            Rule::in(array_merge([''], array_keys(WholesalePricingManager::DISPLAY_MODES))),
        ];

        $rules['wholesale_min_price'] = [
            $isRange ? 'required' : 'nullable',
            'numeric',
            'min:0',
            new Decimal,
        ];

        $maxPriceRules = [
            $isRange ? 'required' : 'nullable',
            'numeric',
            'min:0',
            new Decimal,
        ];

        if ($isRange || ($this->filled('wholesale_min_price') && $this->filled('wholesale_max_price'))) {
            $maxPriceRules[] = 'gte:wholesale_min_price';
        }

        $rules['wholesale_max_price'] = $maxPriceRules;

        $rules['wholesale_moq'] = [
            'nullable',
            'numeric',
            'gt:0',
            new Decimal,
        ];

        $rules['wholesale_unit'] = [
            'nullable',
            'string',
            Rule::in(array_keys(WholesalePricingManager::UNITS)),
        ];

        $rules['supply_availability'] = [
            'nullable',
            'string',
            Rule::in(array_keys(WholesalePricingManager::AVAILABILITY_OPTIONS)),
        ];

        return $rules;
    }

    /**
     * Custom validation messages for wholesale pricing fields.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'wholesale_min_price.required' => 'The Minimum Wholesale Price is required when Price Display Mode is set to Price Range.',
            'wholesale_min_price.min'      => 'The Minimum Wholesale Price must not be negative.',
            'wholesale_max_price.required' => 'The Maximum Wholesale Price is required when Price Display Mode is set to Price Range.',
            'wholesale_max_price.min'      => 'The Maximum Wholesale Price must not be negative.',
            'wholesale_max_price.gte'      => 'The Maximum Wholesale Price must be greater than or equal to the Minimum Wholesale Price.',
            'wholesale_moq.gt'             => 'The Minimum Order Quantity (MOQ) must be greater than zero.',
            'wholesale_unit.in'            => 'Please select a valid Unit of Measurement.',
            'supply_availability.in'       => 'Please select a valid Supply Availability status.',
            'price_display_mode.in'        => 'The selected price display mode is invalid. Valid options are: range, contact, or blank (Model A).',
        ]);
    }

    /**
     * Normalize empty/zero wholesale fields after validation so Bagisto's
     * ProductAttributeValueRepository::saveValues() updates cleared or zero values properly.
     */
    protected function passedValidation()
    {
        parent::passedValidation();

        $normalized = [];

        foreach (['wholesale_min_price', 'wholesale_max_price'] as $priceField) {
            if ($this->exists($priceField)) {
                $val = $this->input($priceField);

                if ($val === null || $val === '') {
                    $normalized[$priceField] = '';
                } elseif (is_numeric($val)) {
                    $normalized[$priceField] = number_format((float) $val, 2, '.', '');
                }
            }
        }

        foreach (['wholesale_moq', 'wholesale_unit', 'supply_availability', 'price_display_mode'] as $textField) {
            if ($this->exists($textField)) {
                $val = $this->input($textField);
                $normalized[$textField] = $val === null ? '' : (string) $val;
            }
        }

        if (! empty($normalized)) {
            $this->merge($normalized);
        }
    }
}


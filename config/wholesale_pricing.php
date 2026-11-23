<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Wholesale Pricing Display Mode
    |--------------------------------------------------------------------------
    |
    | Supported primary modes:
    | - BULK_CONTACT  : Custom wholesale quote / inquiry pricing ("Contact for Price")
    | - FIXED_PRICE   : Single fixed wholesale rate (optional unit)
    | - STARTING_FROM : Starting-from commercial rate (optional unit)
    |
    */
    'default_mode' => 'BULK_CONTACT',

    /*
    |--------------------------------------------------------------------------
    | Mode Presentation Metadata
    |--------------------------------------------------------------------------
    */
    'modes' => [
        'FIXED_PRICE' => [
            'label'        => 'Fixed Wholesale Price',
            'headline'     => 'Wholesale Price',
            'badge'        => 'Fixed Wholesale Rate',
            'default_note' => 'Fixed wholesale pricing applies per specified unit, subject to commercial account qualification and current allocation.',
        ],
        'STARTING_FROM' => [
            'label'        => 'Starting From',
            'headline'     => 'Wholesale Pricing',
            'badge'        => 'Tiered Starting Rate',
            'default_note' => 'Starting wholesale pricing varies by order volume and product specification requirements. Contact our wholesale desk for formal volume quotes.',
        ],
        'BULK_CONTACT' => [
            'label'        => 'Bulk Contact / Custom Quote',
            'headline'     => 'Bulk Wholesale Pricing',
            'badge'        => 'Custom Commercial Quote',
            'default_note' => 'Pricing is tailored to order volume, product format, and commercial buyer qualification. Contact our wholesale desk via Message Admin.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Product-Level Centralized Pricing Defaults (Keyed by SKU)
    |--------------------------------------------------------------------------
    |
    | Values set on a product in Bagisto Admin (EAV wholesale attributes) take
    | precedence when present. Otherwise, App\Support\WholesalePricingManager
    | reads these centralized defaults so no invented pricing appears.
    |
    */
    'products' => [
        'PUR-KRATOM-POWDER' => [
            'mode'                => 'BULK_CONTACT',
            'fixed_price'         => null,
            'starting_from_price' => null,
            'unit'                => null,
            'moq'                 => null,
            'supply_availability' => 'Contact for Availability',
            'price_note'          => 'Commercial wholesale pricing is tailored to inquiry volume and verified buyer qualification. Message our Chicago wholesale desk for formal pricing.',
        ],
        'PUR-KRATOM-EXTRACT' => [
            'mode'                => 'BULK_CONTACT',
            'fixed_price'         => null,
            'starting_from_price' => null,
            'unit'                => null,
            'moq'                 => null,
            'supply_availability' => 'Contact for Availability',
            'price_note'          => 'Concentrated extract pricing depends on commercial volume and specification review. Message our Chicago wholesale desk for formal pricing.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Commercial Pricing Disclaimer
    |--------------------------------------------------------------------------
    */
    'disclaimer' => 'Displayed pricing information is for qualified B2B wholesale reference only. Final commercial pricing depends on order quantity, product specifications, buyer verification status, shipping requirements, and mutual agreement.',
];


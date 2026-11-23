<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;
use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Models\AttributeFamily;
use Webkul\Attribute\Models\AttributeGroup;
use Webkul\Attribute\Models\AttributeOption;
use Webkul\B2BSuite\Models\CustomerQuote;
use Webkul\B2BSuite\Repositories\CustomerQuoteRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Product\Models\Product;

class WholesalePricingManager
{
    public const MODE_FIXED_PRICE = 'FIXED_PRICE';

    public const MODE_STARTING_FROM = 'STARTING_FROM';

    public const MODE_BULK_CONTACT = 'BULK_CONTACT';

    public const MODE_RANGE = 'range';

    public const MODE_CONTACT = 'contact';

    public const ATTRIBUTE_GROUP_CODE = 'wholesale_pricing';

    public const ATTRIBUTE_GROUP_NAME = 'Wholesale Pricing & Supply';

    public const DISCLAIMER = 'Displayed pricing information is for qualified B2B wholesale reference only. Final commercial pricing depends on order quantity, product specifications, buyer verification status, shipping requirements, and mutual agreement.';

    public const DISPLAY_MODES = [
        self::MODE_BULK_CONTACT  => 'Bulk Contact / Custom Quote (BULK_CONTACT)',
        self::MODE_FIXED_PRICE   => 'Fixed Wholesale Price (FIXED_PRICE)',
        self::MODE_STARTING_FROM => 'Starting From Price (STARTING_FROM)',
        self::MODE_RANGE         => 'Price Range (Legacy Range)',
        self::MODE_CONTACT       => 'Contact for Price (Legacy Contact)',
    ];

    public const UNITS = [
        'kg'        => 'kg',
        'ton'       => 'ton',
        'g'         => 'g',
        'lb'        => 'lb',
        'L'         => 'L',
        'mL'        => 'mL',
        'bag'       => 'bag',
        'box'       => 'box',
        'carton'    => 'carton',
        'drum'      => 'drum',
        'pallet'    => 'pallet',
        'container' => 'container',
        'piece'     => 'piece',
        'unit'      => 'unit',
        'pack'      => 'pack',
        'set'       => 'set',
    ];

    public const AVAILABILITY_OPTIONS = [
        'Available'                => 'Available',
        'Limited Availability'     => 'Limited Availability',
        'Made to Order'            => 'Made to Order',
        'Contact for Availability' => 'Contact for Availability',
    ];

    /**
     * Attribute codes managed by the wholesale pricing module.
     */
    public const ATTRIBUTE_CODES = [
        'price_display_mode',
        'wholesale_min_price',
        'wholesale_max_price',
        'wholesale_moq',
        'wholesale_unit',
        'supply_availability',
    ];

    /**
     * Internal / pricing attribute codes that must never be exposed in generic specification tables.
     */
    public const HIDDEN_SPEC_ATTRIBUTE_CODES = [
        'price',
        'cost',
        'special_price',
        'special_price_from',
        'special_price_to',
        'price_display_mode',
        'wholesale_min_price',
        'wholesale_max_price',
        'wholesale_moq',
        'wholesale_unit',
        'supply_availability',
    ];

    protected static bool $provisionChecked = false;

    /**
     * Ensure the 6 wholesale pricing EAV attributes and their Attribute Group exist
     * in Bagisto's native EAV tables without altering any database schema.
     */
    public static function ensureAttributesProvisioned(): void
    {
        if (self::$provisionChecked) {
            return;
        }

        try {
            if (
                ! Schema::hasTable('attributes')
                || ! Schema::hasTable('attribute_families')
                || ! Schema::hasTable('attribute_groups')
                || ! Schema::hasTable('attribute_group_mappings')
            ) {
                return;
            }

            $existingCount = Attribute::query()
                ->whereIn('code', self::ATTRIBUTE_CODES)
                ->count();

            $groupExists = AttributeGroup::query()
                ->where('code', self::ATTRIBUTE_GROUP_CODE)
                ->exists();

            if ($existingCount === count(self::ATTRIBUTE_CODES) && $groupExists) {
                self::$provisionChecked = true;

                return;
            }

            DB::transaction(function () {
                $definitions = [
                    [
                        'code'          => 'price_display_mode',
                        'admin_name'    => 'Price Display Mode',
                        'type'          => 'text',
                        'validation'    => null,
                        'default_value' => self::MODE_BULK_CONTACT,
                        'position'      => 101,
                    ],
                    [
                        'code'          => 'wholesale_min_price',
                        'admin_name'    => 'Minimum Wholesale Price',
                        'type'          => 'price',
                        'validation'    => 'decimal',
                        'default_value' => null,
                        'position'      => 102,
                    ],
                    [
                        'code'          => 'wholesale_max_price',
                        'admin_name'    => 'Maximum Wholesale Price',
                        'type'          => 'price',
                        'validation'    => 'decimal',
                        'default_value' => null,
                        'position'      => 103,
                    ],
                    [
                        'code'          => 'wholesale_moq',
                        'admin_name'    => 'Minimum Order Quantity (MOQ)',
                        'type'          => 'text',
                        'validation'    => 'decimal',
                        'default_value' => null,
                        'position'      => 104,
                    ],
                    [
                        'code'          => 'wholesale_unit',
                        'admin_name'    => 'Unit of Measurement',
                        'type'          => 'text',
                        'validation'    => null,
                        'default_value' => null,
                        'position'      => 105,
                    ],
                    [
                        'code'          => 'supply_availability',
                        'admin_name'    => 'Supply Availability',
                        'type'          => 'text',
                        'validation'    => null,
                        'default_value' => null,
                        'position'      => 106,
                    ],
                ];

                $locales = DB::table('locales')->pluck('code')->all() ?: ['en'];
                $attributeIdsByPosition = [];

                foreach ($definitions as $index => $def) {
                    $attribute = Attribute::query()->firstOrCreate(
                        ['code' => $def['code']],
                        [
                            'admin_name'          => $def['admin_name'],
                            'type'                => $def['type'],
                            'validation'          => $def['validation'],
                            'position'            => $def['position'],
                            'is_required'         => 0,
                            'is_unique'           => 0,
                            'value_per_locale'    => 0,
                            'value_per_channel'   => 0,
                            'default_value'       => $def['default_value'],
                            'is_filterable'       => 0,
                            'is_configurable'     => 0,
                            'is_visible_on_front' => 0,
                            'is_user_defined'     => 1,
                            'is_comparable'       => 0,
                            'enable_wysiwyg'      => 0,
                        ]
                    );

                    foreach ($locales as $locale) {
                        DB::table('attribute_translations')->updateOrInsert(
                            [
                                'attribute_id' => $attribute->id,
                                'locale'       => $locale,
                            ],
                            [
                                'name' => $def['admin_name'],
                            ]
                        );
                    }

                    $attributeIdsByPosition[$attribute->id] = $index + 1;
                }

                $families = AttributeFamily::query()->get();

                foreach ($families as $family) {
                    $group = $family->attribute_groups()->firstOrCreate(
                        [
                            'code' => self::ATTRIBUTE_GROUP_CODE,
                        ],
                        [
                            'name'            => self::ATTRIBUTE_GROUP_NAME,
                            'column'          => 2,
                            'is_user_defined' => 1,
                            'position'        => 2,
                        ]
                    );

                    foreach ($attributeIdsByPosition as $attributeId => $pos) {
                        DB::table('attribute_group_mappings')->updateOrInsert(
                            [
                                'attribute_id'       => $attributeId,
                                'attribute_group_id' => $group->id,
                            ],
                            [
                                'position' => $pos,
                            ]
                        );
                    }
                }
            });

            self::$provisionChecked = true;
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * Format a product's wholesale pricing and supply attributes for authenticated member views.
     *
     * Privacy guarantee:
     * - Never exposes Bagisto's base retail `price` or `cost`.
     * - Reads product-level Bagisto EAV overrides first, then falls back to centralized `config/wholesale_pricing.php`.
     * - When `BULK_CONTACT` (or legacy `contact`) is active, or when required numeric price fields
     *   for `FIXED_PRICE` / `STARTING_FROM` / `range` are unconfigured, numeric price fields remain strictly null.
     */
    public static function formatForMember($product): array
    {
        $configEntry = self::resolveConfigEntry($product);
        $rawMode = self::resolveEffectiveMode($product, $configEntry)
            ?? config('wholesale_pricing.default_mode', self::MODE_BULK_CONTACT);

        $mode = $rawMode === self::MODE_CONTACT ? self::MODE_BULK_CONTACT : $rawMode;

        $unit = self::resolveStringValue($product, 'wholesale_unit')
            ?? ($configEntry['unit'] ?? null);
        $unit = ($unit !== null && isset(self::UNITS[$unit])) ? self::UNITS[$unit] : ($unit ?: null);

        $availability = self::resolveStringValue($product, 'supply_availability')
            ?? ($configEntry['supply_availability'] ?? null);
        $availability = ($availability !== null && isset(self::AVAILABILITY_OPTIONS[$availability]))
            ? self::AVAILABILITY_OPTIONS[$availability]
            : ($availability ?: null);

        $moqRaw = $product->wholesale_moq ?? ($configEntry['moq'] ?? null);
        $moqFormatted = null;
        $moqDisplay = null;

        if ($moqRaw !== null && $moqRaw !== '' && is_numeric($moqRaw) && (float) $moqRaw > 0) {
            $moqFormatted = self::formatQuantity((float) $moqRaw);
            $moqDisplay = $unit ? "{$moqFormatted} {$unit}" : $moqFormatted;
        }

        $disclaimer = config('wholesale_pricing.disclaimer', self::DISCLAIMER);
        $minRaw = $product->wholesale_min_price ?? null;
        $maxRaw = $product->wholesale_max_price ?? null;

        if ($mode === self::MODE_FIXED_PRICE) {
            $fixedRaw = self::isValidNonNegativeNumeric($minRaw)
                ? $minRaw
                : ($configEntry['fixed_price'] ?? null);

            if (self::isValidNonNegativeNumeric($fixedRaw)) {
                $fixedFormatted = core()->formatPrice((float) $fixedRaw);
                $priceDisplay = $unit ? "{$fixedFormatted} / {$unit}" : $fixedFormatted;

                return self::buildPricingPayload(self::MODE_FIXED_PRICE, [
                    'is_fixed'              => true,
                    'fixed_price_formatted' => $fixedFormatted,
                    'min_price_formatted'   => $fixedFormatted,
                    'price_display'         => $priceDisplay,
                    'unit'                  => $unit,
                    'moq'                   => $moqFormatted,
                    'moq_display'           => $moqDisplay,
                    'supply_availability'   => $availability,
                    'disclaimer'            => $disclaimer,
                ], $configEntry);
            }
        }

        if ($mode === self::MODE_STARTING_FROM) {
            $startRaw = self::isValidNonNegativeNumeric($minRaw)
                ? $minRaw
                : ($configEntry['starting_from_price'] ?? null);

            if (self::isValidNonNegativeNumeric($startRaw)) {
                $startFormatted = core()->formatPrice((float) $startRaw);
                $priceDisplay = $unit ? "Starting from {$startFormatted} / {$unit}" : "Starting from {$startFormatted}";

                return self::buildPricingPayload(self::MODE_STARTING_FROM, [
                    'is_starting_from'    => true,
                    'min_price_formatted' => $startFormatted,
                    'price_display'       => $priceDisplay,
                    'unit'                => $unit,
                    'moq'                 => $moqFormatted,
                    'moq_display'         => $moqDisplay,
                    'supply_availability' => $availability,
                    'disclaimer'          => $disclaimer,
                ], $configEntry);
            }
        }

        if (
            $mode === self::MODE_RANGE
            && self::isValidNonNegativeNumeric($minRaw)
            && self::isValidNonNegativeNumeric($maxRaw)
            && (float) $maxRaw >= (float) $minRaw
        ) {
            $minFormatted = core()->formatPrice((float) $minRaw);
            $maxFormatted = core()->formatPrice((float) $maxRaw);
            $rangeText = "{$minFormatted} – {$maxFormatted}";
            $priceDisplay = $unit ? "{$rangeText} / {$unit}" : $rangeText;

            return self::buildPricingPayload(self::MODE_RANGE, [
                'is_range'            => true,
                'min_price_formatted' => $minFormatted,
                'max_price_formatted' => $maxFormatted,
                'price_display'       => $priceDisplay,
                'unit'                => $unit,
                'moq'                 => $moqFormatted,
                'moq_display'         => $moqDisplay,
                'supply_availability' => $availability,
                'disclaimer'          => $disclaimer,
            ], $configEntry);
        }

        return self::buildPricingPayload(self::MODE_BULK_CONTACT, [
            'is_contact'          => true,
            'price_display'       => 'Contact for Price',
            'unit'                => $unit,
            'moq'                 => $moqFormatted,
            'moq_display'         => $moqDisplay,
            'supply_availability' => $availability,
            'disclaimer'          => $disclaimer,
        ], $configEntry);
    }

    /**
     * Pricing model detection.
     *
     * Model A (native Bagisto pricing): no wholesale pricing mode configured on the product or centralized config.
     * Model B (wholesale pricing): `price_display_mode` or centralized config defines `FIXED_PRICE`,
     * `STARTING_FROM`, `BULK_CONTACT`, `range`, or `contact`.
     *
     * Variants inherit Model B from their parent product.
     */
    public static function usesWholesalePricing($product): bool
    {
        if (! $product) {
            return false;
        }

        $configEntry = self::resolveConfigEntry($product);

        if (self::resolveEffectiveMode($product, $configEntry) !== null) {
            return true;
        }

        if (! empty($product->parent_id) && $product->parent) {
            return self::usesWholesalePricing($product->parent);
        }

        return false;
    }

    /**
     * Build a normalized pricing view-model from centralized mode metadata and product config.
     */
    protected static function buildPricingPayload(string $mode, array $overrides, array $configEntry): array
    {
        $modesMeta = config('wholesale_pricing.modes', []);
        $metaKey = $mode === self::MODE_RANGE ? self::MODE_STARTING_FROM : $mode;
        $meta = $modesMeta[$metaKey] ?? [];

        return array_merge([
            'mode'                  => $mode,
            'mode_label'            => $meta['label'] ?? ($mode === self::MODE_RANGE ? 'Price Range' : 'Bulk Contact / Custom Quote'),
            'headline_label'        => $meta['headline'] ?? 'Wholesale Pricing',
            'badge_label'           => $meta['badge'] ?? 'Wholesale Pricing',
            'is_fixed'              => false,
            'is_starting_from'      => false,
            'is_range'              => false,
            'is_contact'            => false,
            'fixed_price_formatted' => null,
            'min_price_formatted'   => null,
            'max_price_formatted'   => null,
            'price_display'         => 'Contact for Price',
            'price_note'            => $configEntry['price_note'] ?? ($meta['default_note'] ?? null),
            'unit'                  => null,
            'moq'                   => null,
            'moq_display'           => null,
            'supply_availability'   => null,
            'disclaimer'            => self::DISCLAIMER,
        ], $overrides);
    }

    /**
     * Resolve centralized `config/wholesale_pricing.php` entry for a product by SKU or URL key.
     */
    protected static function resolveConfigEntry($product): array
    {
        if (! $product) {
            return [];
        }

        $productsConfig = config('wholesale_pricing.products', []);

        if (! is_array($productsConfig) || empty($productsConfig)) {
            return [];
        }

        $sku = $product->sku ?? null;

        if ($sku && isset($productsConfig[$sku]) && is_array($productsConfig[$sku])) {
            return $productsConfig[$sku];
        }

        $urlKey = $product->url_key ?? null;

        if ($urlKey && isset($productsConfig[$urlKey]) && is_array($productsConfig[$urlKey])) {
            return $productsConfig[$urlKey];
        }

        return [];
    }

    /**
     * Resolve the effective wholesale display mode from the product EAV attribute or centralized config.
     */
    protected static function resolveEffectiveMode($product, array $configEntry): ?string
    {
        $validModes = array_keys(self::DISPLAY_MODES);

        $rawAttrMode = self::resolveStringValue($product, 'price_display_mode');

        if ($rawAttrMode !== null && in_array($rawAttrMode, $validModes, true)) {
            return $rawAttrMode;
        }

        $configMode = $configEntry['mode'] ?? null;

        if (is_string($configMode) && in_array($configMode, $validModes, true)) {
            return $configMode;
        }

        return null;
    }

    protected static function isValidNonNegativeNumeric(mixed $value): bool
    {
        return $value !== null && $value !== '' && is_numeric($value) && (float) $value >= 0;
    }

    /**
     * Model B products can only be transacted through an approved quotation.
     */
    public static function requiresApprovedQuotation($product): bool
    {
        return self::usesWholesalePricing($product);
    }

    /**
     * Quick Order safe price label for a Model B product (never a raw native/indicative number).
     */
    public static function quickOrderLabel($product): string
    {
        return self::formatForMember($product)['price_display'];
    }

    /**
     * Verify that every Model B line in a prospective transaction is backed by a valid,
     * approved quotation. Model A lines are ignored.
     *
     * Each line is an array with keys:
     *  - product_id        int
     *  - child_product_ids int[]          (optional, configurable/bundle children)
     *  - name              string|null
     *  - qty               float
     *  - base_price        float          effective unit price in base currency
     *  - custom_price      float|null|false  cart `custom_price`; `false` when not applicable (order payload)
     *  - additional        array
     *
     * Nothing client-submitted is trusted: quote ownership, status, expiry, product, quantity and
     * price are all re-read from the database.
     *
     * @return string[] human readable violations (empty when the transaction is allowed)
     */
    public static function quotationViolations(array $lines, $customerId): array
    {
        $violations = [];

        $customer = $customerId ? app(CustomerRepository::class)->find($customerId) : null;

        $usedQuoteItems = [];

        foreach ($lines as $line) {
            $product = Product::query()->with('parent')->find($line['product_id'] ?? null);

            $needsQuote = self::requiresApprovedQuotation($product);

            if (! $needsQuote) {
                foreach ($line['child_product_ids'] ?? [] as $childId) {
                    if (self::requiresApprovedQuotation(Product::query()->with('parent')->find($childId))) {
                        $needsQuote = true;

                        break;
                    }
                }
            }

            if (! $needsQuote) {
                continue;
            }

            $label = $line['name'] ?? ($product?->sku ?? ('#'.($line['product_id'] ?? '?')));

            $reason = self::quotationViolationForLine($line, $customer, $usedQuoteItems);

            if ($reason !== null) {
                $violations[] = "{$label}: {$reason}";
            }
        }

        return $violations;
    }

    /**
     * Quotation violations for a Bagisto cart (see quotationViolations()).
     *
     * @return string[]
     */
    public static function cartQuotationViolations($cart, $customerId): array
    {
        if (! $cart) {
            return [];
        }

        $lines = [];

        foreach ($cart->items()->whereNull('parent_id')->with('children')->get() as $item) {
            $lines[] = [
                'product_id'        => $item->product_id,
                'child_product_ids' => $item->children->pluck('product_id')->all(),
                'name'              => $item->name,
                'qty'               => (float) $item->quantity,
                'base_price'        => (float) $item->base_price,
                'custom_price'      => is_null($item->custom_price) ? null : (float) $item->custom_price,
                'additional'        => $item->additional ?? [],
            ];
        }

        return self::quotationViolations($lines, $customerId);
    }

    /**
     * Validate a single Model B line. Returns a reason string or null when authorized.
     */
    protected static function quotationViolationForLine(array $line, $customer, array &$usedQuoteItems): ?string
    {
        $additional = is_array($line['additional'] ?? null) ? $line['additional'] : [];

        $quoteId = $additional['quote_id'] ?? null;
        $quoteItemId = $additional['quote_item_id'] ?? null;

        if (! $quoteId || ! $quoteItemId) {
            return 'an approved quotation is required before this product can be ordered.';
        }

        if (! $customer) {
            return 'please sign in with the account that owns the approved quotation.';
        }

        /**
         * Ownership: mirror the B2B Suite's own quote scoping (company quotes belong to the
         * company; company users see their company's quotes; others only their own).
         */
        $conditions = ['id' => (int) $quoteId, 'soft_deleted' => 0];

        if ($customer->type === 'company') {
            $conditions['company_id'] = $customer->id;
        } elseif ($company = $customer->companies()->first()) {
            $conditions['company_id'] = $company->id;
        } else {
            $conditions['customer_id'] = $customer->id;
        }

        $quote = app(CustomerQuoteRepository::class)->findOneWhere($conditions);

        if (! $quote) {
            return 'the referenced quotation was not found for your account.';
        }

        if ($quote->order_id || $quote->status === CustomerQuote::STATUS_ORDERED || $quote->state === CustomerQuote::STATE_PURCHASE_ORDER) {
            return 'the referenced quotation has already been ordered.';
        }

        if ($quote->status !== CustomerQuote::STATUS_ACCEPTED) {
            return "the referenced quotation is not approved (current status: {$quote->status}).";
        }

        if ($quote->expiration_date && now()->startOfDay()->gt(Carbon::parse($quote->expiration_date))) {
            return 'the referenced quotation has expired.';
        }

        $quoteItem = $quote->items()->where('id', (int) $quoteItemId)->first();

        if (! $quoteItem) {
            return 'the referenced quotation item does not belong to the quotation.';
        }

        if ((int) $quoteItem->product_id !== (int) ($line['product_id'] ?? 0)) {
            return 'the quotation item does not match this product.';
        }

        $key = $quote->id.':'.$quoteItem->id;

        if (isset($usedQuoteItems[$key])) {
            return 'the same quotation item cannot be used more than once.';
        }

        $usedQuoteItems[$key] = true;

        if (abs((float) ($line['qty'] ?? 0) - (float) $quoteItem->negotiated_qty) > 0.0001) {
            return 'the quantity does not match the approved quotation quantity ('.self::formatQuantity((float) $quoteItem->negotiated_qty).').';
        }

        $approvedPrice = (float) $quoteItem->base_negotiated_price;

        if ($approvedPrice <= 0) {
            return 'the approved quotation does not contain a valid price.';
        }

        $customPrice = $line['custom_price'] ?? false;

        if ($customPrice !== false && (is_null($customPrice) || abs((float) $customPrice - $approvedPrice) > 0.0001)) {
            return 'the cart price does not match the approved quotation price.';
        }

        if (abs((float) ($line['base_price'] ?? 0) - $approvedPrice) > 0.0001) {
            return 'the price does not match the approved quotation price.';
        }

        return null;
    }

    /**
     * Format decimal or integer MOQ cleanly without trailing zeros (e.g. 100.00 -> "100", 0.50 -> "0.5").
     */
    public static function formatQuantity(float $value): string
    {
        if (floor($value) == $value) {
            return number_format($value, 0, '.', ',');
        }

        return rtrim(rtrim(number_format($value, 4, '.', ','), '0'), '.');
    }

    /**
     * Resolve a string attribute value, including fallback if an option ID was ever stored.
     */
    protected static function resolveStringValue($product, string $code): ?string
    {
        $val = $product->{$code} ?? null;

        if ($val === null || $val === '') {
            return null;
        }

        $str = trim((string) $val);

        if ($str === '') {
            return null;
        }

        if (ctype_digit($str)) {
            $opt = AttributeOption::query()->find((int) $str);

            if ($opt && ! empty($opt->admin_name)) {
                return trim((string) $opt->admin_name);
            }
        }

        return $str;
    }
}

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
    public const MODE_RANGE = 'range';

    public const MODE_CONTACT = 'contact';

    public const ATTRIBUTE_GROUP_CODE = 'wholesale_pricing';

    public const ATTRIBUTE_GROUP_NAME = 'Wholesale Pricing & Supply';

    public const DISCLAIMER = 'Displayed prices are indicative wholesale prices only. Final pricing depends on order quantity, product specifications, availability, shipping requirements, and mutual agreement.';

    public const DISPLAY_MODES = [
        self::MODE_RANGE   => 'Price Range',
        self::MODE_CONTACT => 'Contact for Price',
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
                        'default_value' => self::MODE_RANGE,
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
     * - When `price_display_mode` is `contact` (or when wholesale range prices are not configured),
     *   `min_price` and `max_price` are strictly null and never emitted.
     */
    public static function formatForMember($product): array
    {
        $rawMode = self::resolveStringValue($product, 'price_display_mode');
        $mode = in_array($rawMode, [self::MODE_RANGE, self::MODE_CONTACT], true)
            ? $rawMode
            : self::MODE_RANGE;

        $unit = self::resolveStringValue($product, 'wholesale_unit');
        $unit = ($unit !== null && isset(self::UNITS[$unit])) ? self::UNITS[$unit] : ($unit ?: null);

        $availability = self::resolveStringValue($product, 'supply_availability');
        $availability = ($availability !== null && isset(self::AVAILABILITY_OPTIONS[$availability]))
            ? self::AVAILABILITY_OPTIONS[$availability]
            : ($availability ?: null);

        $moqRaw = $product->wholesale_moq;
        $moqFormatted = null;
        $moqDisplay = null;

        if ($moqRaw !== null && $moqRaw !== '' && is_numeric($moqRaw) && (float) $moqRaw > 0) {
            $moqFormatted = self::formatQuantity((float) $moqRaw);
            $moqDisplay = $unit ? "{$moqFormatted} {$unit}" : $moqFormatted;
        }

        $minRaw = $product->wholesale_min_price;
        $maxRaw = $product->wholesale_max_price;

        $hasValidRange = $mode === self::MODE_RANGE
            && $minRaw !== null && $minRaw !== '' && is_numeric($minRaw) && (float) $minRaw >= 0
            && $maxRaw !== null && $maxRaw !== '' && is_numeric($maxRaw) && (float) $maxRaw >= (float) $minRaw;

        if ($hasValidRange) {
            $minFormatted = core()->formatPrice((float) $minRaw);
            $maxFormatted = core()->formatPrice((float) $maxRaw);

            $rangeText = "{$minFormatted} – {$maxFormatted}";
            $priceDisplay = $unit ? "{$rangeText} / {$unit}" : $rangeText;

            return [
                'mode'                => self::MODE_RANGE,
                'is_range'            => true,
                'is_contact'          => false,
                'min_price_formatted' => $minFormatted,
                'max_price_formatted' => $maxFormatted,
                'price_display'       => $priceDisplay,
                'unit'                => $unit,
                'moq'                 => $moqFormatted,
                'moq_display'         => $moqDisplay,
                'supply_availability' => $availability,
                'disclaimer'          => self::DISCLAIMER,
            ];
        }

        return [
            'mode'                => self::MODE_CONTACT,
            'is_range'            => false,
            'is_contact'          => true,
            'min_price_formatted' => null,
            'max_price_formatted' => null,
            'price_display'       => 'Contact for Price',
            'unit'                => $unit,
            'moq'                 => $moqFormatted,
            'moq_display'         => $moqDisplay,
            'supply_availability' => $availability,
            'disclaimer'          => self::DISCLAIMER,
        ];
    }

    /**
     * Pricing model detection.
     *
     * Model A (native Bagisto pricing): `price_display_mode` is empty / null / "native" / anything else.
     * Model B (wholesale indicative pricing): `price_display_mode` is `range` or `contact`.
     *
     * Variants inherit Model B from their parent product.
     */
    public static function usesWholesalePricing($product): bool
    {
        if (! $product) {
            return false;
        }

        $mode = self::resolveStringValue($product, 'price_display_mode');

        if (in_array($mode, [self::MODE_RANGE, self::MODE_CONTACT], true)) {
            return true;
        }

        if (! empty($product->parent_id) && $product->parent) {
            return self::usesWholesalePricing($product->parent);
        }

        return false;
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
        $val = $product->{$code};

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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySupplySection extends Model
{
    use HasFactory;

    protected $table = 'company_supply_sections';

    public const SECTION_COMPANY_OVERVIEW         = 'company_overview';
    public const SECTION_MANUFACTURING_SOURCING    = 'manufacturing_sourcing';
    public const SECTION_SUPPLY_CAPABILITIES       = 'supply_capabilities';
    public const SECTION_QUALITY_PRODUCT_HANDLING  = 'quality_product_handling';
    public const SECTION_INVENTORY_FULFILLMENT     = 'inventory_fulfillment';
    public const SECTION_SUPPLY_COOPERATION       = 'supply_cooperation';

    public const DEFAULT_SECTIONS = [
        self::SECTION_COMPANY_OVERVIEW => [
            'title'          => 'Company Overview',
            'guidance_notes' => 'Introduce the company, business operating model, company background, and dedicated wholesale member contact channels.',
            'sort_order'     => 1,
        ],
        self::SECTION_MANUFACTURING_SOURCING => [
            'title'          => 'Manufacturing & Sourcing',
            'guidance_notes' => 'Detail manufacturing partner arrangements, raw material sourcing standards, production facility protocols, and available processing capabilities.',
            'sort_order'     => 2,
        ],
        self::SECTION_SUPPLY_CAPABILITIES => [
            'title'          => 'Supply Capabilities',
            'guidance_notes' => 'Outline commercial supply capabilities, batch availability, replenishment schedules, supply planning, and capacity details.',
            'sort_order'     => 3,
        ],
        self::SECTION_QUALITY_PRODUCT_HANDLING => [
            'title'          => 'Quality & Product Handling',
            'guidance_notes' => 'Describe quality assurance protocols, third-party testing, purity verification, climate-controlled storage practices, and references to available certificates.',
            'sort_order'     => 4,
        ],
        self::SECTION_INVENTORY_FULFILLMENT => [
            'title'          => 'Inventory & Fulfillment',
            'guidance_notes' => 'Explain the US inventory operating model, domestic distribution points, packing standards, carrier coordination, and verified lead times.',
            'sort_order'     => 5,
        ],
        self::SECTION_SUPPLY_COOPERATION => [
            'title'          => 'Supply Cooperation',
            'guidance_notes' => 'Define wholesale coordination expectations, distributor communication workflows, escalation paths, and commercial partnership principles.',
            'sort_order'     => 6,
        ],
    ];

    protected $fillable = [
        'page_id',
        'section_key',
        'title',
        'guidance_notes',
        'content',
        'sort_order',
        'is_enabled',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_enabled' => 'boolean',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(CompanySupplyPage::class, 'page_id');
    }

    public function hasContent(): bool
    {
        return ! empty(trim((string) $this->content));
    }
}


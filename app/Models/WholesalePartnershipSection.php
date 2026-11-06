<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WholesalePartnershipSection extends Model
{
    use HasFactory;

    protected $table = 'wholesale_partnership_sections';

    public const SECTION_US_WHOLESALE_BUYER     = 'us_wholesale_buyer_partnership';
    public const SECTION_US_DISTRIBUTOR         = 'us_distributor_partnership';
    public const SECTION_MANUFACTURING_PARTNER  = 'manufacturing_partner';
    public const SECTION_PARTNERSHIP_REQ        = 'partnership_requirements';
    public const SECTION_ONBOARDING_PROCESS     = 'partnership_onboarding_process';
    public const SECTION_COOPERATION_MODELS     = 'commercial_cooperation_models';

    public const DEFAULT_SECTIONS = [
        self::SECTION_US_WHOLESALE_BUYER => [
            'title'          => 'US Wholesale Buyer Partnership',
            'guidance_notes' => 'Overview of commercial wholesale purchasing, buyer profiles, registration and verification prerequisites, product discussion, and transaction workflow.',
            'sort_order'     => 1,
        ],
        self::SECTION_US_DISTRIBUTOR => [
            'title'          => 'US Distributor Partnership',
            'guidance_notes' => 'Distributor cooperation overview, qualifications, distribution and inventory coordination, logistics fulfillment, and territory arrangements subject to written agreement.',
            'sort_order'     => 2,
        ],
        self::SECTION_MANUFACTURING_PARTNER => [
            'title'          => 'Manufacturing Partner',
            'guidance_notes' => 'Manufacturing cooperation arrangements, production scheduling, raw material sourcing standards, quality parameters, and partner communication workflow.',
            'sort_order'     => 3,
        ],
        self::SECTION_PARTNERSHIP_REQ => [
            'title'          => 'Partnership Requirements',
            'guidance_notes' => 'Required corporate identity verification, tax documentation, business credentials, confidentiality terms, and distinctions between general registration, buyer verification, and partner evaluation.',
            'sort_order'     => 4,
        ],
        self::SECTION_ONBOARDING_PROCESS => [
            'title'          => 'Partnership Onboarding Process',
            'guidance_notes' => 'Step-by-step onboarding journey: registration, operational review, model selection, administration contact, credential review, agreement preparation, and partnership activation.',
            'sort_order'     => 5,
        ],
        self::SECTION_COOPERATION_MODELS => [
            'title'          => 'Commercial Cooperation Models',
            'guidance_notes' => 'High-level structures: wholesale purchasing, distributor distribution, manufacturing collaboration, and long-term supply planning. All terms subject to individual contract.',
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
        return $this->belongsTo(WholesalePartnershipPage::class, 'page_id');
    }

    public function hasContent(): bool
    {
        return ! empty(trim((string) $this->content));
    }
}


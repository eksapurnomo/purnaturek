<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\User\Models\Admin;

class CompanySupplyPage extends Model
{
    use HasFactory;

    protected $table = 'company_supply_pages';

    public const STATUS_DRAFT       = 'draft';
    public const STATUS_PUBLISHED   = 'published';
    public const STATUS_UNPUBLISHED = 'unpublished';

    public const STATUSES = [
        self::STATUS_DRAFT       => 'Draft',
        self::STATUS_PUBLISHED   => 'Published',
        self::STATUS_UNPUBLISHED => 'Unpublished',
    ];

    protected $fillable = [
        'title',
        'description',
        'status',
        'published_at',
        'updated_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(CompanySupplySection::class, 'page_id')->orderBy('sort_order', 'asc');
    }

    public function enabledSections(): HasMany
    {
        return $this->sections()
            ->where('is_enabled', true)
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->orderBy('sort_order', 'asc');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isUnpublished(): bool
    {
        return $this->status === self::STATUS_UNPUBLISHED;
    }

    public static function getOrCreateDefault(): self
    {
        $page = self::first();

        if (! $page) {
            $page = self::create([
                'title'       => 'Company & Supply Information',
                'description' => 'Official company operations, manufacturing arrangements, quality standards, inventory model, and supply cooperation guidelines for wholesale members.',
                'status'      => self::STATUS_DRAFT,
            ]);

            foreach (CompanySupplySection::DEFAULT_SECTIONS as $key => $meta) {
                $page->sections()->create([
                    'section_key'    => $key,
                    'title'          => $meta['title'],
                    'guidance_notes' => $meta['guidance_notes'],
                    'content'        => null,
                    'sort_order'     => $meta['sort_order'],
                    'is_enabled'     => true,
                ]);
            }
        }

        return $page;
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\User\Models\Admin;

class ContractTemplate extends Model
{
    use HasFactory;

    protected $table = 'contract_templates';

    public const CATEGORY_US_WHOLESALE_BUYER = 'us_wholesale_buyer';
    public const CATEGORY_NDA                = 'nda';
    public const CATEGORY_US_DISTRIBUTOR     = 'us_distributor';
    public const CATEGORY_CUSTOM             = 'custom';

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED  = 'archived';

    public const CATEGORIES = [
        self::CATEGORY_US_WHOLESALE_BUYER => 'US Wholesale Buyer Agreement',
        self::CATEGORY_NDA                => 'Non-Disclosure Agreement (NDA)',
        self::CATEGORY_US_DISTRIBUTOR     => 'US Distributor Agreement',
        self::CATEGORY_CUSTOM             => 'Custom Agreement Template',
    ];

    public const STATUSES = [
        self::STATUS_DRAFT     => 'Draft',
        self::STATUS_PUBLISHED => 'Published',
        self::STATUS_ARCHIVED  => 'Archived',
    ];

    protected $fillable = [
        'title',
        'identifier',
        'category',
        'description',
        'version',
        'original_filename',
        'storage_path',
        'mime_type',
        'file_size',
        'status',
        'member_accessible',
        'effective_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'member_accessible' => 'boolean',
        'effective_date'    => 'date',
        'file_size'         => 'integer',
    ];

    public function memberContracts(): HasMany
    {
        return $this->hasMany(MemberContract::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeMemberAccessible(Builder $query): Builder
    {
        return $query->where('member_accessible', true);
    }

    public function hasFile(): bool
    {
        return !empty($this->storage_path);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    public function formattedFileSize(): string
    {
        if (!$this->file_size) {
            return '—';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}


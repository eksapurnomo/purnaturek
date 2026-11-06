<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Product\Models\Product;

class ProductDocument extends Model
{
    use HasFactory;

    protected $table = 'product_documents';

    public const TYPE_COA = 'coa';
    public const TYPE_QUALITY = 'quality';
    public const TYPE_TDS = 'tds';
    public const TYPE_PRODUCT_SHEET = 'product_sheet';
    public const TYPE_OTHER = 'other';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    public const DOCUMENT_TYPES = [
        self::TYPE_COA           => 'Certificate of Analysis (COA)',
        self::TYPE_QUALITY       => 'Quality Document',
        self::TYPE_TDS           => 'Technical Data Sheet (TDS)',
        self::TYPE_PRODUCT_SHEET => 'Product Information Sheet',
        self::TYPE_OTHER         => 'Other Technical Document',
    ];

    public const STATUSES = [
        self::STATUS_DRAFT     => 'Draft',
        self::STATUS_PUBLISHED => 'Published',
    ];

    public const CATEGORY_COA_QUALITY = 'coa_quality';
    public const CATEGORY_TECHNICAL = 'technical';
    public const CATEGORY_OTHER = 'other';

    public const CATEGORY_GROUPS = [
        self::CATEGORY_COA_QUALITY => [
            'title' => 'Certificate of Analysis & Quality Documents',
            'types' => [self::TYPE_COA, self::TYPE_QUALITY],
        ],
        self::CATEGORY_TECHNICAL => [
            'title' => 'Technical Data Sheets & Product Sheets',
            'types' => [self::TYPE_TDS, self::TYPE_PRODUCT_SHEET],
        ],
        self::CATEGORY_OTHER => [
            'title' => 'Other Technical Documentation',
            'types' => [self::TYPE_OTHER],
        ],
    ];

    protected $fillable = [
        'product_id',
        'title',
        'document_type',
        'description',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'version',
        'issue_date',
        'expiry_date',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'expiry_date' => 'date',
        'file_size'   => 'integer',
        'sort_order'  => 'integer',
    ];

    /**
     * Relationship: Associated Bagisto Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Scope: Only published documents (member visible)
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope: Filter by product ID
     */
    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Check if published
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Get human-readable type label
     */
    public function getTypeLabelAttribute(): string
    {
        return self::DOCUMENT_TYPES[$this->document_type] ?? ucfirst($this->document_type);
    }

    /**
     * Get human-readable status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Format file size in KB or MB
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }

    /**
     * Get category group key
     */
    public function getCategoryKeyAttribute(): string
    {
        foreach (self::CATEGORY_GROUPS as $key => $group) {
            if (in_array($this->document_type, $group['types'], true)) {
                return $key;
            }
        }

        return self::CATEGORY_OTHER;
    }
}


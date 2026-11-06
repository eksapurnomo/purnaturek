<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Customer\Models\Customer;
use Webkul\User\Models\Admin;

class BuyerVerification extends Model
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUS_LABELS = [
        self::STATUS_PENDING  => 'Pending Review',
        self::STATUS_APPROVED => 'Verified Wholesale Buyer',
        self::STATUS_REJECTED => 'Verification Rejected',
    ];

    public const BUSINESS_TYPES = [
        'distributor'  => 'Wholesale Distributor',
        'retailer'     => 'Retailer / Pharmacy',
        'clinic'       => 'Clinic / Medical Practice',
        'manufacturer' => 'Formulator / Manufacturer',
        'ecommerce'    => 'E-Commerce Retailer',
        'other'        => 'Other Commercial Entity',
    ];

    protected $fillable = [
        'customer_id',
        'company_name',
        'tax_id',
        'business_type',
        'business_phone',
        'business_address',
        'website_url',
        'status',
        'rejection_reason',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at'  => 'datetime',
    ];

    /**
     * Business phone alias.
     */
    public function getPhoneAttribute(): ?string
    {
        return $this->business_phone;
    }

    /**
     * Business address alias.
     */
    public function getAddressAttribute(): ?string
    {
        return $this->business_address;
    }

    /**
     * Associated customer record.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Supporting business verification documents.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(BuyerVerificationDocument::class);
    }

    /**
     * Reviewing administrator.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    /**
     * Scope for pending applications.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for approved applications.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for rejected applications.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function getBusinessTypeLabelAttribute(): string
    {
        return self::BUSINESS_TYPES[$this->business_type] ?? ($this->business_type ?: 'Not Specified');
    }
}

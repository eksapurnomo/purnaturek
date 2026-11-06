<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Customer\Models\Customer;
use Webkul\User\Models\Admin;

class MemberContract extends Model
{
    use HasFactory;

    protected $table = 'member_contracts';

    public const STATUS_DRAFT      = 'draft';
    public const STATUS_SENT       = 'sent';
    public const STATUS_ACCEPTED   = 'accepted';
    public const STATUS_REJECTED   = 'rejected';
    public const STATUS_EXPIRED    = 'expired';
    public const STATUS_CANCELLED  = 'cancelled';
    public const STATUS_SUPERSEDED = 'superseded';

    public const STATUSES = [
        self::STATUS_DRAFT      => 'Draft',
        self::STATUS_SENT       => 'Sent (Pending Acknowledgment)',
        self::STATUS_ACCEPTED   => 'Accepted',
        self::STATUS_REJECTED   => 'Rejected',
        self::STATUS_EXPIRED    => 'Expired',
        self::STATUS_CANCELLED  => 'Cancelled',
        self::STATUS_SUPERSEDED => 'Superseded',
    ];

    public const STATUS_BADGES = [
        self::STATUS_DRAFT      => 'badge-secondary',
        self::STATUS_SENT       => 'badge-warning',
        self::STATUS_ACCEPTED   => 'badge-success',
        self::STATUS_REJECTED   => 'badge-danger',
        self::STATUS_EXPIRED    => 'badge-secondary',
        self::STATUS_CANCELLED  => 'badge-secondary',
        self::STATUS_SUPERSEDED => 'badge-info',
    ];

    protected $fillable = [
        'reference_number',
        'title',
        'customer_id',
        'template_id',
        'template_version',
        'original_filename',
        'storage_path',
        'mime_type',
        'file_size',
        'file_hash',
        'status',
        'issued_date',
        'effective_date',
        'expires_at',
        'sent_at',
        'responded_at',
        'member_notes',
        'admin_notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'issued_date'    => 'date',
        'effective_date' => 'date',
        'expires_at'     => 'datetime',
        'sent_at'        => 'datetime',
        'responded_at'   => 'datetime',
        'file_size'      => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ContractTemplate::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(MemberContractEvent::class, 'member_contract_id')->orderBy('id', 'asc');
    }

    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }

    public function evaluateExpiry(): bool
    {
        if ($this->status === self::STATUS_SENT && $this->expires_at && $this->expires_at->isPast()) {
            $this->status = self::STATUS_EXPIRED;
            $this->save();

            $this->events()->create([
                'event'       => MemberContractEvent::EVENT_EXPIRED,
                'actor_type'  => MemberContractEvent::ACTOR_SYSTEM,
                'notes'       => 'Contract automatically marked as expired upon expiry date passing.',
                'metadata'    => ['expired_at' => Carbon::now()->toIso8601String()],
                'created_at'  => Carbon::now(),
            ]);

            return true;
        }

        return false;
    }

    public function canBeActionedByMember(): bool
    {
        $this->evaluateExpiry();

        return $this->status === self::STATUS_SENT;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isSuperseded(): bool
    {
        return $this->status === self::STATUS_SUPERSEDED;
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


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberContractEvent extends Model
{
    use HasFactory;

    protected $table = 'member_contract_events';

    public $timestamps = false;

    public const EVENT_CREATED    = 'created';
    public const EVENT_UPDATED    = 'updated';
    public const EVENT_SENT       = 'sent';
    public const EVENT_VIEWED     = 'viewed';
    public const EVENT_ACCEPTED   = 'accepted';
    public const EVENT_REJECTED   = 'rejected';
    public const EVENT_EXPIRED    = 'expired';
    public const EVENT_CANCELLED  = 'cancelled';
    public const EVENT_SUPERSEDED = 'superseded';

    public const ACTOR_ADMIN    = 'admin';
    public const ACTOR_CUSTOMER = 'customer';
    public const ACTOR_SYSTEM   = 'system';

    public const EVENT_LABELS = [
        self::EVENT_CREATED    => 'Contract Created',
        self::EVENT_UPDATED    => 'Contract Updated',
        self::EVENT_SENT       => 'Contract Sent to Member',
        self::EVENT_VIEWED     => 'Contract Viewed / Downloaded',
        self::EVENT_ACCEPTED   => 'Contract Accepted by Member',
        self::EVENT_REJECTED   => 'Contract Rejected by Member',
        self::EVENT_EXPIRED    => 'Contract Expired',
        self::EVENT_CANCELLED  => 'Contract Cancelled by Admin',
        self::EVENT_SUPERSEDED => 'Contract Superseded',
    ];

    protected $fillable = [
        'member_contract_id',
        'event',
        'actor_type',
        'actor_id',
        'actor_name',
        'actor_email',
        'ip_address',
        'user_agent',
        'file_hash',
        'notes',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(MemberContract::class, 'member_contract_id');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Customer\Models\Customer;
use Webkul\User\Models\Admin;

class MemberConversationMessage extends Model
{
    use HasFactory;

    protected $table = 'member_conversation_messages';

    public const SENDER_CUSTOMER = 'customer';
    public const SENDER_ADMIN = 'admin';

    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_id',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Relationship: The conversation this message belongs to
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(MemberConversation::class, 'conversation_id');
    }

    /**
     * Check if sent by member/customer
     */
    public function isFromCustomer(): bool
    {
        return $this->sender_type === self::SENDER_CUSTOMER;
    }

    /**
     * Check if sent by admin
     */
    public function isFromAdmin(): bool
    {
        return $this->sender_type === self::SENDER_ADMIN;
    }

    /**
     * Get sender display name
     */
    public function getSenderNameAttribute(): string
    {
        if ($this->isFromAdmin()) {
            return 'PurNatureK Administration';
        }

        $customer = Customer::find($this->sender_id);
        if ($customer) {
            return trim($customer->first_name . ' ' . $customer->last_name) ?: $customer->email;
        }

        return 'Wholesale Member';
    }
}


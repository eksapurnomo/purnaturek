<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Webkul\Customer\Models\Customer;

class MemberConversation extends Model
{
    use HasFactory;

    protected $table = 'member_conversations';

    public const STATUS_WAITING_ADMIN = 'waiting_admin';
    public const STATUS_WAITING_MEMBER = 'waiting_member';
    public const STATUS_CLOSED = 'closed';

    public const CATEGORY_PRODUCT_INQUIRY = 'product_inquiry';
    public const CATEGORY_PRICING_INQUIRY = 'pricing_inquiry';
    public const CATEGORY_SUPPLY_AVAILABILITY = 'supply_availability';
    public const CATEGORY_PRODUCT_DOCUMENTATION = 'product_documentation';
    public const CATEGORY_PARTNERSHIP_INQUIRY = 'partnership_inquiry';
    public const CATEGORY_ORDER_SUPPORT = 'order_support';
    public const CATEGORY_GENERAL_INQUIRY = 'general_inquiry';

    public const STATUSES = [
        self::STATUS_WAITING_ADMIN  => 'Waiting on Admin',
        self::STATUS_WAITING_MEMBER => 'Waiting on Member',
        self::STATUS_CLOSED         => 'Closed',
    ];

    public const CATEGORIES = [
        self::CATEGORY_PRODUCT_INQUIRY       => 'Product Inquiry',
        self::CATEGORY_PRICING_INQUIRY       => 'Wholesale Pricing Inquiry',
        self::CATEGORY_SUPPLY_AVAILABILITY   => 'Supply & Availability',
        self::CATEGORY_PRODUCT_DOCUMENTATION => 'Product Documentation',
        self::CATEGORY_PARTNERSHIP_INQUIRY   => 'Partnership Inquiry',
        self::CATEGORY_ORDER_SUPPORT         => 'Order Support',
        self::CATEGORY_GENERAL_INQUIRY       => 'General Inquiry',
    ];

    protected $fillable = [
        'customer_id',
        'subject',
        'category',
        'status',
        'last_message_at',
        'last_sender_type',
        'unread_for_admin_count',
        'unread_for_member_count',
    ];

    protected $casts = [
        'last_message_at'        => 'datetime',
        'unread_for_admin_count'  => 'integer',
        'unread_for_member_count' => 'integer',
    ];

    /**
     * Relationship: The member who owns the conversation
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Relationship: Messages in this conversation in chronological order
     */
    public function messages(): HasMany
    {
        return $this->hasMany(MemberConversationMessage::class, 'conversation_id')->orderBy('created_at', 'asc');
    }

    /**
     * Relationship: Latest message
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(MemberConversationMessage::class, 'conversation_id')->latestOfMany();
    }

    /**
     * Scope: Filter by customer
     */
    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope: Only open / active conversations
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_CLOSED);
    }

    /**
     * Scope: Closed conversations
     */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    /**
     * Check if conversation is closed
     */
    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    /**
     * Check if conversation is open / active
     */
    public function isOpen(): bool
    {
        return ! $this->isClosed();
    }

    /**
     * Check if a member can reply (only open conversations)
     */
    public function canMemberReply(): bool
    {
        return $this->isOpen();
    }

    /**
     * Category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category));
    }

    /**
     * Status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }
}


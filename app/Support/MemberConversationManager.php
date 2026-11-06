<?php

namespace App\Support;

use App\Mail\AdminConversationReplied;
use App\Mail\MemberConversationCreated;
use App\Mail\MemberConversationReplied;
use App\Models\MemberConversation;
use App\Models\MemberConversationMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Webkul\Customer\Models\Customer;
use Webkul\User\Models\Admin;

class MemberConversationManager
{
    public const RATE_LIMIT_MAX_ATTEMPTS = 15; // 15 messages per 60 seconds
    public const RATE_LIMIT_DECAY_SECONDS = 60;

    /**
     * Create a new conversation initiated by a member.
     */
    public static function createConversation(Customer $customer, array $data): MemberConversation
    {
        self::enforceRateLimit($customer->id);

        return DB::transaction(function () use ($customer, $data) {
            $now = now();

            $conversation = MemberConversation::create([
                'customer_id'             => $customer->id,
                'subject'                 => trim($data['subject']),
                'category'                => $data['category'],
                'status'                  => MemberConversation::STATUS_WAITING_ADMIN,
                'last_message_at'         => $now,
                'last_sender_type'        => MemberConversationMessage::SENDER_CUSTOMER,
                'unread_for_admin_count'  => 1,
                'unread_for_member_count' => 0,
            ]);

            $message = MemberConversationMessage::create([
                'conversation_id' => $conversation->id,
                'sender_type'     => MemberConversationMessage::SENDER_CUSTOMER,
                'sender_id'       => $customer->id,
                'message'         => trim($data['message']),
                'is_read'         => false,
            ]);

            self::sendAdminNotification(new MemberConversationCreated($conversation, $message));

            return $conversation;
        });
    }

    /**
     * Reply to an open conversation as a member.
     */
    public static function replyAsMember(MemberConversation $conversation, Customer $customer, string $messageText): MemberConversationMessage
    {
        if ($conversation->customer_id !== $customer->id) {
            throw new HttpException(403, 'Unauthorized access to conversation.');
        }

        if ($conversation->isClosed()) {
            throw new HttpException(422, 'Cannot reply to a closed conversation.');
        }

        self::enforceRateLimit($customer->id);

        return DB::transaction(function () use ($conversation, $customer, $messageText) {
            $now = now();

            $message = MemberConversationMessage::create([
                'conversation_id' => $conversation->id,
                'sender_type'     => MemberConversationMessage::SENDER_CUSTOMER,
                'sender_id'       => $customer->id,
                'message'         => trim($messageText),
                'is_read'         => false,
            ]);

            $conversation->update([
                'status'                 => MemberConversation::STATUS_WAITING_ADMIN,
                'last_message_at'        => $now,
                'last_sender_type'       => MemberConversationMessage::SENDER_CUSTOMER,
                'unread_for_admin_count' => $conversation->unread_for_admin_count + 1,
            ]);

            self::sendAdminNotification(new MemberConversationReplied($conversation, $message));

            return $message;
        });
    }

    /**
     * Reply to a conversation as an admin.
     */
    public static function replyAsAdmin(MemberConversation $conversation, Admin $admin, string $messageText, ?string $newStatus = null): MemberConversationMessage
    {
        return DB::transaction(function () use ($conversation, $admin, $messageText, $newStatus) {
            $now = now();

            $message = MemberConversationMessage::create([
                'conversation_id' => $conversation->id,
                'sender_type'     => MemberConversationMessage::SENDER_ADMIN,
                'sender_id'       => $admin->id,
                'message'         => trim($messageText),
                'is_read'         => false,
            ]);

            $statusToSet = $newStatus ?: MemberConversation::STATUS_WAITING_MEMBER;

            $conversation->update([
                'status'                  => $statusToSet,
                'last_message_at'         => $now,
                'last_sender_type'        => MemberConversationMessage::SENDER_ADMIN,
                'unread_for_member_count' => $conversation->unread_for_member_count + 1,
            ]);

            self::sendMemberNotification($conversation->customer, new AdminConversationReplied($conversation, $message));

            return $message;
        });
    }

    /**
     * Change conversation status as an admin.
     */
    public static function updateStatusAsAdmin(MemberConversation $conversation, string $status): MemberConversation
    {
        if (! in_array($status, array_keys(MemberConversation::STATUSES), true)) {
            throw new HttpException(422, 'Invalid conversation status.');
        }

        $conversation->update(['status' => $status]);

        return $conversation->fresh();
    }

    /**
     * Mark unread admin messages as read when a member opens the conversation.
     */
    public static function markAsReadByMember(MemberConversation $conversation, Customer $customer): void
    {
        if ($conversation->customer_id !== $customer->id) {
            return;
        }

        MemberConversationMessage::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender_type', MemberConversationMessage::SENDER_ADMIN)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $conversation->update(['unread_for_member_count' => 0]);
    }

    /**
     * Mark unread member messages as read when an admin opens the conversation.
     */
    public static function markAsReadByAdmin(MemberConversation $conversation): void
    {
        MemberConversationMessage::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender_type', MemberConversationMessage::SENDER_CUSTOMER)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $conversation->update(['unread_for_admin_count' => 0]);
    }

    /**
     * Get unread message count for a member across all their conversations.
     */
    public static function getUnreadCountForMember(Customer $customer): int
    {
        return (int) MemberConversation::query()
            ->forCustomer($customer->id)
            ->sum('unread_for_member_count');
    }

    /**
     * Get unread message count for administrators across all conversations.
     */
    public static function getUnreadCountForAdmin(): int
    {
        return (int) MemberConversation::query()->sum('unread_for_admin_count');
    }

    /**
     * Check and enforce rate limiting per customer.
     */
    public static function enforceRateLimit(int $customerId): void
    {
        $throttleKey = 'member-msg:' . $customerId;

        if (RateLimiter::tooManyAttempts($throttleKey, self::RATE_LIMIT_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw new HttpException(429, "Too many messages sent. Please wait {$seconds} seconds before sending another message.");
        }

        RateLimiter::hit($throttleKey, self::RATE_LIMIT_DECAY_SECONDS);
    }

    /**
     * Send email notification to administrators safely.
     */
    protected static function sendAdminNotification($mailable): void
    {
        try {
            $adminEmail = config('mail.admin_address')
                ?: config('mail.from.address')
                ?: Admin::first()?->email;

            if ($adminEmail) {
                Mail::to($adminEmail)->send($mailable);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send admin messaging notification email: ' . $e->getMessage());
        }
    }

    /**
     * Send email notification to a member safely.
     */
    protected static function sendMemberNotification(?Customer $customer, $mailable): void
    {
        if (! $customer || ! $customer->email) {
            return;
        }

        try {
            Mail::to($customer->email)->send($mailable);
        } catch (\Throwable $e) {
            Log::warning('Failed to send member messaging notification email: ' . $e->getMessage());
        }
    }
}


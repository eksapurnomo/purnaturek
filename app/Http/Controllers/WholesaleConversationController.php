<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberConversationCreateRequest;
use App\Http\Requests\MemberConversationReplyRequest;
use App\Models\MemberConversation;
use App\Support\MemberConversationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Shop\Http\Controllers\Controller;

class WholesaleConversationController extends Controller
{
    /**
     * Display member's conversations listing.
     */
    public function index(): View
    {
        $customer = auth()->guard('customer')->user();

        $conversations = MemberConversation::query()
            ->forCustomer($customer->id)
            ->with('latestMessage')
            ->orderBy('last_message_at', 'desc')
            ->paginate(15);

        $unreadCount = MemberConversationManager::getUnreadCountForMember($customer);

        return view('shop::customers.account.conversations.index', compact('conversations', 'unreadCount'));
    }

    /**
     * Show form to initiate a new conversation.
     */
    public function create(Request $request): View
    {
        $categories = MemberConversation::CATEGORIES;
        $prefilledSubject = (string) $request->query('subject', '');
        $prefilledCategory = (string) $request->query('category', MemberConversation::CATEGORY_GENERAL_INQUIRY);

        return view('shop::customers.account.conversations.create', compact(
            'categories',
            'prefilledSubject',
            'prefilledCategory'
        ));
    }

    /**
     * Store and dispatch a new conversation.
     */
    public function store(MemberConversationCreateRequest $request): RedirectResponse
    {
        $customer = auth()->guard('customer')->user();

        $conversation = MemberConversationManager::createConversation($customer, $request->validated());

        session()->flash('success', 'Your message has been sent to PurNatureK administration. We will review and respond promptly.');

        return redirect()->route('shop.customers.account.conversations.view', $conversation->id);
    }

    /**
     * View conversation detail and thread history.
     */
    public function view(int $id): View
    {
        $customer = auth()->guard('customer')->user();

        $conversation = MemberConversation::query()
            ->forCustomer($customer->id)
            ->with(['messages', 'customer'])
            ->findOrFail($id);

        MemberConversationManager::markAsReadByMember($conversation, $customer);

        return view('shop::customers.account.conversations.view', compact('conversation'));
    }

    /**
     * Submit a member reply to an open conversation.
     */
    public function reply(MemberConversationReplyRequest $request, int $id): RedirectResponse
    {
        $customer = auth()->guard('customer')->user();

        $conversation = MemberConversation::query()
            ->forCustomer($customer->id)
            ->findOrFail($id);

        MemberConversationManager::replyAsMember($conversation, $customer, $request->input('message'));

        session()->flash('success', 'Your reply has been sent.');

        return redirect()->route('shop.customers.account.conversations.view', $id);
    }
}


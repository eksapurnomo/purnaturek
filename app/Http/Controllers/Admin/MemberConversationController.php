<?php

namespace App\Http\Controllers\Admin;

use App\DataGrids\Admin\MemberConversationDataGrid;
use App\Http\Requests\Admin\AdminConversationReplyRequest;
use App\Http\Requests\Admin\AdminConversationStatusRequest;
use App\Models\MemberConversation;
use App\Support\MemberConversationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;

class MemberConversationController extends Controller
{
    /**
     * Display a listing of member conversations.
     */
    public function index()
    {
        if (request()->ajax()) {
            return app(MemberConversationDataGrid::class)->process();
        }

        $unreadCount = MemberConversationManager::getUnreadCountForAdmin();

        return view('admin.conversations.index', compact('unreadCount'));
    }

    /**
     * Show detailed conversation thread.
     */
    public function view(int $id): View
    {
        $conversation = MemberConversation::with(['messages', 'customer'])->findOrFail($id);

        MemberConversationManager::markAsReadByAdmin($conversation);

        $statuses = MemberConversation::STATUSES;

        return view('admin.conversations.view', compact('conversation', 'statuses'));
    }

    /**
     * Post an administrative reply to the member conversation.
     */
    public function reply(AdminConversationReplyRequest $request, int $id): RedirectResponse
    {
        $conversation = MemberConversation::findOrFail($id);

        MemberConversationManager::replyAsAdmin(
            $conversation,
            auth()->guard('admin')->user(),
            $request->input('message'),
            $request->input('status')
        );

        session()->flash('success', 'Your reply has been sent to the member.');

        return redirect()->route('admin.b2b.conversations.view', $id);
    }

    /**
     * Update the conversation status.
     */
    public function updateStatus(AdminConversationStatusRequest $request, int $id): RedirectResponse
    {
        $conversation = MemberConversation::findOrFail($id);

        MemberConversationManager::updateStatusAsAdmin($conversation, $request->input('status'));

        session()->flash('success', 'Conversation status updated successfully.');

        return redirect()->route('admin.b2b.conversations.view', $id);
    }
}


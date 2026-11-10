{{--
    PurNatureK Members Portal — Conversation Thread Detail
    (shop::customers.account.conversations.view)
--}}
<x-shop::layouts.account>
    <x-slot:title>
        {{ $conversation->subject }} — Message Admin
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="pk-main pk-stack-md" v-pre>
        <div>
            <a href="{{ route('shop.customers.account.conversations.index') }}" class="pk-back-link">
                &larr; Back to Conversations
            </a>
        </div>

        @if (session('success'))
            <x-shop::portal.alert variant="success">
                {{ session('success') }}
            </x-shop::portal.alert>
        @endif

        @if (session('warning'))
            <x-shop::portal.alert variant="warning">
                {{ session('warning') }}
            </x-shop::portal.alert>
        @endif

        @if (isset($errors) && $errors->any())
            <x-shop::portal.alert variant="danger" title="Unable to send reply">
                <ul style="margin: 6px 0 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-shop::portal.alert>
        @endif

        <!-- Conversation Thread Card -->
        <x-shop::portal.card
            :eyebrow="$conversation->category_label . ' · Thread #' . $conversation->id"
            :title="$conversation->subject"
            :subtitle="'Opened on ' . $conversation->created_at->format('M d, Y H:i')"
        >
            <x-slot:actions>
                @if ($conversation->status === 'waiting_admin')
                    <x-shop::portal.badge variant="pending">Waiting on Admin</x-shop::portal.badge>
                @elseif ($conversation->status === 'waiting_member')
                    <x-shop::portal.badge variant="verified">Waiting on You</x-shop::portal.badge>
                @else
                    <x-shop::portal.badge variant="neutral">Closed</x-shop::portal.badge>
                @endif
            </x-slot:actions>

            <!-- Message History Stream -->
            <div class="pk-thread">
                @foreach ($conversation->messages as $msg)
                    <div class="pk-msg {{ $msg->isFromAdmin() ? 'pk-msg--admin' : 'pk-msg--member' }}">
                        <div class="pk-msg__meta">
                            <div style="display: inline-flex; align-items: center; gap: 8px;">
                                <span class="pk-msg__sender">
                                    {{ $msg->isFromAdmin() ? 'PurNatureK Administration' : 'You' }}
                                </span>
                                <x-shop::portal.badge
                                    :variant="$msg->isFromAdmin() ? 'earth' : 'verified'"
                                    size="sm"
                                >
                                    {{ $msg->isFromAdmin() ? 'Admin' : 'Member' }}
                                </x-shop::portal.badge>
                            </div>

                            <span class="pk-mono" style="font-size: 11px;">
                                {{ $msg->created_at->format('M d, Y H:i') }}
                            </span>
                        </div>

                        <div class="pk-msg__body">{{ $msg->message }}</div>
                    </div>
                @endforeach
            </div>

            <!-- Reply Composer -->
            <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--pk-border);">
                @if ($conversation->canMemberReply())
                    <h2 class="pk-serif" style="margin: 0 0 12px; font-size: 22px; font-weight: 600; color: var(--pk-primary);">
                        Post a Reply
                    </h2>

                    <form
                        action="{{ route('shop.customers.account.conversations.reply', $conversation->id) }}"
                        method="POST"
                        id="pk-conversation-reply-form"
                        data-pk-guard-submit="Sending Reply..."
                    >
                        @csrf

                        <div class="pk-field">
                            <label for="reply_message" class="pk-sr-only">Reply message</label>
                            <textarea
                                id="reply_message"
                                name="message"
                                rows="4"
                                placeholder="Type your reply to PurNatureK administration..."
                                class="pk-textarea @error('message') pk-textarea--error @enderror"
                                required
                            >{{ old('message') }}</textarea>
                            @error('message')
                                <span class="pk-error-text">{{ $message }}</span>
                            @enderror
                        </div>

                        <x-shop::portal.button type="submit" variant="primary">
                            Send Reply
                        </x-shop::portal.button>
                    </form>
                @else
                    <x-shop::portal.alert variant="info" title="Thread Closed">
                        This conversation has been closed by administration. If you have further questions, please open a new thread.
                        <x-slot:actions>
                            <x-shop::portal.button
                                :href="route('shop.customers.account.conversations.create')"
                                variant="secondary"
                                size="sm"
                            >
                                Start a New Conversation
                            </x-shop::portal.button>
                        </x-slot:actions>
                    </x-shop::portal.alert>
                @endif
            </div>
        </x-shop::portal.card>
    </div>

    @pushOnce('scripts')
        <script>
            (function () {
                function bindReplyGuard() {
                    var form = document.getElementById('pk-conversation-reply-form');
                    if (!form || form.dataset.pkBound) return;
                    form.dataset.pkBound = '1';

                    form.addEventListener('submit', function (e) {
                        if (form.dataset.pkSubmitted === '1') {
                            e.preventDefault();
                            return;
                        }
                        if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
                            return;
                        }
                        form.dataset.pkSubmitted = '1';
                        var btn = form.querySelector('button[type="submit"]');
                        if (btn) {
                            setTimeout(function () {
                                btn.disabled = true;
                                btn.textContent = form.getAttribute('data-pk-guard-submit') || 'Sending...';
                            }, 0);
                        }
                    });
                }
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', bindReplyGuard);
                } else {
                    bindReplyGuard();
                }
            })();
        </script>
    @endPushOnce
</x-shop::layouts.account>

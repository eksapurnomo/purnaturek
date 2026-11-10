{{--
    PurNatureK Members Portal — Message Admin Conversations Index
    (shop::customers.account.conversations.index)
--}}
<x-shop::layouts.account>
    <x-slot:title>
        Message Admin — PurNatureK Members Portal
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="pk-main pk-stack-md" v-pre>
        <!-- Flash Messages -->
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

        <!-- Page Header -->
        <x-shop::portal.page-header
            eyebrow="Direct Wholesale Desk"
            title="Message Admin"
            subtitle="Direct private communication with PurNatureK administration for product specifications, bulk logistics, documentation, and account support."
        >
            <x-slot:actions>
                @if ($unreadCount > 0)
                    <x-shop::portal.badge variant="action">
                        {{ $unreadCount }} Unread Reply
                    </x-shop::portal.badge>
                @endif

                <x-shop::portal.button
                    :href="route('shop.customers.account.conversations.create')"
                    variant="primary"
                    size="sm"
                >
                    + New Conversation
                </x-shop::portal.button>
            </x-slot:actions>
        </x-shop::portal.page-header>

        <!-- Conversations Panel -->
        <x-shop::portal.card
            eyebrow="Inquiry Threads"
            title="Active & Archived Conversations"
        >
            @if ($conversations->isNotEmpty())
                <div class="pk-convo-list">
                    @foreach ($conversations as $conv)
                        <div class="pk-convo-row">
                            <div class="pk-convo-row__main">
                                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                                    <a
                                        href="{{ route('shop.customers.account.conversations.view', $conv->id) }}"
                                        class="pk-convo-row__title"
                                    >
                                        {{ $conv->subject }}
                                    </a>

                                    @if ($conv->unread_for_member_count > 0)
                                        <x-shop::portal.badge variant="action" size="sm">
                                            New Reply
                                        </x-shop::portal.badge>
                                    @endif
                                </div>

                                <div class="pk-convo-row__meta">
                                    <x-shop::portal.badge variant="earth" size="sm">
                                        {{ $conv->category_label }}
                                    </x-shop::portal.badge>

                                    <span class="pk-mono" style="font-size: 11px;">
                                        #{{ $conv->id }}
                                    </span>

                                    <span>
                                        Last message: {{ $conv->last_message_at ? $conv->last_message_at->format('M d, Y H:i') : $conv->created_at->format('M d, Y H:i') }}
                                    </span>
                                </div>
                            </div>

                            <div class="pk-convo-row__right">
                                @if ($conv->status === 'waiting_admin')
                                    <x-shop::portal.badge variant="pending">
                                        Waiting on Admin
                                    </x-shop::portal.badge>
                                @elseif ($conv->status === 'waiting_member')
                                    <x-shop::portal.badge variant="verified">
                                        Waiting on You
                                    </x-shop::portal.badge>
                                @else
                                    <x-shop::portal.badge variant="neutral">
                                        Closed
                                    </x-shop::portal.badge>
                                @endif

                                <x-shop::portal.button
                                    :href="route('shop.customers.account.conversations.view', $conv->id)"
                                    variant="secondary"
                                    size="sm"
                                >
                                    Open Thread &rarr;
                                </x-shop::portal.button>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($conversations->hasPages())
                    <div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--pk-border);">
                        {{ $conversations->links() }}
                    </div>
                @endif
            @else
                <x-shop::portal.empty-state
                    title="No Conversations Opened Yet"
                    description="Need batch COAs, bulk pricing ranges, sample coordination, or custom logistics terms? Start a private inquiry thread with our wholesale desk."
                >
                    <x-slot:actions>
                        <x-shop::portal.button
                            :href="route('shop.customers.account.conversations.create')"
                            variant="primary"
                        >
                            Start a Conversation
                        </x-shop::portal.button>
                    </x-slot:actions>
                </x-shop::portal.empty-state>
            @endif
        </x-shop::portal.card>
    </div>
</x-shop::layouts.account>

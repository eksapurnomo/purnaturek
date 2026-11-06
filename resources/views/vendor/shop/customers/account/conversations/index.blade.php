<x-shop::layouts.account>
    <x-slot:title>
        Message Admin — PurNatureK Members Portal
    </x-slot>

    <x-shop::layouts.account.navigation />

    <div class="pk-main">
        <div class="flex flex-col gap-6">

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    {{ session('warning') }}
                </div>
            @endif

            <!-- Main Listing Card -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 max-sm:p-4">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-200 pb-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-xl font-bold text-zinc-900">Message Admin</h1>
                            @if ($unreadCount > 0)
                                <span class="rounded-full bg-rose-100 text-rose-800 text-xs font-bold px-2.5 py-0.5">
                                    {{ $unreadCount }} new reply
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-zinc-500 mt-1">
                            Direct private communications, product specifications inquiry, and wholesale account support.
                        </p>
                    </div>

                    <a
                        href="{{ route('shop.customers.account.conversations.create') }}"
                        class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-emerald-800"
                    >
                        + New Conversation
                    </a>
                </div>

                <!-- Conversations List -->
                <div class="mt-6">
                    @if ($conversations->isNotEmpty())
                        <div class="divide-y divide-zinc-200">
                            @foreach ($conversations as $conv)
                                <div class="flex flex-wrap items-center justify-between gap-4 py-4 transition-colors hover:bg-zinc-50/50">
                                    <div class="flex flex-col gap-1 min-w-[240px] flex-1">
                                        <div class="flex items-center gap-2">
                                            <a
                                                href="{{ route('shop.customers.account.conversations.view', $conv->id) }}"
                                                class="font-semibold text-zinc-900 hover:text-emerald-700"
                                            >
                                                {{ $conv->subject }}
                                            </a>

                                            @if ($conv->unread_for_member_count > 0)
                                                <span class="rounded-full bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5">
                                                    New Reply
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex flex-wrap items-center gap-3 text-xs text-zinc-500">
                                            <span class="rounded bg-zinc-100 px-2 py-0.5 font-medium text-zinc-700">
                                                {{ $conv->category_label }}
                                            </span>

                                            <span>
                                                Last message: {{ $conv->last_message_at ? $conv->last_message_at->format('M d, Y H:i') : $conv->created_at->format('M d, Y H:i') }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        @if ($conv->status === 'waiting_admin')
                                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                                Waiting on Admin
                                            </span>
                                        @elseif ($conv->status === 'waiting_member')
                                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                                                Waiting on You
                                            </span>
                                        @else
                                            <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-600">
                                                Closed
                                            </span>
                                        @endif

                                        <a
                                            href="{{ route('shop.customers.account.conversations.view', $conv->id) }}"
                                            class="rounded-lg border border-zinc-300 px-3 py-1.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-100"
                                        >
                                            View &rarr;
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $conversations->links() }}
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-zinc-300 p-8 text-center">
                            <p class="text-sm font-medium text-zinc-700">No conversations opened yet.</p>
                            <p class="text-xs text-zinc-500 mt-1">
                                Need technical specifications, bulk pricing details, or custom shipping terms? Click below to send a message to our wholesale team.
                            </p>
                            <div class="mt-4">
                                <a
                                    href="{{ route('shop.customers.account.conversations.create') }}"
                                    class="inline-flex items-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800"
                                >
                                    Start a Conversation
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-shop::layouts.account>


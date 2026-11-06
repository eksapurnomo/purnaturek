<x-admin::layouts>
    <x-slot:title>
        Message Inbox — Member Conversations
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div>
            <div class="flex items-center gap-3">
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    Message Inbox
                </p>
                @if ($unreadCount > 0)
                    <span class="rounded-full bg-rose-100 text-rose-800 text-xs font-bold px-2.5 py-0.5">
                        {{ $unreadCount }} unread
                    </span>
                @endif
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Direct inquiry tickets, product questions, and support communications from wholesale members.
            </p>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="mt-4 rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 dark:bg-emerald-950 dark:border-emerald-800 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="mt-4 rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 dark:bg-amber-950 dark:border-amber-800 dark:text-amber-300">
            {{ session('warning') }}
        </div>
    @endif

    <div class="mt-6">
        <x-admin::datagrid :src="route('admin.b2b.conversations.index')" />
    </div>
</x-admin::layouts>


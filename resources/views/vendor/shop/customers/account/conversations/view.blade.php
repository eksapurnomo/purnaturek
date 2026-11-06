<x-shop::layouts.account>
    <x-slot:title>
        {{ $conversation->subject }} — Message Admin
    </x-slot>

    <x-shop::layouts.account.navigation />

    <div class="pk-main">
        <div class="flex flex-col gap-6">

            <!-- Breadcrumb / Back -->
            <div>
                <a
                    href="{{ route('shop.customers.account.conversations.index') }}"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-700 hover:text-emerald-900"
                >
                    &larr; Back to Conversations
                </a>
            </div>

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

            @if (isset($errors) && $errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    <p class="font-semibold mb-1">Please correct the following errors:</p>
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Conversation Header Card -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 max-sm:p-4">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-200 pb-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-700">
                                {{ $conversation->category_label }}
                            </span>
                            <span class="text-xs text-zinc-400">
                                #{{ $conversation->id }}
                            </span>
                        </div>
                        <h1 class="text-xl font-bold text-zinc-900 mt-1">
                            {{ $conversation->subject }}
                        </h1>
                        <p class="text-xs text-zinc-500 mt-1">
                            Opened on {{ $conversation->created_at->format('M d, Y H:i') }}
                        </p>
                    </div>

                    <div>
                        @if ($conversation->status === 'waiting_admin')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                <span class="h-2 w-2 rounded-full bg-amber-600"></span>
                                Waiting on Admin
                            </span>
                        @elseif ($conversation->status === 'waiting_member')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                                <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                                Waiting on You
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-600">
                                <span class="h-2 w-2 rounded-full bg-zinc-400"></span>
                                Closed
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Message Stream -->
                <div class="mt-6 space-y-4">
                    @foreach ($conversation->messages as $msg)
                        <div class="rounded-xl p-5 border {{ $msg->isFromAdmin() ? 'bg-emerald-50/60 border-emerald-200' : 'bg-zinc-50 border-zinc-200' }}">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm {{ $msg->isFromAdmin() ? 'text-emerald-900' : 'text-zinc-900' }}">
                                        {{ $msg->isFromAdmin() ? 'PurNatureK Administration' : 'You' }}
                                    </span>
                                    <span class="text-[10px] px-2 py-0.5 rounded font-semibold uppercase {{ $msg->isFromAdmin() ? 'bg-emerald-200 text-emerald-900' : 'bg-zinc-200 text-zinc-700' }}">
                                        {{ $msg->isFromAdmin() ? 'Admin' : 'Member' }}
                                    </span>
                                </div>

                                <span class="text-xs text-zinc-400">
                                    {{ $msg->created_at->format('M d, Y H:i') }}
                                </span>
                            </div>

                            <div class="text-sm text-zinc-800 leading-relaxed whitespace-pre-wrap">{!! nl2br(e($msg->message)) !!}</div>
                        </div>
                    @endforeach
                </div>

                <!-- Reply Section -->
                <div class="mt-8 pt-6 border-t border-zinc-200">
                    @if ($conversation->canMemberReply())
                        <h2 class="text-base font-bold text-zinc-900 mb-3">Post a Reply</h2>

                        <form
                            action="{{ route('shop.customers.account.conversations.reply', $conversation->id) }}"
                            method="POST"
                            class="space-y-4"
                        >
                            @csrf

                            <div>
                                <textarea
                                    name="message"
                                    rows="4"
                                    placeholder="Type your reply to PurNatureK administration..."
                                    class="w-full rounded-lg border border-zinc-300 px-4 py-2.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                                    required
                                >{{ old('message') }}</textarea>
                            </div>

                            <div>
                                <button
                                    type="submit"
                                    class="rounded-lg bg-emerald-700 px-6 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800"
                                >
                                    Send Reply
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="rounded-lg bg-zinc-100 p-4 text-center text-sm text-zinc-600">
                            This conversation has been closed by administration. If you have additional questions or require further assistance, please
                            <a href="{{ route('shop.customers.account.conversations.create') }}" class="font-semibold text-emerald-700 hover:underline">start a new conversation</a>.
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-shop::layouts.account>


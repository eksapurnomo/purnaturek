<x-admin::layouts>
    <x-slot:title>
        Conversation #{{ $conversation->id }} — {{ $conversation->subject }}
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                    Conversation #{{ $conversation->id }}
                </h1>

                @if ($conversation->status === 'waiting_admin')
                    <span class="label-pending text-xs">Waiting on Admin</span>
                @elseif ($conversation->status === 'waiting_member')
                    <span class="label-active text-xs">Waiting on Member</span>
                @else
                    <span class="label-canceled text-xs">Closed</span>
                @endif
            </div>

            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-1">
                {{ $conversation->subject }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a
                href="{{ route('admin.b2b.conversations.index') }}"
                class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
            >
                Back to Inbox
            </a>
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

    @if (isset($errors) && $errors->any())
        <div class="mt-4 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 dark:bg-rose-950 dark:border-rose-800 dark:text-rose-300">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Message History (2 cols) -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-4 border-b pb-3 dark:border-gray-800">
                    Message History
                </h2>

                <div class="space-y-4">
                    @forelse ($conversation->messages as $msg)
                        <div class="rounded-lg p-4 border {{ $msg->isFromAdmin() ? 'bg-emerald-50/50 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800' : 'bg-gray-50 border-gray-200 dark:bg-gray-800 dark:border-gray-700' }}">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-sm {{ $msg->isFromAdmin() ? 'text-emerald-800 dark:text-emerald-300' : 'text-gray-900 dark:text-white' }}">
                                        {{ $msg->sender_name }}
                                    </span>
                                    <span class="text-xs px-2 py-0.5 rounded {{ $msg->isFromAdmin() ? 'bg-emerald-200 text-emerald-900 dark:bg-emerald-800 dark:text-emerald-100' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                        {{ $msg->isFromAdmin() ? 'PurNatureK Admin' : 'Wholesale Member' }}
                                    </span>
                                </div>
                                <span class="text-xs text-gray-500">
                                    {{ $msg->created_at->format('M d, Y H:i') }}
                                </span>
                            </div>

                            <div class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-wrap">{!! nl2br(e($msg->message)) !!}</div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No messages found in this conversation.</p>
                    @endforelse
                </div>
            </div>

            <!-- Reply Form Card -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-4">
                    Post Admin Reply
                </h2>

                <form
                    action="{{ route('admin.b2b.conversations.reply', $conversation->id) }}"
                    method="POST"
                >
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Message <span class="text-rose-600">*</span>
                            </label>
                            <textarea
                                id="message"
                                name="message"
                                rows="5"
                                placeholder="Type your response to the wholesale member..."
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                required
                            >{{ old('message') }}</textarea>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Set Status After Reply
                            </label>
                            <select
                                id="status"
                                name="status"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="waiting_member" {{ $conversation->status !== 'closed' ? 'selected' : '' }}>Waiting on Member</option>
                                <option value="waiting_admin">Keep as Waiting on Admin</option>
                                <option value="closed">Close Conversation</option>
                            </select>
                        </div>

                        <div>
                            <button
                                type="submit"
                                class="primary-button"
                            >
                                Send Reply
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar Details & Actions (1 col) -->
        <div class="flex flex-col gap-6">
            <!-- Member Overview Card -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-4 border-b pb-2 dark:border-gray-800">
                    Member Information
                </h2>

                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs uppercase text-gray-500 font-semibold">Name</dt>
                        <dd class="text-gray-800 dark:text-gray-200 font-medium">
                            {{ $conversation->customer?->first_name }} {{ $conversation->customer?->last_name }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs uppercase text-gray-500 font-semibold">Email</dt>
                        <dd class="text-gray-800 dark:text-gray-200">
                            {{ $conversation->customer?->email }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs uppercase text-gray-500 font-semibold">Customer ID</dt>
                        <dd class="text-gray-800 dark:text-gray-200">
                            #{{ $conversation->customer_id }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs uppercase text-gray-500 font-semibold">Inquiry Category</dt>
                        <dd class="text-gray-800 dark:text-gray-200 font-medium">
                            {{ $conversation->category_label }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs uppercase text-gray-500 font-semibold">Created Date</dt>
                        <dd class="text-gray-800 dark:text-gray-200">
                            {{ $conversation->created_at->format('M d, Y H:i') }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Status Controls Card -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-4 border-b pb-2 dark:border-gray-800">
                    Conversation Status
                </h2>

                <form
                    action="{{ route('admin.b2b.conversations.status', $conversation->id) }}"
                    method="POST"
                    class="space-y-4"
                >
                    @csrf

                    <div>
                        <select
                            name="status"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            @foreach ($statuses as $statusKey => $statusLabel)
                                <option value="{{ $statusKey }}" {{ $conversation->status === $statusKey ? 'selected' : '' }}>
                                    {{ $statusLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button
                        type="submit"
                        class="secondary-button w-full justify-center"
                    >
                        Update Status
                    </button>
                </form>

                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 flex flex-col gap-2">
                    @if ($conversation->isOpen())
                        <form
                            action="{{ route('admin.b2b.conversations.status', $conversation->id) }}"
                            method="POST"
                        >
                            @csrf
                            <input type="hidden" name="status" value="closed">
                            <button
                                type="submit"
                                class="transparent-button w-full justify-center text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950"
                            >
                                Close Conversation
                            </button>
                        </form>
                    @else
                        <form
                            action="{{ route('admin.b2b.conversations.status', $conversation->id) }}"
                            method="POST"
                        >
                            @csrf
                            <input type="hidden" name="status" value="waiting_member">
                            <button
                                type="submit"
                                class="transparent-button w-full justify-center text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950"
                            >
                                Reopen Conversation
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin::layouts>


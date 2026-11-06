<x-admin::layouts>
    <x-slot:title>
        Contract {{ $contract->reference_number }} — {{ $contract->title }}
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-bold font-mono text-gray-800 dark:text-white">
                    {{ $contract->reference_number }}
                </h1>

                @php
                    $badgeClass = match ($contract->status) {
                        'accepted'   => 'label-active',
                        'sent'       => 'label-pending',
                        'rejected'   => 'label-canceled',
                        'expired'    => 'label-closed',
                        'cancelled'  => 'label-closed',
                        'superseded' => 'label-info',
                        default      => 'label-pending',
                    };
                @endphp
                <span class="{{ $badgeClass }} text-xs font-semibold">
                    {{ \App\Models\MemberContract::STATUSES[$contract->status] ?? ucfirst($contract->status) }}
                </span>
            </div>

            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-1">
                {{ $contract->title }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a
                href="{{ route('admin.b2b.member-contracts.download', $contract->id) }}"
                class="secondary-button"
            >
                Download Document
            </a>

            @if($contract->status === 'draft')
                <form
                    method="POST"
                    action="{{ route('admin.b2b.member-contracts.send', $contract->id) }}"
                    onsubmit="return confirm('Issue and send this contract to the wholesale member? An email notification will be dispatched.');"
                >
                    @csrf
                    <button type="submit" class="primary-button">
                        Send to Member
                    </button>
                </form>
            @endif

            @if(in_array($contract->status, ['draft', 'sent']))
                <form
                    method="POST"
                    action="{{ route('admin.b2b.member-contracts.cancel', $contract->id) }}"
                    onsubmit="return confirm('Are you sure you want to cancel this contract?');"
                >
                    @csrf
                    <button type="submit" class="transparent-button text-red-600 hover:bg-red-50 dark:hover:bg-red-950">
                        Cancel Contract
                    </button>
                </form>
            @endif

            <a
                href="{{ route('admin.b2b.member-contracts.index') }}"
                class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
            >
                Back to Agreements
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

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Contract Overview -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white border-b border-gray-200 pb-3 dark:border-gray-800">
                    Contract Details
                </h2>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Agreement Title:</span>
                        <p class="font-medium text-gray-800 dark:text-white">{{ $contract->title }}</p>
                    </div>

                    <div>
                        <span class="text-gray-500">Template Reference:</span>
                        <p class="font-medium text-gray-800 dark:text-white">
                            @if($contract->template)
                                {{ $contract->template->title }} (v{{ $contract->template_version ?? '1.0' }})
                            @else
                                <span class="text-gray-400">Custom Agreement (No Template)</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <span class="text-gray-500">Issued Date:</span>
                        <p class="font-medium text-gray-800 dark:text-white">
                            {{ $contract->issued_date ? $contract->issued_date->format('M d, Y') : '—' }}
                        </p>
                    </div>

                    <div>
                        <span class="text-gray-500">Effective Date:</span>
                        <p class="font-medium text-gray-800 dark:text-white">
                            {{ $contract->effective_date ? $contract->effective_date->format('M d, Y') : '—' }}
                        </p>
                    </div>

                    <div>
                        <span class="text-gray-500">Expiration:</span>
                        <p class="font-medium text-gray-800 dark:text-white">
                            @if($contract->expires_at)
                                <span class="{{ $contract->expires_at->isPast() ? 'text-red-500 font-bold' : '' }}">
                                    {{ $contract->expires_at->format('M d, Y H:i T') }}
                                </span>
                            @else
                                <span class="text-gray-400">No Hard Expiry</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <span class="text-gray-500">Sent to Member:</span>
                        <p class="font-medium text-gray-800 dark:text-white">
                            {{ $contract->sent_at ? $contract->sent_at->format('M d, Y H:i T') : 'Not yet sent' }}
                        </p>
                    </div>

                    <div>
                        <span class="text-gray-500">Member Response Date:</span>
                        <p class="font-medium text-gray-800 dark:text-white">
                            {{ $contract->responded_at ? $contract->responded_at->format('M d, Y H:i:s T') : 'Pending response' }}
                        </p>
                    </div>

                    <div>
                        <span class="text-gray-500">Created By Admin:</span>
                        <p class="font-medium text-gray-800 dark:text-white">
                            {{ $contract->creator?->name ?? 'Admin #' . $contract->created_by }}
                        </p>
                    </div>
                </div>

                @if(!empty($contract->member_notes))
                    <div class="mt-6 rounded-md bg-gray-50 p-4 border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                        <span class="text-xs font-semibold uppercase text-gray-500">Member Notes / Response Comments:</span>
                        <p class="text-sm text-gray-800 dark:text-gray-200 mt-1 whitespace-pre-line">{{ $contract->member_notes }}</p>
                    </div>
                @endif

                @if(!empty($contract->admin_notes))
                    <div class="mt-4 rounded-md bg-amber-50 p-4 border border-amber-200 dark:bg-amber-950 dark:border-amber-800">
                        <span class="text-xs font-semibold uppercase text-amber-800 dark:text-amber-300">Internal Admin Notes (Confidential):</span>
                        <p class="text-sm text-amber-900 dark:text-amber-200 mt-1 whitespace-pre-line">{{ $contract->admin_notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Immutable Audit Trail -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white border-b border-gray-200 pb-3 dark:border-gray-800">
                    Immutable Audit Trail & Event History
                </h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Event</th>
                                <th class="px-4 py-3">Actor</th>
                                <th class="px-4 py-3">Timestamp</th>
                                <th class="px-4 py-3">IP Address</th>
                                <th class="px-4 py-3">Notes & Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse($contract->events as $event)
                                <tr>
                                    <td class="px-4 py-3 font-medium whitespace-nowrap">
                                        @php
                                            $eventBadge = match ($event->event) {
                                                'accepted'   => 'label-active',
                                                'sent'       => 'label-pending',
                                                'rejected'   => 'label-canceled',
                                                'viewed'     => 'label-info',
                                                'expired'    => 'label-closed',
                                                'cancelled'  => 'label-closed',
                                                'superseded' => 'label-info',
                                                default      => 'label-pending',
                                            };
                                        @endphp
                                        <span class="{{ $eventBadge }} text-xs">
                                            {{ \App\Models\MemberContractEvent::EVENT_LABELS[$event->event] ?? ucfirst($event->event) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs whitespace-nowrap">
                                        <span class="font-semibold">{{ ucfirst($event->actor_type) }}</span><br>
                                        <span class="text-gray-400">{{ $event->actor_name ?: $event->actor_email ?: 'System' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-xs whitespace-nowrap">
                                        {{ $event->created_at ? $event->created_at->format('M d, Y H:i:s T') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs font-mono whitespace-nowrap">
                                        {{ $event->ip_address ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        <p>{{ $event->notes }}</p>
                                        @if($event->file_hash)
                                            <p class="font-mono text-[10px] text-gray-400 mt-1">Hash: {{ substr($event->file_hash, 0, 16) }}...</p>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-400">
                                        No event history recorded.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Information -->
        <div class="space-y-6">
            <!-- Member Card -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white border-b border-gray-200 pb-3 dark:border-gray-800">
                    Wholesale Member
                </h2>

                <div class="mt-4 space-y-3 text-sm">
                    <div>
                        <span class="text-xs text-gray-500">Member Name:</span>
                        <p class="font-medium text-gray-800 dark:text-white">
                            {{ $contract->customer?->first_name }} {{ $contract->customer?->last_name }}
                        </p>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500">Email Address:</span>
                        <p class="font-medium text-gray-800 dark:text-white">
                            {{ $contract->customer?->email }}
                        </p>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500">Customer ID:</span>
                        <p class="font-mono text-gray-800 dark:text-white">
                            #{{ $contract->customer_id }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Document Integrity Card -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white border-b border-gray-200 pb-3 dark:border-gray-800">
                    Document File Integrity
                </h2>

                <div class="mt-4 space-y-3 text-sm">
                    <div>
                        <span class="text-xs text-gray-500">Original Filename:</span>
                        <p class="font-mono text-xs text-gray-800 dark:text-white break-all">
                            {{ $contract->original_filename }}
                        </p>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500">File Size & Type:</span>
                        <p class="text-xs text-gray-700 dark:text-gray-300">
                            {{ $contract->formattedFileSize() }} &bull; {{ $contract->mime_type }}
                        </p>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500">Cryptographic Digest (SHA-256):</span>
                        <p class="font-mono text-[11px] text-emerald-700 dark:text-emerald-400 bg-gray-50 dark:bg-gray-800 p-2 rounded break-all mt-1">
                            {{ $contract->file_hash }}
                        </p>
                    </div>

                    <div class="pt-2">
                        <a
                            href="{{ route('admin.b2b.member-contracts.download', $contract->id) }}"
                            class="w-full text-center block primary-button text-xs"
                        >
                            Download Stored Document
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin::layouts>


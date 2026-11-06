{{--
    PurNatureK Members Portal — Contract Detail & Electronic Acknowledgment
    resources/views/vendor/shop/customers/account/contracts/view.blade.php
--}}
<x-shop::layouts.account>
    <x-slot:title>
        {{ $contract->reference_number }} — {{ $contract->title }}
    </x-slot>

    <x-shop::layouts.account.navigation />

    <div class="pk-main">
        <div class="flex flex-col gap-6">

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('info'))
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
                    {{ session('info') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    {{ session('warning') }}
                </div>
            @endif

            @if (isset($errors) && $errors->any())
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Main Agreement Card -->
            <div class="pk-card p-6 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[#d9e6e1] pb-5">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-xl font-bold text-emerald-800">{{ $contract->reference_number }}</span>

                            @if($contract->status === 'accepted')
                                <span class="pk-badge pk-badge--verified text-xs px-2.5 py-0.5">
                                    Accepted
                                </span>
                            @elseif($contract->status === 'sent')
                                <span class="pk-badge pk-badge--pending text-xs px-2.5 py-0.5 animate-pulse">
                                    Pending Your Review
                                </span>
                            @elseif($contract->status === 'rejected')
                                <span class="pk-badge pk-badge--action text-xs px-2.5 py-0.5">
                                    Declined
                                </span>
                            @elseif($contract->status === 'expired')
                                <span class="pk-badge bg-zinc-100 text-zinc-600 border border-zinc-200 text-xs px-2.5 py-0.5">
                                    Expired
                                </span>
                            @else
                                <span class="pk-badge bg-zinc-100 text-zinc-700 border border-zinc-200 text-xs px-2.5 py-0.5">
                                    {{ ucfirst($contract->status) }}
                                </span>
                            @endif
                        </div>

                        <h1 class="text-xl font-extrabold text-[#0f4a3e] mt-2">{{ $contract->title }}</h1>
                    </div>

                    <div class="flex items-center gap-3">
                        <a
                            href="{{ route('shop.customers.account.contracts.download', $contract->id) }}"
                            class="pk-btn pk-btn--primary py-2 px-4 text-xs font-bold flex items-center gap-2"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download Agreement Document
                        </a>

                        <a
                            href="{{ route('shop.customers.account.contracts.index') }}"
                            class="pk-btn pk-btn--ghost py-2 px-4 text-xs font-semibold"
                        >
                            Back to Contracts
                        </a>
                    </div>
                </div>

                <!-- Contract Metadata Grid -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-[#d9e6e1] pb-6 text-sm">
                    <div>
                        <span class="text-xs font-semibold text-zinc-500 uppercase">Template Reference:</span>
                        <p class="font-medium text-[#1e2d29] mt-1">
                            @if($contract->template)
                                {{ $contract->template->title }} (v{{ $contract->template_version ?? '1.0' }})
                            @else
                                Individual Commercial Agreement
                            @endif
                        </p>
                    </div>

                    <div>
                        <span class="text-xs font-semibold text-zinc-500 uppercase">Issued Date:</span>
                        <p class="font-medium text-[#1e2d29] mt-1">
                            {{ $contract->issued_date ? $contract->issued_date->format('M d, Y') : '—' }}
                        </p>
                    </div>

                    <div>
                        <span class="text-xs font-semibold text-zinc-500 uppercase">Response Deadline:</span>
                        <p class="font-medium text-[#1e2d29] mt-1">
                            @if($contract->expires_at)
                                <span class="{{ $contract->expires_at->isPast() ? 'text-rose-600 font-bold' : '' }}">
                                    {{ $contract->expires_at->format('M d, Y H:i T') }}
                                </span>
                            @else
                                <span class="text-zinc-400">No Hard Expiry</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <span class="text-xs font-semibold text-zinc-500 uppercase">Effective Date:</span>
                        <p class="font-medium text-[#1e2d29] mt-1">
                            {{ $contract->effective_date ? $contract->effective_date->format('M d, Y') : 'Upon Acceptance' }}
                        </p>
                    </div>

                    <div>
                        <span class="text-xs font-semibold text-zinc-500 uppercase">Attached Document:</span>
                        <p class="font-mono text-xs text-emerald-800 mt-1 truncate">
                            {{ $contract->original_filename }} ({{ $contract->formattedFileSize() }})
                        </p>
                    </div>

                    <div>
                        <span class="text-xs font-semibold text-zinc-500 uppercase">SHA-256 Digest:</span>
                        <p class="font-mono text-[11px] text-zinc-600 mt-1 truncate" title="{{ $contract->file_hash }}">
                            {{ substr($contract->file_hash, 0, 18) }}...
                        </p>
                    </div>
                </div>

                <!-- Status Banner / Response Section -->
                <div class="mt-6">
                    @if($contract->status === 'accepted')
                        <div class="rounded-xl border border-emerald-300 bg-emerald-50/70 p-6">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-200 text-emerald-800 font-bold">&check;</span>
                                <div>
                                    <h3 class="font-bold text-emerald-950 text-base">Agreement Acknowledged & Accepted</h3>
                                    <p class="text-xs text-emerald-800 mt-0.5">
                                        Recorded on {{ $contract->responded_at ? $contract->responded_at->format('M d, Y H:i:s T') : 'record' }}.
                                    </p>
                                </div>
                            </div>

                            @if(!empty($contract->member_notes))
                                <div class="mt-4 rounded-lg bg-white p-4 border border-emerald-200 text-xs text-zinc-700">
                                    <span class="font-semibold text-zinc-500 uppercase">Your Submitted Notes:</span>
                                    <p class="mt-1 whitespace-pre-line">{{ $contract->member_notes }}</p>
                                </div>
                            @endif

                            <div class="mt-4 text-[11px] text-zinc-500 border-t border-emerald-200 pt-3">
                                <em>Notice:</em> This agreement acknowledgment is securely archived with document digest <code>{{ $contract->file_hash }}</code>.
                            </div>
                        </div>

                    @elseif($contract->status === 'rejected')
                        <div class="rounded-xl border border-rose-300 bg-rose-50/70 p-6">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-rose-200 text-rose-800 font-bold">&times;</span>
                                <div>
                                    <h3 class="font-bold text-rose-950 text-base">Agreement Declined</h3>
                                    <p class="text-xs text-rose-800 mt-0.5">
                                        You declined this agreement on {{ $contract->responded_at ? $contract->responded_at->format('M d, Y H:i:s T') : 'record' }}.
                                    </p>
                                </div>
                            </div>

                            @if(!empty($contract->member_notes))
                                <div class="mt-4 rounded-lg bg-white p-4 border border-rose-200 text-xs text-zinc-700">
                                    <span class="font-semibold text-zinc-500 uppercase">Reason / Feedback:</span>
                                    <p class="mt-1 whitespace-pre-line">{{ $contract->member_notes }}</p>
                                </div>
                            @endif
                        </div>

                    @elseif($contract->status === 'expired')
                        <div class="rounded-xl border border-zinc-300 bg-zinc-50 p-6 text-zinc-700">
                            <h3 class="font-bold text-zinc-900 text-base">Agreement Expired</h3>
                            <p class="text-xs text-zinc-600 mt-1 leading-relaxed">
                                This agreement expired on {{ $contract->expires_at ? $contract->expires_at->format('M d, Y H:i T') : 'a prior date' }} and can no longer be electronically acknowledged. Please contact PurNatureK administration if you require a refreshed contract.
                            </p>
                        </div>

                    @elseif($contract->canBeActionedByMember())
                        <!-- Member Action Form -->
                        <div class="rounded-xl border border-[#d9e6e1] bg-[#f0f7f4]/40 p-6">
                            <h3 class="font-bold text-[#0f4a3e] text-base">Review &amp; Electronic Acknowledgment</h3>
                            <p class="text-xs text-[#5a6f68] mt-1">
                                Please download and carefully inspect the contract document before providing your response.
                            </p>

                            <form
                                method="POST"
                                action="{{ route('shop.customers.account.contracts.action', $contract->id) }}"
                                class="mt-6 space-y-4"
                            >
                                @csrf

                                <div>
                                    <label class="block text-xs font-semibold text-zinc-700 uppercase">
                                        Member Notes / Comments (Optional)
                                    </label>
                                    <textarea
                                        name="notes"
                                        rows="3"
                                        class="mt-1 block w-full rounded-xl border border-[#d9e6e1] p-3 text-sm focus:border-emerald-600 focus:outline-none bg-white shadow-sm"
                                        placeholder="Add any purchase order references, corporate signatory designations, or feedback..."
                                    >{{ old('notes') }}</textarea>
                                </div>

                                <div class="rounded-xl border border-[#d9e6e1] bg-white p-4 shadow-sm">
                                    <div class="flex items-start gap-3">
                                        <input
                                            type="checkbox"
                                            id="terms_acknowledge"
                                            required
                                            class="mt-1 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500"
                                        />
                                        <label for="terms_acknowledge" class="text-xs text-[#1e2d29] leading-relaxed">
                                            <strong>Authorized Acknowledgment:</strong> I confirm that I am an authorized commercial representative of my organization and that I have read, understood, and accept the contractual terms set forth in document <strong>{{ $contract->original_filename }}</strong>. I understand that this electronic action will be registered as an internal application acknowledgment with PurNatureK.
                                        </label>
                                    </div>

                                    <p class="text-[11px] text-zinc-400 mt-3 pt-3 border-t border-zinc-100">
                                        <em>Legal Notice:</em> Agreement acceptance through this portal records an electronic application-level acknowledgment tied to your account login, IP address, timestamp, and document cryptographic hash (SHA-256).
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center gap-4 pt-2">
                                    <button
                                        type="submit"
                                        name="action"
                                        value="accept"
                                        class="pk-btn pk-btn--primary py-2.5 px-6 text-sm font-bold shadow-sm"
                                        onclick="return confirm('Confirm acceptance of this commercial agreement?');"
                                    >
                                        Accept &amp; Acknowledge Agreement
                                    </button>

                                    <button
                                        type="submit"
                                        name="action"
                                        value="reject"
                                        formnovalidate
                                        class="pk-btn border-rose-300 text-rose-700 hover:bg-rose-50 py-2.5 px-4 text-sm font-semibold"
                                        onclick="return confirm('Are you sure you wish to decline this agreement?');"
                                    >
                                        Decline Agreement
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6 text-zinc-600 text-sm">
                            Status: <strong>{{ ucfirst($contract->status) }}</strong>. This contract is not currently pending your acknowledgment.
                        </div>
                    @endif
                </div>

                <!-- Event Timeline Section -->
                <div class="mt-8 border-t border-[#d9e6e1] pt-6">
                    <h3 class="font-bold text-[#0f4a3e] text-sm mb-4">Agreement Activity Log</h3>

                    <div class="space-y-3">
                        @forelse($contract->events as $event)
                            <div class="flex items-start justify-between text-xs border-b border-zinc-100 pb-2">
                                <div>
                                    <span class="font-semibold text-zinc-800">
                                        {{ \App\Models\MemberContractEvent::EVENT_LABELS[$event->event] ?? ucfirst($event->event) }}
                                    </span>
                                    <span class="text-zinc-500">by {{ ucfirst($event->actor_type) }}</span>
                                    @if($event->notes)
                                        <p class="text-zinc-500 mt-0.5">{{ $event->notes }}</p>
                                    @endif
                                </div>
                                <span class="text-zinc-400 font-mono whitespace-nowrap">
                                    {{ $event->created_at ? $event->created_at->format('M d, Y H:i T') : '—' }}
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-zinc-400">No activity logged.</p>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-shop::layouts.account>

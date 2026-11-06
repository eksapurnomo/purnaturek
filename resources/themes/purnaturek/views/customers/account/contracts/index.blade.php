{{--
    PurNatureK Members Portal — Contracts & Agreements Index
    resources/themes/purnaturek/views/customers/account/contracts/index.blade.php
--}}
<x-shop::layouts.account>
    <x-slot:title>
        Contracts & Commercial Agreements — PurNatureK Members Portal
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

            @if (session('warning'))
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    {{ session('warning') }}
                </div>
            @endif

            @if (session('info'))
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
                    {{ session('info') }}
                </div>
            @endif

            <!-- Pending Action Callout -->
            @if ($pendingActionCount > 0)
                <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-200 text-amber-800 font-bold">!</span>
                        <div>
                            <p class="font-bold">Action Required: {{ $pendingActionCount }} Agreement(s) Pending Review</p>
                            <p class="text-xs text-amber-700">Please inspect the commercial terms and record your acknowledgment to enable wholesale fulfillment.</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Main Listing Card -->
            <div class="pk-card p-6 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[#d9e6e1] pb-5">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">
                                Commercial Governance
                            </span>
                        </div>
                        <h1 class="text-2xl font-extrabold text-[#0f4a3e]">Contracts & Commercial Agreements</h1>
                        <p class="text-xs text-[#5a6f68] mt-1">
                            Review executed supply contracts, confidential agreements, and approved reference templates.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <a
                            href="{{ route('shop.customers.account.contracts.templates') }}"
                            class="pk-btn pk-btn--ghost py-2 px-4 text-xs font-semibold"
                        >
                            Reference Templates ({{ $templates->count() }})
                        </a>
                    </div>
                </div>

                <!-- Contracts Table -->
                <div class="mt-6 overflow-x-auto">
                    <table class="w-full text-left text-sm text-[#1e2d29]">
                        <thead class="bg-[#f0f7f4] text-xs font-bold uppercase tracking-wider text-emerald-900">
                            <tr>
                                <th class="px-4 py-3 rounded-l-lg">Reference #</th>
                                <th class="px-4 py-3">Agreement Title</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Issued Date</th>
                                <th class="px-4 py-3">Deadline / Expiry</th>
                                <th class="px-4 py-3 text-right rounded-r-lg">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#d9e6e1]">
                            @forelse($contracts as $contract)
                                <tr class="hover:bg-[#f0f7f4]/40 transition">
                                    <td class="px-4 py-4 font-mono font-bold text-emerald-800">
                                        <a href="{{ route('shop.customers.account.contracts.view', $contract->id) }}" class="hover:underline">
                                            {{ $contract->reference_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-4">
                                        <p class="font-bold text-[#0f4a3e]">{{ $contract->title }}</p>
                                        @if($contract->template)
                                            <p class="text-xs text-[#5a6f68]">Template: {{ $contract->template->title }} (v{{ $contract->template_version ?? '1.0' }})</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
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
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-xs text-[#5a6f68]">
                                        {{ $contract->sent_at ? $contract->sent_at->format('M d, Y') : ($contract->created_at ? $contract->created_at->format('M d, Y') : '—') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-xs">
                                        @if($contract->expires_at)
                                            <span class="{{ $contract->expires_at->isPast() ? 'text-rose-600 font-bold' : 'text-[#1e2d29]' }}">
                                                {{ $contract->expires_at->format('M d, Y') }}
                                            </span>
                                        @else
                                            <span class="text-zinc-400">No Hard Expiry</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a
                                                href="{{ route('shop.customers.account.contracts.download', $contract->id) }}"
                                                class="pk-btn pk-btn--ghost p-2 text-xs"
                                                title="Download Document"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            </a>

                                            @if($contract->canBeActionedByMember())
                                                <a
                                                    href="{{ route('shop.customers.account.contracts.view', $contract->id) }}"
                                                    class="pk-btn pk-btn--primary py-1.5 px-3 text-xs font-bold"
                                                >
                                                    Review & Acknowledge
                                                </a>
                                            @else
                                                <a
                                                    href="{{ route('shop.customers.account.contracts.view', $contract->id) }}"
                                                    class="pk-btn pk-btn--ghost py-1.5 px-3 text-xs font-semibold"
                                                >
                                                    View Details
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center text-zinc-400">
                                        <div class="h-12 w-12 rounded-full bg-[#f0f7f4] text-[#0f4a3e] flex items-center justify-center mx-auto mb-3">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <p class="font-bold text-base text-[#0f4a3e]">No Issued Agreements On Record</p>
                                        <p class="text-xs text-[#5a6f68] mt-1 max-w-md mx-auto">
                                            When PurNatureK administration issues a formal supply agreement or commercial document for your account, it will appear here for review and execution.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($contracts->hasPages())
                    <div class="mt-6 border-t border-[#d9e6e1] pt-4">
                        {{ $contracts->links() }}
                    </div>
                @endif
            </div>

            <!-- Reference Templates Summary Box -->
            @if($templates->isNotEmpty())
                <div class="pk-card p-6 bg-[#f0f7f4]/40 border border-[#d9e6e1]">
                    <div class="flex items-center justify-between pb-3 border-b border-[#d9e6e1]">
                        <h2 class="font-bold text-[#0f4a3e] text-sm">Reference Agreement Templates Library</h2>
                        <a href="{{ route('shop.customers.account.contracts.templates') }}" class="text-xs text-emerald-800 font-bold hover:underline">
                            Browse All Templates &rarr;
                        </a>
                    </div>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($templates->take(3) as $tpl)
                            <div class="rounded-xl border border-[#d9e6e1] bg-white p-4 flex flex-col justify-between shadow-sm">
                                <div>
                                    <p class="font-bold text-xs text-[#0f4a3e]">{{ $tpl->title }}</p>
                                    <p class="text-[11px] text-[#5a6f68] mt-1 line-clamp-2">{{ $tpl->description ?: 'Standard wholesale commercial agreement template.' }}</p>
                                </div>
                                <div class="mt-3 pt-3 border-t border-[#d9e6e1]/60 flex items-center justify-between">
                                    <span class="text-[11px] font-mono text-zinc-400">v{{ $tpl->version ?: '1.0' }}</span>
                                    @if($tpl->hasFile())
                                        <a href="{{ route('shop.customers.account.contracts.templates.download', $tpl->id) }}" class="text-xs font-bold text-emerald-800 hover:underline">
                                            Download
                                        </a>
                                    @else
                                        <span class="text-[11px] text-zinc-400 font-medium">Pending Execution</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-shop::layouts.account>


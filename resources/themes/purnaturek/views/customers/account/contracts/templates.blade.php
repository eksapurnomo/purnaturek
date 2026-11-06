{{--
    PurNatureK Members Portal — Reference Contract Templates
    resources/themes/purnaturek/views/customers/account/contracts/templates.blade.php
--}}
<x-shop::layouts.account>
    <x-slot:title>
        Contract Templates Library — PurNatureK Members Portal
    </x-slot>

    <x-shop::layouts.account.navigation />

    <div class="pk-main">
        <div class="flex flex-col gap-6">

            <!-- Main Listing Card -->
            <div class="pk-card p-6 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[#d9e6e1] pb-5">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">
                                Legal & Commercial Reference
                            </span>
                        </div>
                        <h1 class="text-2xl font-extrabold text-[#0f4a3e]">Reference Contract Templates</h1>
                        <p class="text-xs text-[#5a6f68] mt-1">
                            Download reference copies of PurNatureK standard commercial agreements, non-disclosure agreements, and distributor guidelines.
                        </p>
                    </div>

                    <a
                        href="{{ route('shop.customers.account.contracts.index') }}"
                        class="pk-btn pk-btn--ghost py-2 px-4 text-xs font-semibold"
                    >
                        My Issued Agreements &rarr;
                    </a>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($templates as $tpl)
                        <div class="rounded-xl border border-[#d9e6e1] bg-white p-5 shadow-sm hover:border-emerald-500 transition flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="rounded bg-zinc-100 px-2 py-0.5 font-mono text-[11px] text-zinc-700 border border-zinc-200">
                                        v{{ $tpl->version ?: '1.0' }}
                                    </span>

                                    <span class="text-[11px] font-bold text-emerald-800">
                                        {{ \App\Models\ContractTemplate::CATEGORIES[$tpl->category] ?? ucfirst($tpl->category) }}
                                    </span>
                                </div>

                                <h2 class="text-base font-bold text-[#0f4a3e] mt-2.5">{{ $tpl->title }}</h2>

                                <p class="text-xs text-[#5a6f68] mt-2 leading-relaxed">
                                    {{ $tpl->description ?: 'Standard template agreement provided for member reference.' }}
                                </p>
                            </div>

                            <div class="mt-6 border-t border-[#d9e6e1]/70 pt-4 flex items-center justify-between">
                                <span class="text-[11px] text-zinc-400">
                                    {{ $tpl->effective_date ? 'Effective: ' . $tpl->effective_date->format('M Y') : 'Reference Spec' }}
                                </span>

                                @if($tpl->hasFile())
                                    <a
                                        href="{{ route('shop.customers.account.contracts.templates.download', $tpl->id) }}"
                                        class="pk-btn pk-btn--primary py-1.5 px-3 text-xs font-bold flex items-center gap-1.5"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Download ({{ $tpl->formattedFileSize() }})
                                    </a>
                                @else
                                    <span class="text-xs text-zinc-400 font-medium italic">Approved File Pending</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center text-zinc-400">
                            <div class="h-12 w-12 rounded-full bg-[#f0f7f4] text-[#0f4a3e] flex items-center justify-center mx-auto mb-3">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <p class="font-bold text-base text-[#0f4a3e]">No Reference Templates Published</p>
                            <p class="text-xs text-[#5a6f68] mt-1 max-w-md mx-auto">
                                Standard agreement templates will be uploaded and published by PurNatureK administration for member reference.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-shop::layouts.account>


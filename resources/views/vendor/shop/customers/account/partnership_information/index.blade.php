{{--
    PurNatureK Members Portal — Wholesale Partnership Information
    resources/views/vendor/shop/customers/account/partnership_information/index.blade.php
--}}
<x-shop::layouts.account>
    <x-slot:title>
        {{ $page->title }} — PurNatureK Members Portal
    </x-slot>

    <x-shop::layouts.account.navigation />

    <div class="pk-main">
        <!-- Main Content Card -->
        <div class="pk-card p-6 sm:p-8">
            <!-- Header Section -->
            <div class="border-b border-[#d9e6e1] pb-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">
                                Commercial Cooperation Briefing
                            </span>
                        </div>
                        <h1 class="text-2xl font-extrabold text-[#0f4a3e]">
                            {{ $page->title }}
                        </h1>
                        @if ($page->description)
                            <p class="text-sm text-[#5a6f68] mt-2 max-w-3xl leading-relaxed">
                                {{ $page->description }}
                            </p>
                        @endif
                    </div>

                    <span class="pk-badge pk-badge--verified text-xs px-3 py-1.5">
                        Member Access Only
                    </span>
                </div>

                <!-- Quick Section Jump Anchors (if published with content) -->
                @if ($isPublished && $sections->isNotEmpty())
                    <div class="mt-6 flex flex-wrap gap-2 pt-2 border-t border-[#f0f7f4]">
                        @foreach ($sections as $sec)
                            <a
                                href="#{{ $sec->section_key }}"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-[#f0f7f4] hover:bg-emerald-100/70 hover:text-[#0f4a3e] px-3 py-1.5 text-xs font-semibold text-[#1e2d29] border border-[#d9e6e1] transition shadow-sm"
                            >
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-700"></span>
                                {{ $sec->title }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Published Sections Body -->
            @if ($isPublished && $sections->isNotEmpty())
                <div class="mt-8 space-y-10">
                    @foreach ($sections as $section)
                        <article
                            id="{{ $section->section_key }}"
                            class="border-b border-[#d9e6e1]/70 pb-8 last:border-b-0 scroll-mt-24"
                        >
                            <div class="flex items-center gap-2 mb-3">
                                <span class="h-2.5 w-2.5 rounded-sm bg-[#0f4a3e]"></span>
                                <h2 class="text-lg font-bold text-[#0f4a3e]">
                                    {{ $section->title }}
                                </h2>
                            </div>

                            <div class="pl-4 border-l-2 border-emerald-500/40 text-sm text-[#1e2d29] leading-relaxed">
                                {!! \App\Support\WholesalePartnershipManager::formatSafeContent($section->content) !!}
                            </div>

                            <!-- Contextual Integrations & Actions -->
                            @if ($section->section_key === 'us_wholesale_buyer_partnership')
                                <div class="mt-4 rounded-xl bg-[#f0f7f4] border border-[#d9e6e1] p-4 ml-4 text-xs text-[#0f4a3e] flex items-center justify-between flex-wrap gap-3">
                                    <span>Ready to initiate formal wholesale buyer account onboarding?</span>
                                    <div class="flex items-center gap-2">
                                        <a
                                            href="{{ route('shop.customers.account.buyer_verification.index') }}"
                                            class="pk-btn pk-btn--primary py-1.5 px-3 text-xs"
                                        >
                                            Complete Buyer Verification &rarr;
                                        </a>
                                        <a
                                            href="{{ route('shop.customers.account.conversations.create', ['category' => 'partnership_inquiry', 'subject' => 'Wholesale Buyer Inquiry']) }}"
                                            class="pk-btn pk-btn--ghost py-1.5 px-3 text-xs"
                                        >
                                            Message Admin &rarr;
                                        </a>
                                    </div>
                                </div>
                            @elseif ($section->section_key === 'partnership_requirements')
                                <div class="mt-4 rounded-xl bg-blue-50/70 border border-blue-200/70 p-4 ml-4 text-xs text-blue-900 flex items-center justify-between flex-wrap gap-3">
                                    <span>Review active commercial agreements and standard contract templates:</span>
                                    <a
                                        href="{{ route('shop.customers.account.contracts.index') }}"
                                        class="pk-btn bg-blue-700 text-white hover:bg-blue-800 py-1.5 px-3 text-xs font-semibold"
                                    >
                                        View Contracts & Agreements &rarr;
                                    </a>
                                </div>
                            @elseif ($section->section_key === 'manufacturing_partner')
                                <div class="mt-4 rounded-xl bg-zinc-50 border border-zinc-200 p-4 ml-4 text-xs text-zinc-800 flex items-center justify-between flex-wrap gap-3">
                                    <span>Looking for manufacturing facility details, quality control standards, or supply capabilities?</span>
                                    <a
                                        href="{{ route('shop.customers.account.company_supply.index') }}"
                                        class="pk-btn pk-btn--ghost py-1.5 px-3 text-xs"
                                    >
                                        View Company & Supply Information &rarr;
                                    </a>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>

                <!-- Partnership Inquiry Call-To-Action Box -->
                <div class="mt-12 rounded-xl bg-gradient-to-r from-[#f0f7f4] to-[#fef9c3]/30 border border-[#d9e6e1] p-6 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-[#0f4a3e]">Inquire About Wholesale Partnership</h3>
                        <p class="text-xs text-[#5a6f68] mt-1 max-w-xl leading-relaxed">
                            Connect directly with our wholesale administration team to discuss distribution agreements, custom supply models, or specialized purchasing requirements.
                        </p>
                    </div>

                    <a
                        href="{{ route('shop.customers.account.conversations.create', ['category' => 'partnership_inquiry', 'subject' => 'Wholesale Partnership Inquiry']) }}"
                        class="pk-btn pk-btn--primary py-2 px-5 text-xs font-bold shrink-0"
                    >
                        Initiate Partnership Message &rarr;
                    </a>
                </div>

            @else
                <!-- Professional Awaiting Administration / Maintenance State -->
                <div class="py-16 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-amber-800 mb-4 border border-amber-200">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>

                    <h3 class="text-lg font-bold text-[#0f4a3e]">Wholesale Partnership Information Unavailable</h3>
                    <p class="text-sm text-[#5a6f68] mt-2 max-w-lg mx-auto leading-relaxed">
                        Commercial cooperation models, distributor qualification criteria, and partnership onboarding details are currently being finalized and verified by PurNatureK administration.
                    </p>

                    <div class="mt-8 flex flex-wrap justify-center gap-3">
                        <a
                            href="{{ route('shop.customers.account.conversations.create', ['category' => 'partnership_inquiry', 'subject' => 'Wholesale Partnership Inquiry']) }}"
                            class="pk-btn pk-btn--primary py-2.5 px-5 text-xs font-bold"
                        >
                            Contact Administration
                        </a>

                        <a
                            href="{{ route('shop.customers.account.index') }}"
                            class="pk-btn pk-btn--ghost py-2.5 px-5 text-xs font-semibold"
                        >
                            Return to Portal Home
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-shop::layouts.account>

@php
    $channel = core()->getCurrentChannel();
    $currentCustomer = auth()->guard('customer')->user();
@endphp

<x-shop::layouts>
    <x-slot:title>
        {{ $currentCustomer ? 'Member Workspace — PurNatureK' : 'Wholesale Members Portal — PurNatureK' }}
    </x-slot>

    @if ($currentCustomer)
        {{-- =====================================================================
             AUTHENTICATED MEMBER EXPERIENCE
             ===================================================================== --}}
        @php
            $verificationStatus = \App\Support\BuyerVerificationManager::getVerificationStatus($currentCustomer);
            $isVerified = \App\Support\BuyerVerificationManager::isVerifiedBuyer($currentCustomer);
            $unreadCount = \App\Support\MemberConversationManager::getUnreadCountForMember($currentCustomer);
        @endphp

        <section class="pk-gateway-hero">
            <div class="pk-gateway-container">
                <div class="pk-gateway-card">
                    <!-- Status Banner -->
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[#d9e6e1] pb-6 mb-8">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-600"></span>
                                <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">
                                    Active Wholesale Session
                                </span>
                            </div>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-[#0f4a3e] mt-1.5">
                                Welcome back, {{ $currentCustomer->first_name }}
                            </h1>
                            <p class="text-sm text-[#5a6f68] mt-1">
                                Authorized member account: <span class="font-medium text-[#1e2d29]">{{ $currentCustomer->email }}</span>
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            @if ($isVerified)
                                <span class="pk-badge pk-badge--verified text-xs px-3 py-1.5">
                                    &check; Verified Wholesale Buyer
                                </span>
                            @elseif ($verificationStatus === 'pending')
                                <span class="pk-badge pk-badge--pending text-xs px-3 py-1.5">
                                    &bull; Buyer Verification Pending
                                </span>
                            @elseif ($verificationStatus === 'rejected')
                                <span class="pk-badge pk-badge--action text-xs px-3 py-1.5">
                                    &excl; Action Required on Verification
                                </span>
                            @else
                                <span class="pk-badge bg-zinc-100 text-zinc-700 border border-zinc-200 text-xs px-3 py-1.5">
                                    Registered Member (Tier 1)
                                </span>
                            @endif

                            <a
                                href="{{ route('shop.customers.account.index') }}"
                                class="pk-btn pk-btn--primary"
                            >
                                Enter Member Workspace &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- Quick Workspace Access Grid -->
                    <h2 class="text-base font-bold text-[#0f4a3e] uppercase tracking-wide text-xs mb-4">
                        Member Workspace Shortcuts
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Catalog -->
                        <a
                            href="{{ route('shop.customers.account.index') }}#catalog"
                            class="group block p-4 rounded-xl border border-[#d9e6e1] bg-[#f0f7f4]/40 hover:bg-[#f0f7f4] hover:border-emerald-500 transition shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-sm text-[#0f4a3e] group-hover:text-emerald-700">Wholesale Product Catalog</span>
                                <span class="text-xs text-emerald-600 font-semibold">&rarr;</span>
                            </div>
                            <p class="text-xs text-[#5a6f68] mt-1">
                                Browse bulk botanical extract specifications and commercial availability.
                            </p>
                        </a>

                        <!-- Verification -->
                        <a
                            href="{{ route('shop.customers.account.buyer_verification.index') }}"
                            class="group block p-4 rounded-xl border border-[#d9e6e1] bg-white hover:border-emerald-500 transition shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-sm text-[#0f4a3e]">Buyer Verification</span>
                                <span class="text-xs text-emerald-600 font-semibold">&rarr;</span>
                            </div>
                            <p class="text-xs text-[#5a6f68] mt-1">
                                Submit business credentials and tax documentation for Tier 2 purchasing unlock.
                            </p>
                        </a>

                        <!-- RFQ & Quotes -->
                        <a
                            href="{{ route('shop.customers.account.quotes.index') }}"
                            class="group block p-4 rounded-xl border border-[#d9e6e1] bg-white hover:border-emerald-500 transition shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-sm text-[#0f4a3e]">RFQ & Quotations</span>
                                <span class="text-xs text-emerald-600 font-semibold">&rarr;</span>
                            </div>
                            <p class="text-xs text-[#5a6f68] mt-1">
                                Review active price quotes, volume requests, and commercial terms.
                            </p>
                        </a>

                        <!-- Company & Supply Info -->
                        <a
                            href="{{ route('shop.customers.account.company_supply.index') }}"
                            class="group block p-4 rounded-xl border border-[#d9e6e1] bg-white hover:border-emerald-500 transition shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-sm text-[#0f4a3e]">Company & Supply Info</span>
                                <span class="text-xs text-emerald-600 font-semibold">&rarr;</span>
                            </div>
                            <p class="text-xs text-[#5a6f68] mt-1">
                                Operational capacity, manufacturing facility details, and QA standards.
                            </p>
                        </a>

                        <!-- Partnership Info -->
                        <a
                            href="{{ route('shop.customers.account.partnership_information.index') }}"
                            class="group block p-4 rounded-xl border border-[#d9e6e1] bg-white hover:border-emerald-500 transition shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-sm text-[#0f4a3e]">Partnership Information</span>
                                <span class="text-xs text-emerald-600 font-semibold">&rarr;</span>
                            </div>
                            <p class="text-xs text-[#5a6f68] mt-1">
                                Distributor cooperation, manufacturing arrangements, and onboarding.
                            </p>
                        </a>

                        <!-- Message Admin -->
                        <a
                            href="{{ route('shop.customers.account.conversations.index') }}"
                            class="group block p-4 rounded-xl border border-[#d9e6e1] bg-white hover:border-emerald-500 transition shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-sm text-[#0f4a3e]">Message Admin</span>
                                @if ($unreadCount > 0)
                                    <span class="pk-badge pk-badge--counter">{{ $unreadCount }} unread</span>
                                @else
                                    <span class="text-xs text-emerald-600 font-semibold">&rarr;</span>
                                @endif
                            </div>
                            <p class="text-xs text-[#5a6f68] mt-1">
                                Direct confidential messaging with PurNatureK wholesale administration.
                            </p>
                        </a>
                    </div>
                </div>
            </div>
        </section>

    @else
        {{-- =====================================================================
             GUEST EXPERIENCE (STRICTLY PRIVATE CATALOG PRESERVED)
             ===================================================================== --}}
        <section class="pk-gateway-hero">
            <div class="pk-gateway-container">
                <div class="pk-gateway-card">
                    <!-- Brand Pill & Header -->
                    <div class="text-center max-w-2xl mx-auto">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#f0f7f4] text-[#0f4a3e] border border-[#d9e6e1] mb-4">
                            <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                            Authorized B2B Commercial Portal
                        </span>

                        <h1 class="text-3xl sm:text-4xl font-extrabold text-[#0f4a3e] tracking-tight leading-tight">
                            PurNatureK Bulk & Wholesale Platform
                        </h1>

                        <p class="mt-4 text-base text-[#5a6f68] leading-relaxed">
                            Welcome to the private commercial wholesale portal. Authorized business members can access batch-tested botanical ingredients, volume pricing models, and direct supply agreements.
                        </p>

                        <!-- Action Buttons -->
                        <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                            <a
                                href="{{ route('shop.customer.session.index') }}"
                                class="pk-btn pk-btn--primary px-8 py-3 text-base shadow-sm"
                            >
                                Member Sign In
                            </a>

                            <a
                                href="{{ route('shop.customers.register.index') }}"
                                class="pk-btn pk-btn--ghost px-7 py-3 text-base"
                            >
                                Apply for Wholesale Account
                            </a>
                        </div>
                    </div>

                    <!-- 3 Core Commercial Pillars -->
                    <div class="mt-14 pt-10 border-t border-[#d9e6e1] grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Pillar 1 -->
                        <div class="p-5 rounded-xl bg-[#f0f7f4]/40 border border-[#d9e6e1]">
                            <div class="h-9 w-9 rounded-lg bg-[#0f4a3e] text-white flex items-center justify-center font-bold text-sm mb-3">
                                01
                            </div>
                            <h3 class="font-bold text-base text-[#0f4a3e]">Commercial Wholesale Supply</h3>
                            <p class="text-xs text-[#5a6f68] mt-1.5 leading-relaxed">
                                Direct procurement of standardized botanical extracts with flexible volume tiering and pallet logistics across the US.
                            </p>
                        </div>

                        <!-- Pillar 2 -->
                        <div class="p-5 rounded-xl bg-[#f0f7f4]/40 border border-[#d9e6e1]">
                            <div class="h-9 w-9 rounded-lg bg-[#0f4a3e] text-white flex items-center justify-center font-bold text-sm mb-3">
                                02
                            </div>
                            <h3 class="font-bold text-base text-[#0f4a3e]">Quality & Lab Verification</h3>
                            <p class="text-xs text-[#5a6f68] mt-1.5 leading-relaxed">
                                Complete document custody: batch-specific Certificates of Analysis (COA), safety data sheets, and compliance filings.
                            </p>
                        </div>

                        <!-- Pillar 3 -->
                        <div class="p-5 rounded-xl bg-[#f0f7f4]/40 border border-[#d9e6e1]">
                            <div class="h-9 w-9 rounded-lg bg-[#0f4a3e] text-white flex items-center justify-center font-bold text-sm mb-3">
                                03
                            </div>
                            <h3 class="font-bold text-base text-[#0f4a3e]">Verified Buyer Security</h3>
                            <p class="text-xs text-[#5a6f68] mt-1.5 leading-relaxed">
                                Transparent onboarding for licensed retailers, manufacturers, and distributors with contract execution in portal.
                            </p>
                        </div>
                    </div>

                    <!-- Corporate Site Notice -->
                    <div class="mt-8 rounded-lg bg-[#fefce8] border border-[#fef08a] p-4 text-xs text-[#854d0e] flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="font-bold">&bull; Looking for corporate background?</span>
                            <span>General company inquiries and public media are hosted separately.</span>
                        </div>
                        <a
                            href="https://purnaturekbulk.com"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="font-bold underline hover:text-[#a16207] shrink-0"
                        >
                            Visit Corporate Site (purnaturekbulk.com) &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endif
</x-shop::layouts>


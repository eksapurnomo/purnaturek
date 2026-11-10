@php
    $currentCustomer = auth()->guard('customer')->user();
@endphp

<x-shop::layouts>
    <x-slot:title>
        {{ $currentCustomer ? 'Member Workspace — PurNatureK' : 'PurNatureK Bulk & Wholesale Members Portal' }}
    </x-slot>

    @if ($currentCustomer)
        {{-- =====================================================================
             AUTHENTICATED MEMBER GATEWAY
             ===================================================================== --}}
        @php
            $verificationStatus = \App\Support\BuyerVerificationManager::getVerificationStatus($currentCustomer);
            $isVerified = \App\Support\BuyerVerificationManager::isVerifiedBuyer($currentCustomer);
            $unreadCount = \App\Support\MemberConversationManager::getUnreadCountForMember($currentCustomer);
            $pendingContracts = \App\Models\MemberContract::query()
                ->forCustomer((int) $currentCustomer->id)
                ->where('status', \App\Models\MemberContract::STATUS_SENT)
                ->count();
        @endphp

        <section class="pk-section-band">
            <div class="pk-container pk-container--narrow pk-stack-md">
                <x-shop::portal.page-header
                    eyebrow="Active Wholesale Session"
                    :title="'Welcome back, ' . $currentCustomer->first_name"
                    :subtitle="'Signed in as ' . $currentCustomer->email . ' — Private US Commercial B2B Portal.'"
                >
                    <x-slot:actions>
                        @if ($isVerified)
                            <x-shop::portal.badge variant="verified">&check; Verified Wholesale Buyer</x-shop::portal.badge>
                        @elseif ($verificationStatus === 'pending')
                            <x-shop::portal.badge variant="pending">&bull; Verification Pending</x-shop::portal.badge>
                        @elseif ($verificationStatus === 'rejected')
                            <x-shop::portal.badge variant="action">! Action Required</x-shop::portal.badge>
                        @else
                            <x-shop::portal.badge variant="neutral">Tier 1 Registered Member</x-shop::portal.badge>
                        @endif

                        <x-shop::portal.button :href="route('shop.customers.account.index')" variant="primary">
                            Enter Member Workspace &rarr;
                        </x-shop::portal.button>
                    </x-slot>
                </x-shop::portal.page-header>

                <x-shop::portal.card
                    eyebrow="Direct Navigation"
                    title="Member Workspace Shortcuts"
                    subtitle="Jump directly to protected wholesale catalog listings, compliance documentation, contracts, and commercial communication."
                >
                    <div class="pk-grid-3">
                        <a href="{{ route('shop.customers.account.index') }}#catalog" class="pk-shortcut-card">
                            <div class="pk-shortcut-card__top">
                                <span class="pk-shortcut-card__title">Wholesale Catalog</span>
                                <span class="pk-eyebrow">Browse</span>
                            </div>
                            <p class="pk-shortcut-card__desc">
                                Explore standardized powder &amp; extract grades, indicative pricing models, and MOQ terms.
                            </p>
                        </a>

                        <a href="{{ route('shop.customers.account.buyer_verification.index') }}" class="pk-shortcut-card">
                            <div class="pk-shortcut-card__top">
                                <span class="pk-shortcut-card__title">Buyer Verification</span>
                                @if ($isVerified)
                                    <x-shop::portal.badge variant="verified" size="sm">Verified</x-shop::portal.badge>
                                @elseif ($verificationStatus === 'pending')
                                    <x-shop::portal.badge variant="pending" size="sm">Pending</x-shop::portal.badge>
                                @else
                                    <x-shop::portal.badge variant="earth" size="sm">Tier 2</x-shop::portal.badge>
                                @endif
                            </div>
                            <p class="pk-shortcut-card__desc">
                                Submit business credentials and resale certificates to unlock Tier 2 quotation and purchasing workflows.
                            </p>
                        </a>

                        <a href="{{ route('shop.customers.account.quotes.index') }}" class="pk-shortcut-card">
                            <div class="pk-shortcut-card__top">
                                <span class="pk-shortcut-card__title">RFQ &amp; Quotations</span>
                                <span class="pk-text-primary text-xs font-bold">&rarr;</span>
                            </div>
                            <p class="pk-shortcut-card__desc">
                                Review official price quotes, commercial terms, and volume procurement requests.
                            </p>
                        </a>

                        <a href="{{ route('shop.customers.account.contracts.index') }}" class="pk-shortcut-card">
                            <div class="pk-shortcut-card__top">
                                <span class="pk-shortcut-card__title">Contracts &amp; Agreements</span>
                                @if ($pendingContracts > 0)
                                    <x-shop::portal.badge variant="pending" size="sm">{{ $pendingContracts }} pending</x-shop::portal.badge>
                                @else
                                    <span class="pk-text-primary text-xs font-bold">&rarr;</span>
                                @endif
                            </div>
                            <p class="pk-shortcut-card__desc">
                                Review Master Supply Agreements, NDAs, and execute verified electronic acknowledgments.
                            </p>
                        </a>

                        <a href="{{ route('shop.customers.account.company_supply.index') }}" class="pk-shortcut-card">
                            <div class="pk-shortcut-card__top">
                                <span class="pk-shortcut-card__title">Company &amp; Supply Info</span>
                                <span class="pk-text-primary text-xs font-bold">&rarr;</span>
                            </div>
                            <p class="pk-shortcut-card__desc">
                                US wholesale operations, third-party lab testing standards, and bulk supply documentation.
                            </p>
                        </a>

                        <a href="{{ route('shop.customers.account.conversations.index') }}" class="pk-shortcut-card">
                            <div class="pk-shortcut-card__top">
                                <span class="pk-shortcut-card__title">Message Admin</span>
                                @if ($unreadCount > 0)
                                    <x-shop::portal.badge variant="counter" size="sm">{{ $unreadCount }} unread</x-shop::portal.badge>
                                @else
                                    <span class="pk-text-primary text-xs font-bold">&rarr;</span>
                                @endif
                            </div>
                            <p class="pk-shortcut-card__desc">
                                Direct confidential messaging with our Chicago wholesale commercial administration desk.
                            </p>
                        </a>
                    </div>
                </x-shop::portal.card>
            </div>
        </section>
    @else
        {{-- =====================================================================
             GUEST GATEWAY (PRIVATE CATALOG STRICTLY PROTECTED)
             ===================================================================== --}}
        <section class="pk-hero-dark">
            <div class="pk-hero-dark__inner">
                <div>
                    <span class="pk-eyebrow pk-eyebrow--light">Purnature Khatulistiwa LLC &bull; US Wholesale</span>

                    <h1 class="pk-hero-dark__title">
                        Premium Kratom. Wholesale &amp; Bulk Supply.
                    </h1>

                    <p class="pk-hero-dark__lead">
                        Your US-Based Wholesale Partner for Mitragyna speciosa Powder &amp; Extract. Authorized members gain private access to standardized specifications, lot documentation, and structured B2B agreements.
                    </p>

                    <div class="pk-hero-dark__actions">
                        <a href="{{ route('shop.customer.session.index') }}" class="pk-btn pk-btn--earth" style="min-height: 46px; padding-inline: 26px;">
                            Member Sign In &rarr;
                        </a>

                        <a
                            href="{{ route('shop.customers.register.index') }}"
                            class="pk-btn pk-btn--secondary"
                            style="min-height: 46px; padding-inline: 24px; background: transparent; color: #F7F4EC; border-color: rgba(255,255,255,0.34);"
                        >
                            Apply for Wholesale Account
                        </a>
                    </div>
                </div>

                <aside class="pk-hero-dark__card" aria-label="Commercial Portal Access Tiers">
                    <span class="pk-eyebrow pk-eyebrow--light">Member Access Structure</span>

                    <h2 class="pk-serif" style="margin: 10px 0 14px; font-size: 28px; color: #FFFFFF; font-weight: 500;">
                        Private Commercial Custody
                    </h2>

                    <div class="pk-stack-sm" style="font-size: 13px; color: rgba(247, 244, 236, 0.82); line-height: 1.55;">
                        <div style="border-top: 1px solid rgba(255,255,255,0.14); padding-top: 12px;">
                            <div style="font-family: var(--pk-font-mono); font-size: 11px; color: var(--pk-earth-light); text-transform: uppercase; letter-spacing: 0.08em;">
                                Tier 01 &bull; Registered Member
                            </div>
                            <p style="margin: 4px 0 0;">
                                Immediate portal access to product specifications, indicative pricing models, Certificates of Analysis, and admin messaging.
                            </p>
                        </div>

                        <div style="border-top: 1px solid rgba(255,255,255,0.14); padding-top: 12px;">
                            <div style="font-family: var(--pk-font-mono); font-size: 11px; color: var(--pk-earth-light); text-transform: uppercase; letter-spacing: 0.08em;">
                                Tier 02 &bull; Verified Wholesale Buyer
                            </div>
                            <p style="margin: 4px 0 0;">
                                Unlocked after business credential review &mdash; formal quotations, commercial RFQs, and purchase order execution.
                            </p>
                        </div>

                        <div style="border-top: 1px solid rgba(255,255,255,0.14); padding-top: 12px; font-size: 11.5px; color: rgba(247, 244, 236, 0.65);">
                            Authorized commercial accounts only. Age restricted. 21+ only.
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="pk-section-band">
            <div class="pk-container">
                <div style="max-width: 640px; margin-bottom: 28px;">
                    <span class="pk-eyebrow">Wholesale Standards</span>
                    <h2 class="pk-serif" style="margin: 8px 0 0; font-size: 36px; font-weight: 600; color: var(--pk-primary); line-height: 1.15;">
                        Built for Serious Botanical Buyers
                    </h2>
                </div>

                <div class="pk-grid-3">
                    <article class="pk-pillar-card">
                        <span class="pk-pillar-card__num">01 // WHOLESALE SUPPLY</span>
                        <h3 class="pk-pillar-card__title">Structured Bulk Procurement</h3>
                        <p class="pk-pillar-card__text">
                            Consistent sourcing for standardized Mitragyna speciosa powder and extract SKUs with transparent MOQ tiers and domestic fulfillment options.
                        </p>
                    </article>

                    <article class="pk-pillar-card">
                        <span class="pk-pillar-card__num">02 // LAB CUSTODY</span>
                        <h3 class="pk-pillar-card__title">Documentation &amp; COAs</h3>
                        <p class="pk-pillar-card__text">
                            Private member access to batch-specific Certificates of Analysis (COA), Technical Data Sheets (TDS), and compliance records before ordering.
                        </p>
                    </article>

                    <article class="pk-pillar-card">
                        <span class="pk-pillar-card__num">03 // VERIFIED TRADE</span>
                        <h3 class="pk-pillar-card__title">Commercial Agreement Flow</h3>
                        <p class="pk-pillar-card__text">
                            Vetted onboarding for licensed commercial formulators, botanical brands, and wholesale distributors with in-portal agreement review.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section class="pk-section-band pk-section-band--alt">
            <div class="pk-container">
                <div class="pk-panel">
                    <div class="pk-page-header__row" style="align-items: center;">
                        <div>
                            <span class="pk-eyebrow">Chicago Trade Desk</span>
                            <h2 class="pk-panel__title" style="margin-top: 6px;">
                                Purnature Khatulistiwa LLC &mdash; General Inquiries
                            </h2>
                            <p class="pk-panel__subtitle">
                                505 N Lake Shore Dr, Suite 3404, Chicago, IL 60611, USA &bull;
                                <a href="tel:+17734256646" class="font-semibold pk-text-primary hover:underline">+1 (773) 425-6646</a> &bull;
                                <a href="mailto:wholesale@purnaturek.com" class="font-semibold pk-text-primary hover:underline">wholesale@purnaturek.com</a>
                            </p>
                        </div>

                        <div class="pk-page-header__actions">
                            <a
                                href="https://purnaturekbulk.com/"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="pk-btn pk-btn--secondary"
                            >
                                Visit Corporate Website &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
</x-shop::layouts>

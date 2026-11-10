{{--
    PurNatureK Members Portal — Wholesale Partnership Information
    (shop::customers.account.partnership_information.index)
--}}
<x-shop::layouts.account>
    <x-slot:title>
        {{ $page->title }} — PurNatureK Members Portal
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="pk-main pk-stack-md" v-pre>
        <!-- Editorial Page Header -->
        <x-shop::portal.page-header
            eyebrow="Commercial Cooperation Briefing"
            :title="$page->title"
            :subtitle="$page->description"
        >
            <x-slot:actions>
                <x-shop::portal.badge variant="verified">Member Access Only</x-shop::portal.badge>
            </x-slot:actions>

            @if ($isPublished && $sections->isNotEmpty())
                <x-slot:footer>
                    <nav class="pk-anchor-strip" aria-label="Section quick links">
                        @foreach ($sections as $sec)
                            <a href="#{{ $sec->section_key }}" class="pk-anchor-pill">
                                <span class="pk-anchor-pill__dot" aria-hidden="true"></span>
                                {{ $sec->title }}
                            </a>
                        @endforeach
                    </nav>
                </x-slot:footer>
            @endif
        </x-shop::portal.page-header>

        <!-- Briefing Body -->
        <x-shop::portal.card>
            @if ($isPublished && $sections->isNotEmpty())
                <div class="pk-stack-lg">
                    @foreach ($sections as $section)
                        <article id="{{ $section->section_key }}" class="pk-editorial-section">
                            <h2 class="pk-editorial-section__title">
                                {{ $section->title }}
                            </h2>

                            <div class="pk-editorial-prose">
                                {!! \App\Support\WholesalePartnershipManager::formatSafeContent($section->content) !!}
                            </div>

                            @if ($section->section_key === 'us_wholesale_buyer_partnership')
                                <div class="pk-callout-band">
                                    <span>Ready to initiate formal wholesale buyer account onboarding?</span>
                                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                                        <x-shop::portal.button
                                            :href="route('shop.customers.account.buyer_verification.index')"
                                            variant="primary"
                                            size="sm"
                                        >
                                            Complete Buyer Verification &rarr;
                                        </x-shop::portal.button>
                                        <x-shop::portal.button
                                            :href="route('shop.customers.account.conversations.create', ['category' => 'partnership_inquiry', 'subject' => 'Wholesale Buyer Inquiry'])"
                                            variant="secondary"
                                            size="sm"
                                        >
                                            Message Admin &rarr;
                                        </x-shop::portal.button>
                                    </div>
                                </div>
                            @elseif ($section->section_key === 'partnership_requirements')
                                <div class="pk-callout-band">
                                    <span>Review issued commercial agreements and standard contract templates:</span>
                                    <x-shop::portal.button
                                        :href="route('shop.customers.account.contracts.index')"
                                        variant="primary"
                                        size="sm"
                                    >
                                        View Contracts &amp; Agreements &rarr;
                                    </x-shop::portal.button>
                                </div>
                            @elseif ($section->section_key === 'manufacturing_partner')
                                <div class="pk-callout-band">
                                    <span>Looking for supplier coordination standards, quality control protocols, or wholesale supply capabilities?</span>
                                    <x-shop::portal.button
                                        :href="route('shop.customers.account.company_supply.index')"
                                        variant="secondary"
                                        size="sm"
                                    >
                                        View Company &amp; Supply Information &rarr;
                                    </x-shop::portal.button>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>

                <div class="pk-callout-band pk-callout-band--hero">
                    <div>
                        <h3 class="pk-callout-band__title">Inquire About Wholesale Partnership</h3>
                        <p class="pk-callout-band__desc">
                            Connect directly with our wholesale administration team to discuss distribution agreements, custom supply models, or specialized purchasing requirements.
                        </p>
                    </div>

                    <x-shop::portal.button
                        :href="route('shop.customers.account.conversations.create', ['category' => 'partnership_inquiry', 'subject' => 'Wholesale Partnership Inquiry'])"
                        variant="primary"
                    >
                        Initiate Partnership Message &rarr;
                    </x-shop::portal.button>
                </div>
            @else
                <x-shop::portal.empty-state
                    title="Wholesale Partnership Information Unavailable"
                    description="Commercial cooperation models, distributor qualification criteria, and partnership onboarding details are currently being finalized by PurNatureK administration."
                >
                    <x-slot:actions>
                        <x-shop::portal.button
                            :href="route('shop.customers.account.conversations.create', ['category' => 'partnership_inquiry', 'subject' => 'Wholesale Partnership Inquiry'])"
                            variant="primary"
                        >
                            Contact Administration
                        </x-shop::portal.button>
                        <x-shop::portal.button
                            :href="route('shop.customers.account.index')"
                            variant="secondary"
                        >
                            Return to Portal Home
                        </x-shop::portal.button>
                    </x-slot:actions>
                </x-shop::portal.empty-state>
            @endif
        </x-shop::portal.card>
    </div>
</x-shop::layouts.account>

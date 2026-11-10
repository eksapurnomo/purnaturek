{{--
    PurNatureK Members Portal — Company & Supply Information
    (shop::customers.account.company_supply.index)
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
            eyebrow="Corporate Operational Briefing"
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
                                {!! \App\Support\CompanySupplyManager::formatSafeContent($section->content) !!}
                            </div>

                            @if ($section->section_key === 'quality_product_handling')
                                <div class="pk-callout-band">
                                    <span>
                                        Looking for batch Certificates of Analysis (COA) or technical specification sheets?
                                    </span>
                                    <x-shop::portal.button
                                        :href="route('shop.customers.account.index') . '#catalog'"
                                        variant="primary"
                                        size="sm"
                                    >
                                        View Product Documentation in Catalog &rarr;
                                    </x-shop::portal.button>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>

                <div class="pk-callout-band pk-callout-band--hero">
                    <div>
                        <h3 class="pk-callout-band__title">Questions Regarding Supply Operations?</h3>
                        <p class="pk-callout-band__desc">
                            Our wholesale team is available to provide operational parameters, packaging formats, and commercial distribution logistics.
                        </p>
                    </div>

                    <x-shop::portal.button
                        :href="route('shop.customers.account.conversations.create', ['category' => 'general_inquiry', 'subject' => 'Company & Supply Inquiry'])"
                        variant="primary"
                    >
                        Message Admin Regarding Supply &rarr;
                    </x-shop::portal.button>
                </div>
            @else
                <x-shop::portal.empty-state
                    title="Company & Supply Information Unavailable"
                    description="Operational specifications, quality standards, and supply workflows are currently being finalized by PurNatureK administration before member publication."
                >
                    <x-slot:actions>
                        <x-shop::portal.button
                            :href="route('shop.customers.account.conversations.create', ['category' => 'general_inquiry', 'subject' => 'Company & Supply Status Inquiry'])"
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

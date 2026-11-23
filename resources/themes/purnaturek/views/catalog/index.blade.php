{{--
    PurNatureK Members Portal — Dedicated Wholesale Catalog Listing (/catalog)
    (shop::catalog.index)

    Data ($catalogCards, $catalogProducts, $catalogCategories, etc.) is supplied by
    App\View\Composers\MemberCatalogComposer.
--}}
@php
    $catalogCustomer = auth()->guard('customer')->user();
    $verificationStatus = $catalogCustomer
        ? \App\Support\BuyerVerificationManager::getVerificationStatus($catalogCustomer)
        : null;
    $isVerified = $catalogCustomer
        ? \App\Support\BuyerVerificationManager::isVerifiedBuyer($catalogCustomer)
        : false;
@endphp

<x-shop::layouts.account>
    <x-slot:title>
        Wholesale Product Catalog — PurNatureK Members Portal
    </x-slot>

    <!-- Desktop Sidebar Navigation -->
    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <!-- Main Catalog Area -->
    <div class="pk-stack-md" id="catalog" v-pre>
        <!-- Breadcrumb Navigation -->
        <nav class="pk-product__breadcrumb" aria-label="Breadcrumb" style="margin-bottom: 0;">
            <a href="{{ route('shop.customers.account.index') }}" class="pk-product__back">
                Member Workspace
            </a>
            <span aria-hidden="true">/</span>
            <span aria-current="page" class="pk-mono text-xs pk-text-primary">Wholesale Catalog</span>
        </nav>

        <!-- Catalog Header & Search Toolbar -->
        <x-shop::portal.page-header
            eyebrow="Private Commercial Catalog"
            title="Wholesale Product Catalog"
            subtitle="Explore approved botanical products, review verified commercial specifications, inspect pricing terms, and initiate inquiries with our Chicago wholesale desk."
        >
            <x-slot:actions>
                @if ($isVerified)
                    <x-shop::portal.badge variant="verified">&check; Verified Wholesale Buyer</x-shop::portal.badge>
                @elseif ($verificationStatus === 'pending')
                    <x-shop::portal.badge variant="pending">Verification Pending</x-shop::portal.badge>
                @else
                    <x-shop::portal.badge variant="neutral">Tier 1 Registered Member</x-shop::portal.badge>
                @endif

                @if ($memberGroupName)
                    <x-shop::portal.badge variant="info">Group: {{ $memberGroupName }}</x-shop::portal.badge>
                @endif

                @if ($memberIsCompany)
                    <x-shop::portal.badge variant="earth">Corporate Account</x-shop::portal.badge>
                @endif
            </x-slot>

            <x-slot:footer>
                <form
                    method="GET"
                    action="{{ route('shop.catalog.index') }}"
                    class="pk-filter-bar"
                    role="search"
                >
                    <div class="pk-filter-bar__search">
                        <input
                            type="search"
                            name="query"
                            value="{{ $catalogSearch }}"
                            placeholder="Search by product name or SKU..."
                            aria-label="Search wholesale catalog"
                            class="pk-input"
                            maxlength="100"
                        />
                    </div>

                    @if (count($catalogCategories))
                        <div class="pk-filter-bar__select">
                            <select
                                name="category_id"
                                aria-label="Filter by category"
                                class="pk-select"
                            >
                                <option value="">All Categories</option>
                                @foreach ($catalogCategories as $category)
                                    <option
                                        value="{{ $category['id'] }}"
                                        @selected($catalogCategory === $category['id'])
                                    >
                                        {{ str_repeat('— ', $category['depth']) }}{{ $category['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <button type="submit" class="pk-btn pk-btn--primary" style="min-height: 44px;">
                        Search Catalog
                    </button>

                    @if ($catalogFiltered)
                        <a
                            href="{{ route('shop.catalog.index') }}"
                            class="pk-btn pk-btn--secondary"
                            style="min-height: 44px;"
                        >
                            Reset Filters
                        </a>
                    @endif
                </form>

                @if ($catalogFiltered)
                    <p class="pk-help-text" style="margin-top: 12px;">
                        Showing <strong>{{ $catalogProducts->total() }}</strong> {{ \Illuminate\Support\Str::plural('product', $catalogProducts->total()) }} matching active filters.
                    </p>
                @endif
            </x-slot>
        </x-shop::portal.page-header>

        <!-- Product Grid -->
        @if (count($catalogCards))
            <div class="pk-grid">
                @foreach ($catalogCards as $card)
                    <x-shop::portal.product-card :card="$card" />
                @endforeach
            </div>

            @if ($catalogProducts->hasPages())
                <div class="pk-panel" style="padding: 14px 18px;">
                    {{ $catalogProducts->links() }}
                </div>
            @endif
        @else
            <x-shop::portal.empty-state
                title="No wholesale products found"
                description="No catalog entries match your active search or category criteria. Clear filters or message our commercial desk regarding product inquiries."
            >
                <x-slot:actions>
                    @if ($catalogFiltered)
                        <x-shop::portal.button :href="route('shop.catalog.index')" variant="primary" size="sm">
                            Clear Filters
                        </x-shop::portal.button>
                    @endif
                    <x-shop::portal.button :href="route('shop.customers.account.conversations.create', ['category' => 'product_inquiry', 'subject' => 'Catalog Sourcing Inquiry'])" variant="secondary" size="sm">
                        Message Admin
                    </x-shop::portal.button>
                </x-slot>
            </x-shop::portal.empty-state>
        @endif
    </div>
</x-shop::layouts.account>


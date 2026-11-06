{{--
    PurNatureK Members Portal — landing page (override of shop::customers.account.index).

    Route: shop.customers.account.index (customer/account), unchanged.
    Data (catalogCards, catalogProducts, catalogCategories, ...) is supplied by
    App\View\Composers\MemberCatalogComposer, registered in AppServiceProvider.
--}}
<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        PurNatureK Members Portal
    </x-slot>

    <x-shop::layouts.account.navigation />

    <section
        class="pk-main"
        id="catalog"
        v-pre
    >
        <!-- Compact welcome + membership indicator (existing customer data only) -->
        <div class="pk-welcome">
            <span>
                Welcome, <strong>{{ auth()->guard('customer')->user()->first_name }}</strong>
            </span>

            <span class="pk-pills">
                @if ($memberGroupName)
                    <span class="pk-pill">Customer group: {{ $memberGroupName }}</span>
                @endif

                @if ($memberIsCompany)
                    <span class="pk-pill">Company account</span>
                @endif
            </span>
        </div>

        <h1 class="pk-page-title">Wholesale Product Catalog</h1>

        <p class="pk-page-intro">
            Browse the Purnature Khatulistiwa product range and open any product for its full details.
            Pricing and ordering follow the access rules of your account.
        </p>

        <!-- Search + category filter (GET form against the same page) -->
        <form
            method="GET"
            action="{{ route('shop.customers.account.index') }}#catalog"
            class="pk-filters"
            role="search"
        >
            <input
                type="search"
                name="query"
                value="{{ $catalogSearch }}"
                placeholder="Search by product name or SKU"
                aria-label="Search products"
                maxlength="100"
            >

            @if (count($catalogCategories))
                <select
                    name="category_id"
                    aria-label="Filter by category"
                >
                    <option value="">All categories</option>

                    @foreach ($catalogCategories as $category)
                        <option
                            value="{{ $category['id'] }}"
                            @selected($catalogCategory === $category['id'])
                        >
                            {{ str_repeat('— ', $category['depth']) }}{{ $category['name'] }}
                        </option>
                    @endforeach
                </select>
            @endif

            <button type="submit" class="pk-btn pk-btn--primary">Search</button>

            @if ($catalogFiltered)
                <a
                    href="{{ route('shop.customers.account.index') }}#catalog"
                    class="pk-btn pk-btn--ghost"
                >
                    Clear
                </a>
            @endif
        </form>

        @if (count($catalogCards))
            @if ($catalogFiltered)
                <p class="pk-result-count">
                    {{ $catalogProducts->total() }} {{ \Illuminate\Support\Str::plural('product', $catalogProducts->total()) }} found
                </p>
            @endif

            <!-- Product grid -->
            <div class="pk-grid">
                @foreach ($catalogCards as $card)
                    @php
                        $pricing = $card['pricing'] ?? null;
                    @endphp

                    <article class="pk-card">
                        <a
                            href="{{ $card['url'] }}"
                            class="pk-card__media"
                            tabindex="-1"
                            aria-hidden="true"
                        >
                            @if ($card['image'])
                                <img
                                    src="{{ $card['image'] }}"
                                    alt="{{ $card['alt'] }}"
                                    loading="lazy"
                                >
                            @endif
                        </a>

                        <div class="pk-card__body">
                            <h2 class="pk-card__name">
                                <a href="{{ $card['url'] }}" class="pk-card__title-link">
                                    {{ $card['name'] }}
                                </a>
                            </h2>

                            <span class="pk-card__sku">SKU: {{ $card['sku'] }}</span>

                            @if ($pricing)
                                <div class="pk-card__pricing">
                                    @if ($pricing['is_range'])
                                        <span class="pk-card__price pk-card__price--range">
                                            {{ $pricing['price_display'] }}
                                        </span>
                                    @else
                                        <span class="pk-card__price pk-card__price--contact">
                                            {{ $pricing['price_display'] }}
                                        </span>
                                    @endif

                                    @if (! empty($pricing['moq_display']) || ! empty($pricing['supply_availability']))
                                        <div class="pk-card__meta">
                                            @if (! empty($pricing['moq_display']))
                                                <span class="pk-card__badge pk-card__badge--moq">
                                                    MOQ: {{ $pricing['moq_display'] }}
                                                </span>
                                            @endif

                                            @if (! empty($pricing['supply_availability']))
                                                <span class="pk-card__badge pk-card__badge--supply">
                                                    {{ $pricing['supply_availability'] }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <p class="pk-card__summary">{{ $card['summary'] }}</p>

                            <div class="pk-card__cta">
                                <a
                                    href="{{ $card['url'] }}"
                                    class="pk-btn pk-btn--ghost"
                                    aria-label="View details for {{ $card['name'] }}"
                                >
                                    View Details
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($catalogProducts->hasPages())
                <nav class="pk-pager" aria-label="Catalog pages">
                    <a
                        href="{{ $catalogProducts->previousPageUrl() ?? '#' }}"
                        class="pk-btn pk-btn--ghost {{ $catalogProducts->onFirstPage() ? 'is-disabled' : '' }}"
                        rel="prev"
                    >
                        Previous
                    </a>

                    <span>Page {{ $catalogProducts->currentPage() }} of {{ $catalogProducts->lastPage() }}</span>

                    <a
                        href="{{ $catalogProducts->nextPageUrl() ?? '#' }}"
                        class="pk-btn pk-btn--ghost {{ $catalogProducts->hasMorePages() ? '' : 'is-disabled' }}"
                        rel="next"
                    >
                        Next
                    </a>
                </nav>
            @endif
        @elseif ($catalogFiltered)
            <div class="pk-empty">
                <h2>No matching products</h2>

                <p>Try a different name or SKU, or clear the filters to see the full catalog.</p>

                <a
                    href="{{ route('shop.customers.account.index') }}#catalog"
                    class="pk-btn pk-btn--primary"
                >
                    Clear filters
                </a>
            </div>
        @else
            <div class="pk-empty">
                <h2>Product information will be available soon</h2>

                <p>The wholesale catalog is being prepared. Please check back shortly.</p>
            </div>
        @endif
    </section>
</x-shop::layouts.account>

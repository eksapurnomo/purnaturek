{{--
    PurNatureK Members Portal — Member Catalog Homepage
    (shop::customers.account.index).

    Data (catalogCards, catalogProducts, catalogCategories, etc.) is supplied by
    App\View\Composers\MemberCatalogComposer.
--}}
<x-shop::layouts.account>
    <x-slot:title>
        Wholesale Catalog — PurNatureK Members Portal
    </x-slot>

    <!-- Sidebar Navigation -->
    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <!-- Main Workspace Area -->
    <div class="flex-1 min-w-0" id="catalog" v-pre>
        <div class="space-y-6">
            <!-- Welcome Header Card -->
            <div class="bg-white border border-[#d9e6e1] rounded-2xl p-6 sm:p-8 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[#d9e6e1] pb-5">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">
                                Commercial Catalog
                            </span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f4a3e] mt-1">
                            Wholesale Product Catalog
                        </h1>
                        <p class="text-xs sm:text-sm text-[#5a6f68] mt-1">
                            Browse verified botanical raw materials, extract specifications, and batch supply models.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2 text-xs">
                        @if ($memberGroupName)
                            <span class="rounded-full bg-[#f0f7f4] border border-[#d9e6e1] px-3 py-1 font-semibold text-[#0f4a3e]">
                                Group: {{ $memberGroupName }}
                            </span>
                        @endif

                        @if ($memberIsCompany)
                            <span class="rounded-full bg-[#fefce8] border border-[#fef08a] px-3 py-1 font-bold text-[#854d0e]">
                                Corporate Account
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Search & Category Filters -->
                <form
                    method="GET"
                    action="{{ route('shop.customers.account.index') }}#catalog"
                    class="mt-6 flex flex-wrap gap-3"
                    role="search"
                >
                    <div class="flex-1 min-w-[240px]">
                        <input
                            type="search"
                            name="query"
                            value="{{ $catalogSearch }}"
                            placeholder="Search by botanical name, extract type, or SKU..."
                            class="w-full rounded-xl border border-[#d9e6e1] bg-white px-4 py-2.5 text-sm text-[#1e2d29] focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600 shadow-sm"
                            maxlength="100"
                        />
                    </div>

                    @if (count($catalogCategories))
                        <div class="w-full sm:w-auto min-w-[200px]">
                            <select
                                name="category_id"
                                aria-label="Filter by category"
                                class="w-full rounded-xl border border-[#d9e6e1] bg-white px-4 py-2.5 text-sm text-[#1e2d29] focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600 shadow-sm"
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

                    <button
                        type="submit"
                        class="pk-btn pk-btn--primary py-2.5 px-6 text-xs font-bold"
                    >
                        Search Catalog
                    </button>

                    @if ($catalogFiltered)
                        <a
                            href="{{ route('shop.customers.account.index') }}#catalog"
                            class="pk-btn pk-btn--ghost py-2.5 px-4 text-xs font-semibold"
                        >
                            Reset Filters
                        </a>
                    @endif
                </form>

                @if ($catalogFiltered)
                    <p class="mt-4 text-xs text-[#5a6f68]">
                        Found <strong>{{ $catalogProducts->total() }}</strong> {{ \Illuminate\Support\Str::plural('product', $catalogProducts->total()) }} matching criteria.
                    </p>
                @endif
            </div>

            <!-- Product Grid -->
            @if (count($catalogCards))
                <div class="pk-grid">
                    @foreach ($catalogCards as $card)
                        @php
                            $pricing = $card['pricing'] ?? null;
                        @endphp

                        <article class="pk-card">
                            <!-- Card Media -->
                            <a
                                href="{{ $card['url'] }}"
                                class="pk-card__media"
                                tabindex="-1"
                                aria-hidden="true"
                            >
                                @if (! empty($card['image']))
                                    <img
                                        src="{{ $card['image'] }}"
                                        alt="{{ $card['alt'] ?? $card['name'] }}"
                                        loading="lazy"
                                    />
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center p-4 text-center bg-gradient-to-b from-[#f0f7f4] to-[#e4efe9] text-[#5a6f68]">
                                        <div class="h-10 w-10 rounded-full bg-emerald-800/10 flex items-center justify-center mb-1 text-emerald-800">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        </div>
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#0f4a3e]">PurNatureK Botanical Extract</span>
                                        <span class="text-[10px] text-zinc-400 mt-1">Pending Photography</span>
                                    </div>
                                @endif
                            </a>

                            <!-- Card Body -->
                            <div class="pk-card__body">
                                <div class="pk-card__header-line">
                                    <span class="pk-card__sku">SKU: {{ $card['sku'] }}</span>
                                </div>

                                <h2 class="pk-card__name">
                                    <a href="{{ $card['url'] }}">
                                        {{ $card['name'] }}
                                    </a>
                                </h2>

                                <!-- Wholesale Pricing Block -->
                                @if ($pricing)
                                    <div class="pk-card__pricing-box">
                                        <span class="pk-card__price-label">Wholesale Price Model</span>

                                        @if (! empty($pricing['is_range']))
                                            <span class="pk-card__price-val">
                                                {{ $pricing['price_display'] }}
                                            </span>
                                        @else
                                            <span class="pk-card__price-val pk-card__price-val--contact">
                                                {{ $pricing['price_display'] }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Spec & Supply Badges -->
                                    <div class="pk-card__pill-row">
                                        @if (! empty($pricing['moq_display']))
                                            <span class="pk-card__pill pk-card__pill--moq">
                                                MOQ: {{ $pricing['moq_display'] }}
                                            </span>
                                        @endif

                                        @if (! empty($pricing['supply_availability']))
                                            <span class="pk-card__pill">
                                                {{ $pricing['supply_availability'] }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                @if (! empty($card['summary']))
                                    <p class="text-xs text-[#5a6f68] line-clamp-2 mt-1">
                                        {{ $card['summary'] }}
                                    </p>
                                @endif

                                <!-- CTA -->
                                <div class="pk-card__cta">
                                    <a
                                        href="{{ $card['url'] }}"
                                        class="pk-btn pk-btn--ghost text-xs font-bold"
                                    >
                                        View Product Specifications &rarr;
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if ($catalogProducts->hasPages())
                    <div class="bg-white border border-[#d9e6e1] rounded-xl p-4 flex justify-center shadow-sm">
                        {{ $catalogProducts->links() }}
                    </div>
                @endif

            @else
                <!-- Empty State -->
                <div class="bg-white border border-dashed border-[#d9e6e1] rounded-2xl p-12 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#f0f7f4] text-emerald-800 mb-3">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-[#0f4a3e]">No wholesale products found</h3>
                    <p class="text-xs text-[#5a6f68] mt-1 max-w-sm mx-auto">
                        No items match your active search or category filters. Try clearing your filters or contact administration for custom sourcing inquiries.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('shop.customers.account.index') }}#catalog" class="pk-btn pk-btn--primary text-xs py-2 px-4">
                            Clear Filters
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-shop::layouts.account>

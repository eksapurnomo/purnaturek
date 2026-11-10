{{--
    PurNatureK Members Portal — Member Workspace & Wholesale Catalog
    (shop::customers.account.index).

    Data ($catalogCards, $catalogProducts, $catalogCategories, etc.) is supplied by
    App\View\Composers\MemberCatalogComposer.
--}}
@php
    $workspaceCustomer = auth()->guard('customer')->user();
    $verificationStatus = $workspaceCustomer
        ? \App\Support\BuyerVerificationManager::getVerificationStatus($workspaceCustomer)
        : null;
    $isVerified = $workspaceCustomer
        ? \App\Support\BuyerVerificationManager::isVerifiedBuyer($workspaceCustomer)
        : false;
    $unreadCount = $workspaceCustomer
        ? \App\Support\MemberConversationManager::getUnreadCountForMember($workspaceCustomer)
        : 0;
    $pendingContracts = $workspaceCustomer
        ? \App\Models\MemberContract::query()
            ->forCustomer((int) $workspaceCustomer->id)
            ->where('status', \App\Models\MemberContract::STATUS_SENT)
            ->count()
        : 0;
@endphp

<x-shop::layouts.account>
    <x-slot:title>
        Wholesale Catalog — PurNatureK Members Portal
    </x-slot>

    <!-- Desktop Sidebar Navigation -->
    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <!-- Main Workspace Area -->
    <div class="pk-stack-md" id="catalog" v-pre>
        <!-- Tier Verification Contextual Banner -->
        @if ($workspaceCustomer && ! $isVerified)
            @if ($verificationStatus === 'pending')
                <x-shop::portal.alert
                    variant="warning"
                    title="Buyer Verification Under Commercial Review"
                >
                    You currently have Tier 1 Member access to specifications and documentation. Tier 2 quotation and wholesale purchasing workflows unlock upon application approval.
                    <x-slot:actions>
                        <x-shop::portal.button :href="route('shop.customers.account.buyer_verification.index')" variant="secondary" size="sm">
                            View Application Status &rarr;
                        </x-shop::portal.button>
                    </x-slot>
                </x-shop::portal.alert>
            @elseif ($verificationStatus === 'rejected')
                <x-shop::portal.alert
                    variant="danger"
                    title="Verification Action Required"
                >
                    Your previous buyer verification submission requires updated business documentation before Tier 2 purchasing privileges can be enabled.
                    <x-slot:actions>
                        <x-shop::portal.button :href="route('shop.customers.account.buyer_verification.index')" variant="primary" size="sm">
                            Update Submission &rarr;
                        </x-shop::portal.button>
                    </x-slot>
                </x-shop::portal.alert>
            @else
                <x-shop::portal.alert
                    variant="info"
                    title="Complete Tier 2 Buyer Verification to Unlock Quotations"
                >
                    Submit your business registration and resale documentation to request official B2B quotes and submit commercial purchase orders.
                    <x-slot:actions>
                        <x-shop::portal.button :href="route('shop.customers.account.buyer_verification.index')" variant="primary" size="sm">
                            Start Verification &rarr;
                        </x-shop::portal.button>
                    </x-slot>
                </x-shop::portal.alert>
            @endif
        @endif

        <!-- Quick Workspace Actions (Shown on initial landing / unfiltered overview) -->
        @if (! $catalogFiltered && $catalogProducts->currentPage() === 1)
            <div class="pk-grid-4">
                <a href="{{ route('shop.customers.account.quotes.index') }}" class="pk-shortcut-card">
                    <div class="pk-shortcut-card__top">
                        <span class="pk-eyebrow">RFQ Desk</span>
                        <span class="pk-text-primary text-xs font-bold">&rarr;</span>
                    </div>
                    <span class="pk-shortcut-card__title" style="font-size: 18px;">Quotations</span>
                    <p class="pk-shortcut-card__desc">Active price quotes &amp; RFQs</p>
                </a>

                <a href="{{ route('shop.customers.account.contracts.index') }}" class="pk-shortcut-card">
                    <div class="pk-shortcut-card__top">
                        <span class="pk-eyebrow">Agreements</span>
                        @if ($pendingContracts > 0)
                            <x-shop::portal.badge variant="pending" size="sm">{{ $pendingContracts }} pending</x-shop::portal.badge>
                        @else
                            <span class="pk-text-primary text-xs font-bold">&rarr;</span>
                        @endif
                    </div>
                    <span class="pk-shortcut-card__title" style="font-size: 18px;">Contracts</span>
                    <p class="pk-shortcut-card__desc">Master supply terms &amp; NDAs</p>
                </a>

                <a href="{{ route('shop.customers.account.buyer_verification.index') }}" class="pk-shortcut-card">
                    <div class="pk-shortcut-card__top">
                        <span class="pk-eyebrow">Compliance</span>
                        @if ($isVerified)
                            <x-shop::portal.badge variant="verified" size="sm">Tier 2</x-shop::portal.badge>
                        @elseif ($verificationStatus === 'pending')
                            <x-shop::portal.badge variant="pending" size="sm">Pending</x-shop::portal.badge>
                        @else
                            <x-shop::portal.badge variant="neutral" size="sm">Tier 1</x-shop::portal.badge>
                        @endif
                    </div>
                    <span class="pk-shortcut-card__title" style="font-size: 18px;">Verification</span>
                    <p class="pk-shortcut-card__desc">Commercial buyer status</p>
                </a>

                <a href="{{ route('shop.customers.account.conversations.index') }}" class="pk-shortcut-card">
                    <div class="pk-shortcut-card__top">
                        <span class="pk-eyebrow">Support</span>
                        @if ($unreadCount > 0)
                            <x-shop::portal.badge variant="counter" size="sm">{{ $unreadCount }} unread</x-shop::portal.badge>
                        @else
                            <span class="pk-text-primary text-xs font-bold">&rarr;</span>
                        @endif
                    </div>
                    <span class="pk-shortcut-card__title" style="font-size: 18px;">Message Admin</span>
                    <p class="pk-shortcut-card__desc">Direct Chicago desk thread</p>
                </a>
            </div>
        @endif

        <!-- Customer-Scoped Recently Viewed Products (Hydrated via localStorage) -->
        @if ($workspaceCustomer)
            <section id="pk-recent-section" class="pk-panel" hidden>
                <div class="pk-panel__head" style="margin-bottom: 14px; padding-bottom: 10px;">
                    <div>
                        <span class="pk-eyebrow">Quick Resume</span>
                        <h2 class="pk-panel__title" style="font-size: 20px; margin-top: 2px;">Recently Viewed Specifications</h2>
                    </div>
                    <button
                        type="button"
                        id="pk-recent-clear"
                        class="pk-btn pk-btn--secondary pk-btn--sm"
                    >
                        Clear History
                    </button>
                </div>

                <div id="pk-recent-list" class="pk-recent-strip"></div>
            </section>
        @endif

        <!-- Catalog Header & Search Toolbar -->
        <x-shop::portal.page-header
            eyebrow="Commercial Catalog"
            title="Wholesale Product Catalog"
            subtitle="Browse verified botanical powders, standardized extracts, MOQ schedules, and commercial supply documentation."
        >
            <x-slot:actions>
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
                    action="{{ route('shop.customers.account.index') }}#catalog"
                    class="pk-filter-bar"
                    role="search"
                >
                    <div class="pk-filter-bar__search">
                        <input
                            type="search"
                            name="query"
                            value="{{ $catalogSearch }}"
                            placeholder="Search by botanical name, extract grade, or SKU..."
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
                            href="{{ route('shop.customers.account.index') }}#catalog"
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
                            @if (! empty($card['image']))
                                <img
                                    src="{{ $card['image'] }}"
                                    alt="{{ $card['alt'] ?? $card['name'] }}"
                                    loading="lazy"
                                />
                            @else
                                <div class="pk-card__placeholder">
                                    <span class="pk-eyebrow">PUR NATUREK</span>
                                    <span class="text-xs font-semibold pk-text-primary" style="margin-top: 6px;">Botanical Specification</span>
                                    <span class="text-[11px] pk-text-muted" style="margin-top: 2px;">Photography Pending</span>
                                </div>
                            @endif
                        </a>

                        <div class="pk-card__body">
                            <span class="pk-card__sku">SKU // {{ $card['sku'] }}</span>

                            <h2 class="pk-card__name">
                                <a href="{{ $card['url'] }}">
                                    {{ $card['name'] }}
                                </a>
                            </h2>

                            @if ($pricing)
                                <div class="pk-card__pricing-box">
                                    <span class="pk-card__price-label">Indicative Wholesale Range</span>

                                    @if (! empty($pricing['is_range']))
                                        <span class="pk-card__price-val">{{ $pricing['price_display'] }}</span>
                                    @else
                                        <span class="pk-card__price-val pk-card__price-val--contact">{{ $pricing['price_display'] }}</span>
                                    @endif
                                </div>

                                <div class="pk-card__pill-row">
                                    @if (! empty($pricing['moq_display']))
                                        <x-shop::portal.badge variant="earth" size="sm">
                                            MOQ: {{ $pricing['moq_display'] }}
                                        </x-shop::portal.badge>
                                    @endif

                                    @if (! empty($pricing['supply_availability']))
                                        <x-shop::portal.badge variant="verified" size="sm">
                                            {{ $pricing['supply_availability'] }}
                                        </x-shop::portal.badge>
                                    @endif
                                </div>
                            @endif

                            @if (! empty($card['summary']))
                                <p class="pk-card__summary">{{ $card['summary'] }}</p>
                            @endif

                            <div class="pk-card__cta">
                                <a
                                    href="{{ $card['url'] }}"
                                    class="pk-btn pk-btn--secondary pk-btn--block"
                                >
                                    View Specifications &rarr;
                                </a>
                            </div>
                        </div>
                    </article>
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
                description="No catalog entries match your active search or category criteria. Clear filters or message our commercial desk regarding custom sourcing requests."
            >
                <x-slot:actions>
                    @if ($catalogFiltered)
                        <x-shop::portal.button :href="route('shop.customers.account.index') . '#catalog'" variant="primary" size="sm">
                            Clear Filters
                        </x-shop::portal.button>
                    @endif
                    <x-shop::portal.button :href="route('shop.customers.account.conversations.create', ['category' => 'product_inquiry', 'subject' => 'Catalog Sourcing Inquiry'])" variant="secondary" size="sm">
                        Message Commercial Desk
                    </x-shop::portal.button>
                </x-slot>
            </x-shop::portal.empty-state>
        @endif
    </div>

    @if ($workspaceCustomer)
        @push('scripts')
            <script>
                (function () {
                    var storageKey = 'pk_recent_{{ (int) $workspaceCustomer->id }}';
                    var section = document.getElementById('pk-recent-section');
                    var list = document.getElementById('pk-recent-list');
                    var clearBtn = document.getElementById('pk-recent-clear');
                    if (!section || !list) return;

                    function isSafeLocalUrl(url) {
                        if (typeof url !== 'string' || !url) return false;
                        return url.indexOf('/') === 0 || url.indexOf(window.location.origin + '/') === 0;
                    }

                    function renderRecent() {
                        try {
                            var raw = localStorage.getItem(storageKey);
                            if (!raw) return;
                            var items = JSON.parse(raw);
                            if (!Array.isArray(items) || !items.length) return;

                            list.textContent = '';
                            var rendered = 0;

                            items.slice(0, 4).forEach(function (item) {
                                if (!item || !isSafeLocalUrl(item.url) || !item.name) return;

                                var link = document.createElement('a');
                                link.href = item.url;
                                link.className = 'pk-recent-item';

                                var thumb = document.createElement('div');
                                thumb.className = 'pk-recent-item__thumb';

                                if (item.image && isSafeLocalUrl(item.image)) {
                                    var img = document.createElement('img');
                                    img.src = item.image;
                                    img.alt = item.name;
                                    img.loading = 'lazy';
                                    thumb.appendChild(img);
                                } else {
                                    thumb.textContent = 'SKU';
                                }

                                var meta = document.createElement('div');
                                meta.style.minWidth = '0';

                                var nameEl = document.createElement('div');
                                nameEl.className = 'pk-recent-item__name';
                                nameEl.textContent = item.name;

                                var skuEl = document.createElement('div');
                                skuEl.className = 'pk-recent-item__sku';
                                skuEl.textContent = item.sku ? 'SKU: ' + item.sku : 'Specification';

                                meta.appendChild(nameEl);
                                meta.appendChild(skuEl);
                                link.appendChild(thumb);
                                link.appendChild(meta);
                                list.appendChild(link);
                                rendered++;
                            });

                            if (rendered > 0) {
                                section.hidden = false;
                            }
                        } catch (e) {}
                    }

                    if (clearBtn) {
                        clearBtn.addEventListener('click', function () {
                            try {
                                localStorage.removeItem(storageKey);
                            } catch (e) {}
                            list.textContent = '';
                            section.hidden = true;
                        });
                    }

                    renderRecent();
                })();
            </script>
        @endpush
    @endif
</x-shop::layouts.account>

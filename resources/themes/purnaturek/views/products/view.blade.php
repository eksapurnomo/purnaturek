{{--
    PurNatureK Members Portal — Private Product Detail View (PDP)
    resources/themes/purnaturek/views/products/view.blade.php
--}}
@php
    \App\Support\WholesalePricingManager::ensureAttributesProvisioned();

    $pricing ??= \App\Support\WholesalePricingManager::formatForMember($product);
    $baseImage = product_image()->getProductBaseImage($product);
    $galleryImages = product_image()->getGalleryImages($product);

    $customer ??= auth()->guard('customer')->user();
    $isVerifiedBuyer ??= \App\Support\BuyerVerificationManager::isVerifiedBuyer($customer);
    $verificationStatus ??= $customer ? \App\Support\BuyerVerificationManager::getVerificationStatus($customer) : null;
    $canAccessB2BQuotes = $customer
        && function_exists('customer_bouncer')
        && customer_bouncer()->hasPermission('account.quotes');

    $specs ??= \App\Support\ProductDocumentationManager::getProductSpecifications($product);
    $groupedDocuments ??= \App\Support\ProductDocumentationManager::getGroupedProductDocuments((int) $product->id);
    $relatedProducts ??= app(\App\View\Composers\MemberCatalogComposer::class)->relatedCardsForProduct($product, 4);
    $hasAnyDocuments = collect($groupedDocuments)->pluck('documents')->flatten(1)->isNotEmpty();

    $mainImageSrc = $baseImage['large_image_url'] ?? ($baseImage['medium_image_url'] ?? null);
    $recentThumb = $baseImage['small_image_url'] ?? ($baseImage['medium_image_url'] ?? null);

    $hasConfiguredPricingSpecs = ! empty($pricing['moq_display'])
        || ! empty($pricing['unit'])
        || ! empty($pricing['supply_availability']);
@endphp

<x-shop::layouts.account>
    <x-slot:title>
        {{ $product->name }} — Wholesale Catalog | PurNatureK Members Portal
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <section class="pk-product pk-stack-md" v-pre>
        <!-- Breadcrumb Navigation back to /catalog -->
        <nav class="pk-product__breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('shop.catalog.index') }}" class="pk-product__back">
                &larr; Wholesale Catalog
            </a>
            <span aria-hidden="true">/</span>
            <span class="pk-mono text-xs">{{ $product->sku }}</span>
            <span aria-hidden="true">/</span>
            <span aria-current="page" class="text-xs font-semibold pk-text-primary">{{ $product->name }}</span>
        </nav>

        <!-- Editorial B2B Product Hero -->
        <div class="pk-product__hero">
            <!-- Media Presentation Column -->
            <div>
                <div class="pk-product__image-main">
                    @if ($mainImageSrc)
                        <img
                            id="pk-pdp-main-img"
                            src="{{ $mainImageSrc }}"
                            alt="{{ $baseImage['alt'] ?? $product->name }}"
                        >
                    @else
                        <div class="pk-card__placeholder" style="min-height: 280px;">
                            <span class="pk-eyebrow">PUR NATUREK</span>
                            <span class="pk-serif" style="font-size: 22px; font-weight: 600; color: var(--pk-primary); margin-top: 8px;">
                                {{ $product->name }}
                            </span>
                            <span class="pk-mono text-xs pk-text-muted" style="margin-top: 6px;">SKU: {{ $product->sku }}</span>
                            <span class="text-[11px] pk-text-muted" style="margin-top: 10px;">Product Photography Pending</span>
                        </div>
                    @endif
                </div>

                @if (count($galleryImages) > 1 && $mainImageSrc)
                    <div class="pk-product__thumbs" role="list" aria-label="Product gallery thumbnails">
                        @foreach ($galleryImages as $idx => $img)
                            @php
                                $largeUrl = $img['large_image_url'] ?? ($img['medium_image_url'] ?? '');
                                $smallUrl = $img['small_image_url'] ?? $largeUrl;
                            @endphp
                            @if ($smallUrl && $largeUrl)
                                <button
                                    type="button"
                                    class="pk-product__thumb {{ $idx === 0 ? 'is-active' : '' }}"
                                    data-pk-thumb="{{ $largeUrl }}"
                                    aria-label="View product image {{ $idx + 1 }}"
                                    @if ($idx === 0) aria-current="true" @endif
                                >
                                    <img
                                        src="{{ $smallUrl }}"
                                        alt="{{ $product->name }} view {{ $idx + 1 }}"
                                        loading="lazy"
                                    >
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Commercial Overview, Flexible Pricing & Wholesale Inquiry Column -->
            <div class="pk-product__summary">
                <div>
                    <div class="pk-product__badges">
                        <x-shop::portal.badge variant="neutral">SKU: {{ $product->sku }}</x-shop::portal.badge>

                        @if (! empty($pricing['badge_label']))
                            <x-shop::portal.badge variant="earth">
                                {{ $pricing['badge_label'] }}
                            </x-shop::portal.badge>
                        @endif

                        @if (! empty($pricing['supply_availability']))
                            <x-shop::portal.badge variant="verified">
                                {{ $pricing['supply_availability'] }}
                            </x-shop::portal.badge>
                        @endif
                    </div>

                    <h1 class="pk-product__title">{{ $product->name }}</h1>

                    @if (filled($product->short_description))
                        <div class="pk-product__short-desc">
                            {!! $product->short_description !!}
                        </div>
                    @endif
                </div>

                <!-- Centralized Wholesale Pricing Section (FIXED_PRICE / STARTING_FROM / BULK_CONTACT) -->
                <div class="pk-pricing-box" data-pricing-mode="{{ $pricing['mode'] }}">
                    <div class="pk-pricing-box__header">
                        <div class="pk-pricing-box__headline-row">
                            <span class="pk-pricing-box__label">{{ $pricing['headline_label'] }}</span>
                            <span class="pk-pricing-box__mode-tag">{{ $pricing['mode_label'] }}</span>
                        </div>

                        <div class="pk-pricing-box__value {{ ! empty($pricing['is_contact']) ? 'pk-pricing-box__value--contact' : '' }}">
                            {{ $pricing['price_display'] }}
                        </div>

                        @if (! empty($pricing['price_note']))
                            <p class="pk-pricing-box__note">
                                {{ $pricing['price_note'] }}
                            </p>
                        @endif
                    </div>

                    @if ($hasConfiguredPricingSpecs)
                        <dl class="pk-pricing-box__specs pk-pricing-box__specs--auto">
                            @if (! empty($pricing['moq_display']))
                                <div class="pk-pricing-box__spec">
                                    <dt>Minimum Order (MOQ)</dt>
                                    <dd>{{ $pricing['moq_display'] }}</dd>
                                </div>
                            @endif

                            @if (! empty($pricing['unit']))
                                <div class="pk-pricing-box__spec">
                                    <dt>Unit</dt>
                                    <dd>{{ $pricing['unit'] }}</dd>
                                </div>
                            @endif

                            @if (! empty($pricing['supply_availability']))
                                <div class="pk-pricing-box__spec">
                                    <dt>Supply Availability</dt>
                                    <dd>{{ $pricing['supply_availability'] }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif

                    @if (! empty($pricing['disclaimer']))
                        <p class="pk-pricing-box__disclaimer">
                            {{ $pricing['disclaimer'] }}
                        </p>
                    @endif
                </div>

                <!-- Wholesale Inquiry Actions (Handled via Message Admin) -->
                <div class="pk-product__actions-panel">
                    <div class="pk-product__actions-head">
                        <h2 class="pk-product__actions-title">Wholesale Inquiry &amp; Samples</h2>

                        @if ($isVerifiedBuyer)
                            <x-shop::portal.badge variant="verified" size="sm">&check; Verified Wholesale Buyer</x-shop::portal.badge>
                        @elseif ($verificationStatus === 'pending')
                            <x-shop::portal.badge variant="pending" size="sm">Verification Pending Review</x-shop::portal.badge>
                        @else
                            <x-shop::portal.badge variant="neutral" size="sm">Tier 1 Registered Member</x-shop::portal.badge>
                        @endif
                    </div>

                    <div class="pk-product__actions">
                        <x-shop::portal.button
                            :href="route('shop.customers.account.conversations.create', ['category' => \App\Models\MemberConversation::CATEGORY_PRICING_INQUIRY, 'subject' => 'Wholesale Pricing Inquiry: ' . $product->name . ' (SKU: ' . $product->sku . ')'])"
                            variant="primary"
                            title="Submit a wholesale pricing and volume inquiry to PurNatureK administration"
                        >
                            Inquire via Message Admin &rarr;
                        </x-shop::portal.button>

                        <x-shop::portal.button
                            :href="route('shop.customers.account.conversations.create', ['category' => \App\Models\MemberConversation::CATEGORY_PRODUCT_INQUIRY, 'subject' => 'Sample Request: ' . $product->name . ' (SKU: ' . $product->sku . ')'])"
                            variant="secondary"
                            title="Request evaluation samples or product specifications through Message Admin"
                        >
                            Request Sample via Message Admin
                        </x-shop::portal.button>

                        @if ($isVerifiedBuyer && $canAccessB2BQuotes)
                            <x-shop::portal.button
                                :href="route('shop.customers.account.quotes.index')"
                                variant="earth"
                                title="View approved wholesale quotations for your verified buyer account"
                            >
                                View Approved Quotations
                            </x-shop::portal.button>
                        @elseif ($verificationStatus !== 'pending')
                            <x-shop::portal.button
                                :href="route('shop.customers.account.buyer_verification.index')"
                                variant="secondary"
                                size="sm"
                                title="Submit business credentials to qualify as a Verified Wholesale Buyer"
                            >
                                Complete Buyer Verification &rarr;
                            </x-shop::portal.button>
                        @endif
                    </div>

                    <p class="pk-product__verification-note">
                        @if ($isVerifiedBuyer)
                            Your account holds <strong class="pk-text-primary">Verified Wholesale Buyer (Tier 2)</strong> status. Product inquiries, sample requests, and commercial volume coordination are handled directly via Message Admin.
                        @else
                            All wholesale pricing quotes, sample evaluations, and product questions are handled directly through <strong class="pk-text-primary">Message Admin</strong>. Formal wholesale purchasing workflows remain restricted to approved <strong class="pk-text-primary">Verified Wholesale Buyers</strong>.
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Product Overview & Description -->
        @if (filled($product->description))
            <x-shop::portal.card
                eyebrow="Commercial Profile"
                title="Product Overview & Description"
            >
                <div class="pk-product__prose">
                    {!! $product->description !!}
                </div>
            </x-shop::portal.card>
        @endif

        <!-- Verified Product Information & Specifications -->
        <x-shop::portal.card
            eyebrow="Verified Catalog Data"
            title="Product Information & Specifications"
            subtitle="Displays verified product catalog data only. Lot-specific analytical parameters and technical data are furnished inside private documentation or upon inquiry."
        >
            <dl class="pk-spec-list">
                <div class="pk-spec-list__row">
                    <dt>Product Name</dt>
                    <dd>{{ $product->name }}</dd>
                </div>

                @foreach ($specs as $spec)
                    <div class="pk-spec-list__row">
                        <dt>{{ $spec['label'] }}</dt>
                        <dd>{{ $spec['value'] }}</dd>
                    </div>
                @endforeach

                <div class="pk-spec-list__row">
                    <dt>Pricing Mode</dt>
                    <dd>{{ $pricing['mode_label'] }}</dd>
                </div>

                <div class="pk-spec-list__row">
                    <dt>Commercial Channel</dt>
                    <dd>Wholesale B2B Members Portal</dd>
                </div>
            </dl>
        </x-shop::portal.card>

        <!-- Grouped Documentation (COA / TDS / Compliance) -->
        <x-shop::portal.card
            eyebrow="Quality Custody"
            title="Product Documentation"
            subtitle="Private member access to published technical specifications, Certificates of Analysis (COA), and compliance files."
        >
            @if ($hasAnyDocuments)
                <div class="pk-stack-md">
                    @foreach ($groupedDocuments as $group)
                        @if ($group['documents']->isNotEmpty())
                            <div class="pk-stack-xs">
                                <span class="pk-eyebrow">{{ $group['title'] }}</span>

                                <div class="pk-stack-xs">
                                    @foreach ($group['documents'] as $doc)
                                        <div class="pk-doc-card">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h4 class="pk-doc-card__title">{{ $doc->title }}</h4>
                                                    <x-shop::portal.badge variant="neutral" size="sm">{{ $doc->type_label }}</x-shop::portal.badge>
                                                    @if (filled($doc->version))
                                                        <x-shop::portal.badge variant="verified" size="sm">{{ $doc->version }}</x-shop::portal.badge>
                                                    @endif
                                                </div>

                                                <div class="pk-doc-card__meta">
                                                    <span>
                                                        <strong>Format:</strong>
                                                        {{ strtoupper($doc->mime_type === 'application/pdf' ? 'PDF' : ($doc->mime_type === 'image/webp' ? 'WEBP' : ($doc->mime_type === 'image/png' ? 'PNG' : 'JPG'))) }}
                                                    </span>
                                                    <span><strong>Size:</strong> {{ $doc->formatted_file_size }}</span>
                                                    @if ($doc->issue_date)
                                                        <span><strong>Issued:</strong> {{ $doc->issue_date->format('M d, Y') }}</span>
                                                    @endif
                                                    @if ($doc->expiry_date)
                                                        <span><strong>Expires:</strong> {{ $doc->expiry_date->format('M d, Y') }}</span>
                                                    @endif
                                                </div>

                                                @if (filled($doc->description))
                                                    <p class="pk-help-text" style="margin-top: 6px;">{{ $doc->description }}</p>
                                                @endif
                                            </div>

                                            <div class="pk-doc-card__actions">
                                                <x-shop::portal.button
                                                    :href="route('shop.customers.account.product_documents.preview', [$product->id, $doc->id])"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    variant="secondary"
                                                    size="sm"
                                                >
                                                    Preview
                                                </x-shop::portal.button>

                                                <x-shop::portal.button
                                                    :href="route('shop.customers.account.product_documents.download', [$product->id, $doc->id])"
                                                    variant="primary"
                                                    size="sm"
                                                >
                                                    Download
                                                </x-shop::portal.button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <x-shop::portal.empty-state
                    title="Documentation Pending Publication"
                    description="No technical documents or Certificates of Analysis (COA) are currently published for this product. Request lot-specific records directly from our commercial team via Message Admin."
                >
                    <x-slot:actions>
                        <x-shop::portal.button
                            :href="route('shop.customers.account.conversations.create', ['category' => \App\Models\MemberConversation::CATEGORY_PRODUCT_DOCUMENTATION, 'subject' => 'Documentation Request: ' . $product->name . ' (SKU: ' . $product->sku . ')'])"
                            variant="primary"
                            size="sm"
                        >
                            Request Document via Message Admin &rarr;
                        </x-shop::portal.button>
                    </x-slot>
                </x-shop::portal.empty-state>
            @endif
        </x-shop::portal.card>

        <!-- Related Product Navigation -->
        @if (! empty($relatedProducts))
            <x-shop::portal.card
                eyebrow="Wholesale Catalog"
                title="Other Wholesale Products"
                subtitle="Explore additional botanical offerings available in the PurNatureK private catalog."
            >
                <x-slot:actions>
                    <x-shop::portal.button :href="route('shop.catalog.index')" variant="secondary" size="sm">
                        &larr; All Catalog Products
                    </x-shop::portal.button>
                </x-slot>

                <div class="pk-grid pk-grid--related">
                    @foreach ($relatedProducts as $relatedCard)
                        <x-shop::portal.product-card :card="$relatedCard" />
                    @endforeach
                </div>
            </x-shop::portal.card>
        @endif
    </section>

    @push('scripts')
        <script>
            (function () {
                // Interactive Gallery Thumbnail Switcher
                var mainImg = document.getElementById('pk-pdp-main-img');
                var thumbs = document.querySelectorAll('[data-pk-thumb]');
                if (mainImg && thumbs.length) {
                    thumbs.forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            var nextSrc = btn.getAttribute('data-pk-thumb');
                            if (!nextSrc) return;
                            mainImg.src = nextSrc;
                            thumbs.forEach(function (other) {
                                other.classList.remove('is-active');
                                other.removeAttribute('aria-current');
                            });
                            btn.classList.add('is-active');
                            btn.setAttribute('aria-current', 'true');
                        });
                    });
                }

                // Customer-scoped Recently Viewed persistence
                @if ($customer)
                    @php
                        $recentPayload = [
                            'id'    => (int) $product->id,
                            'sku'   => (string) $product->sku,
                            'name'  => (string) $product->name,
                            'url'   => route('shop.catalog.show', $product->url_key, false),
                            'image' => $recentThumb ? parse_url($recentThumb, PHP_URL_PATH) : null,
                        ];
                    @endphp
                    try {
                        var storageKey = 'pk_recent_{{ (int) $customer->id }}';
                        var currentItem = {{ \Illuminate\Support\Js::from($recentPayload) }};

                        var existing = JSON.parse(localStorage.getItem(storageKey) || '[]');
                        if (!Array.isArray(existing)) existing = [];
                        existing = existing.filter(function (item) {
                            return item && item.id !== currentItem.id;
                        });
                        existing.unshift(currentItem);
                        localStorage.setItem(storageKey, JSON.stringify(existing.slice(0, 6)));
                    } catch (e) {}
                @endif
            })();
        </script>
    @endpush
</x-shop::layouts.account>

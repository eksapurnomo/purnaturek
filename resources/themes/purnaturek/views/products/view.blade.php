{{--
    PurNatureK Members Portal — Private Product Detail View (PDP)
    resources/themes/purnaturek/views/products/view.blade.php
--}}
@inject('productViewHelper', 'Webkul\Product\Helpers\View')

@php
    \App\Support\WholesalePricingManager::ensureAttributesProvisioned();

    $pricing = \App\Support\WholesalePricingManager::formatForMember($product);
    $baseImage = product_image()->getProductBaseImage($product);
    $galleryImages = product_image()->getGalleryImages($product);

    $customer = auth()->guard('customer')->user();
    $isVerifiedBuyer = \App\Support\BuyerVerificationManager::isVerifiedBuyer($customer);
    $verificationStatus = $customer ? \App\Support\BuyerVerificationManager::getVerificationStatus($customer) : null;
    $canAccessB2BQuotes = $customer
        && function_exists('customer_bouncer')
        && customer_bouncer()->hasPermission('account.quotes');

    $specs = \App\Support\ProductDocumentationManager::getProductSpecifications($product);
    $groupedDocuments = \App\Support\ProductDocumentationManager::getGroupedProductDocuments($product->id);
    $hasAnyDocuments = collect($groupedDocuments)->pluck('documents')->flatten(1)->isNotEmpty();

    $mainImageSrc = $baseImage['large_image_url'] ?? ($baseImage['medium_image_url'] ?? null);
    $recentThumb = $baseImage['small_image_url'] ?? ($baseImage['medium_image_url'] ?? null);
@endphp

<x-shop::layouts.account>
    <x-slot:title>
        {{ $product->name }} — PurNatureK Members Portal
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <section class="pk-product pk-stack-md" v-pre>
        <!-- Breadcrumb Navigation -->
        <nav class="pk-product__breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('shop.customers.account.index') }}#catalog" class="pk-product__back">
                &larr; Wholesale Product Catalog
            </a>
            <span aria-hidden="true">/</span>
            <span class="pk-mono text-xs">{{ $product->sku }}</span>
        </nav>

        <!-- Editorial Product Hero -->
        <div class="pk-product__hero">
            <!-- Gallery Column -->
            <div>
                <div class="pk-product__image-main">
                    @if ($mainImageSrc)
                        <img
                            id="pk-pdp-main-img"
                            src="{{ $mainImageSrc }}"
                            alt="{{ $product->name }}"
                        >
                    @else
                        <div class="pk-card__placeholder" style="min-height: 280px;">
                            <span class="pk-eyebrow">PUR NATUREK</span>
                            <span class="pk-serif" style="font-size: 22px; font-weight: 600; color: var(--pk-primary); margin-top: 8px;">
                                {{ $product->name }}
                            </span>
                            <span class="pk-mono text-xs pk-text-muted" style="margin-top: 6px;">SKU: {{ $product->sku }}</span>
                            <span class="text-[11px] pk-text-muted" style="margin-top: 10px;">Specification Photography Pending</span>
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

            <!-- Commercial Pricing & Action Summary -->
            <div class="pk-product__summary">
                <div>
                    <div class="pk-product__badges">
                        <x-shop::portal.badge variant="neutral">SKU: {{ $product->sku }}</x-shop::portal.badge>

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

                <!-- Model B Indicative Pricing Box -->
                <div class="pk-pricing-box">
                    <div class="pk-pricing-box__header">
                        <span class="pk-pricing-box__label">Indicative Wholesale Price</span>

                        <div class="pk-pricing-box__value">
                            {{ $pricing['price_display'] }}
                        </div>
                    </div>

                    <dl class="pk-pricing-box__specs">
                        <div class="pk-pricing-box__spec">
                            <dt>Minimum Order (MOQ)</dt>
                            <dd>{{ $pricing['moq_display'] ?? 'Contact for MOQ' }}</dd>
                        </div>

                        <div class="pk-pricing-box__spec">
                            <dt>Packaging Unit</dt>
                            <dd>{{ $pricing['unit'] ?? 'Per specification' }}</dd>
                        </div>

                        <div class="pk-pricing-box__spec">
                            <dt>Commercial Supply</dt>
                            <dd>{{ $pricing['supply_availability'] ?? 'Contact for Availability' }}</dd>
                        </div>
                    </dl>

                    <p class="pk-pricing-box__disclaimer">
                        {{ $pricing['disclaimer'] }}
                    </p>
                </div>

                <!-- Inquiry & Purchasing Actions Panel -->
                <div class="pk-product__actions-panel">
                    <h2 class="pk-product__actions-title">Wholesale Inquiry &amp; Purchasing</h2>

                    <div class="pk-product__actions">
                        @if ($isVerifiedBuyer && $canAccessB2BQuotes)
                            <x-shop::portal.button :href="route('shop.customers.account.quotes.index')" variant="primary">
                                Buy Wholesale &rarr;
                            </x-shop::portal.button>
                        @elseif ($verificationStatus === 'pending')
                            <x-shop::portal.button
                                :href="route('shop.customers.account.buyer_verification.index')"
                                variant="earth"
                                title="Your buyer verification application is currently under commercial review"
                            >
                                Verification Pending Review
                            </x-shop::portal.button>
                        @else
                            <x-shop::portal.button
                                :href="route('shop.customers.account.buyer_verification.index')"
                                variant="primary"
                                title="Submit business credentials to unlock wholesale purchasing"
                            >
                                Verify Account to Buy Wholesale
                            </x-shop::portal.button>
                        @endif

                        <span
                            class="pk-btn pk-btn--secondary"
                            aria-disabled="true"
                            title="Long-term supply agreements require formal agreement execution"
                        >
                            Buy with Long-Term Contract
                        </span>

                        <x-shop::portal.button
                            :href="route('shop.customers.account.conversations.create', ['category' => 'product_inquiry', 'subject' => 'Inquiry: ' . $product->name . ' (SKU: ' . $product->sku . ')'])"
                            variant="secondary"
                            title="Directly message PurNatureK administrators regarding this product"
                        >
                            Message Admin
                        </x-shop::portal.button>
                    </div>

                    <p class="pk-product__verification-note">
                        Official wholesale transactions and Master Supply Agreements are restricted to
                        <strong class="pk-text-primary">Verified Wholesale Buyers</strong> and remain subject to formal quotation, specification review, and mutual agreement.
                    </p>
                </div>
            </div>
        </div>

        <!-- Product Description & Overview -->
        @if (filled($product->description))
            <x-shop::portal.card
                eyebrow="Botanical Profile"
                title="Product Description & Overview"
            >
                <div class="pk-product__prose">
                    {!! $product->description !!}
                </div>
            </x-shop::portal.card>
        @endif

        <!-- Technical Specifications -->
        @if ($specs->isNotEmpty())
            <x-shop::portal.card
                eyebrow="Technical Parameters"
                title="Product Specifications"
            >
                <dl class="pk-spec-list">
                    @foreach ($specs as $spec)
                        <div class="pk-spec-list__row">
                            <dt>{{ $spec['label'] }}</dt>
                            <dd>{{ $spec['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-shop::portal.card>
        @endif

        <!-- Grouped Documentation (COA / TDS / Compliance) -->
        <x-shop::portal.card
            eyebrow="Quality Custody"
            title="Product Documentation & Quality Certificates"
            subtitle="Verified technical specifications, batch Certificates of Analysis (COA), Technical Data Sheets (TDS), and quality documentation."
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
                    description="No technical documents or batch Certificates of Analysis (COA) are currently published for this SKU. Contact our commercial desk for lot-specific files."
                >
                    <x-slot:actions>
                        <x-shop::portal.button
                            :href="route('shop.customers.account.conversations.create', ['category' => 'product_inquiry', 'subject' => 'Document Request: ' . $product->name . ' (SKU: ' . $product->sku . ')'])"
                            variant="primary"
                            size="sm"
                        >
                            Request Document via Message Admin &rarr;
                        </x-shop::portal.button>
                    </x-slot>
                </x-shop::portal.empty-state>
            @endif
        </x-shop::portal.card>
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
                            'url'   => route('shop.product_or_category.index', $product->url_key, false),
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

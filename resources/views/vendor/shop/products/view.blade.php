{{--
    PurNatureK Members Portal — Private Product Detail View
    resources/views/vendor/shop/products/view.blade.php
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
@endphp

<x-shop::layouts.account>
    <x-slot:title>
        {{ $product->name }} — PurNatureK Members Portal
    </x-slot>

    <x-shop::layouts.account.navigation />

    <section
        class="pk-main pk-product"
        v-pre
    >
        <!-- Breadcrumb / Back Navigation -->
        <nav class="pk-product__breadcrumb mb-4" aria-label="Breadcrumb">
            <a
                href="{{ route('shop.customers.account.index') }}#catalog"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-800 hover:text-emerald-950 hover:underline transition"
            >
                &larr; Back to Wholesale Product Catalog
            </a>
        </nav>

        <!-- Product Hero: Gallery + Wholesale Pricing & Terms -->
        <div class="pk-product__hero pk-card p-6 md:p-8">
            <!-- Media Column -->
            <div class="pk-product__media">
                <div class="pk-product__image-main aspect-[4/3] rounded-xl overflow-hidden border border-[#d9e6e1] bg-[#f0f7f4]">
                    @if (! empty($baseImage['large_image_url']) || ! empty($baseImage['medium_image_url']))
                        <img
                            src="{{ $baseImage['large_image_url'] ?? $baseImage['medium_image_url'] }}"
                            alt="{{ $product->name }}"
                            class="w-full h-full object-cover"
                        >
                    @else
                        <!-- Neutral Branded Botanical Placeholder -->
                        <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center bg-gradient-to-b from-[#f0f7f4] to-[#e4efe9]">
                            <div class="h-14 w-14 rounded-full bg-emerald-800/10 flex items-center justify-center mb-2.5 text-emerald-800">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-wider text-[#0f4a3e]">PurNatureK Botanical Extract</span>
                            <span class="text-[11px] font-mono text-[#5a6f68] mt-0.5">SKU: {{ $product->sku }}</span>
                            <span class="text-[10px] text-zinc-400 mt-2">Specification Photography Pending</span>
                        </div>
                    @endif
                </div>

                @if (count($galleryImages) > 1)
                    <div class="pk-product__thumbs flex flex-wrap gap-2 mt-3">
                        @foreach ($galleryImages as $img)
                            @if (! empty($img['small_image_url']))
                                <a
                                    href="{{ $img['large_image_url'] ?? $img['medium_image_url'] }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="h-16 w-16 rounded-lg border border-[#d9e6e1] overflow-hidden hover:border-emerald-600 transition"
                                >
                                    <img
                                        src="{{ $img['small_image_url'] }}"
                                        alt="{{ $product->name }}"
                                        loading="lazy"
                                        class="h-full w-full object-cover"
                                    >
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Wholesale Pricing & Commercial Summary Column -->
            <div class="pk-product__summary flex flex-col gap-5">
                <div class="pk-product__header">
                    <div class="pk-product__badges flex flex-wrap items-center gap-2 mb-2">
                        <span class="pk-card__sku text-xs font-mono font-semibold px-2 py-0.5 rounded bg-zinc-100 text-zinc-700 border border-zinc-200">
                            SKU: {{ $product->sku }}
                        </span>

                        @if (! empty($pricing['supply_availability']))
                            <span class="pk-badge pk-badge--verified text-xs px-2.5 py-0.5">
                                {{ $pricing['supply_availability'] }}
                            </span>
                        @endif
                    </div>

                    <h1 class="pk-product__title text-2xl md:text-3xl font-extrabold text-[#0f4a3e] tracking-tight">
                        {{ $product->name }}
                    </h1>

                    @if (filled($product->short_description))
                        <div class="pk-product__short-desc text-sm text-[#5a6f68] mt-2 leading-relaxed">
                            {!! $product->short_description !!}
                        </div>
                    @endif
                </div>

                <!-- B2B Wholesale Pricing Box -->
                <div class="pk-pricing-box rounded-xl bg-[#f0f7f4] border border-[#d9e6e1] p-5 shadow-sm">
                    <div class="pk-pricing-box__header border-b border-[#d9e6e1] pb-3 mb-3">
                        <span class="pk-pricing-box__label text-xs font-bold uppercase tracking-wider text-emerald-800">
                            Indicative Wholesale Price
                        </span>

                        @if ($pricing['is_range'])
                            <div class="pk-pricing-box__value text-2xl font-extrabold text-[#0f4a3e] mt-1">
                                {{ $pricing['price_display'] }}
                            </div>
                        @else
                            <div class="pk-pricing-box__value text-xl font-bold text-emerald-800 mt-1">
                                {{ $pricing['price_display'] }}
                            </div>
                        @endif
                    </div>

                    <dl class="pk-pricing-box__specs grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <div class="pk-pricing-box__spec bg-white/70 rounded-lg p-2.5 border border-[#d9e6e1]/60">
                            <dt class="text-zinc-500 font-medium">Minimum Order (MOQ)</dt>
                            <dd class="text-zinc-900 font-bold mt-0.5">{{ $pricing['moq_display'] ?? 'Contact for MOQ' }}</dd>
                        </div>

                        <div class="pk-pricing-box__spec bg-white/70 rounded-lg p-2.5 border border-[#d9e6e1]/60">
                            <dt class="text-zinc-500 font-medium">Packaging Unit</dt>
                            <dd class="text-zinc-900 font-bold mt-0.5">{{ $pricing['unit'] ?? 'Per specification' }}</dd>
                        </div>

                        <div class="pk-pricing-box__spec bg-white/70 rounded-lg p-2.5 border border-[#d9e6e1]/60">
                            <dt class="text-zinc-500 font-medium">Commercial Supply</dt>
                            <dd class="text-zinc-900 font-bold mt-0.5">{{ $pricing['supply_availability'] ?? 'Contact for Availability' }}</dd>
                        </div>
                    </dl>

                    <p class="pk-pricing-box__disclaimer text-[11px] text-[#5a6f68] mt-3 pt-3 border-t border-[#d9e6e1]/70 leading-relaxed italic">
                        {{ $pricing['disclaimer'] }}
                    </p>
                </div>

                <!-- Wholesale Purchasing & Inquiry Actions Panel -->
                <div class="pk-product__actions-panel border-t border-[#d9e6e1] pt-5">
                    <h2 class="pk-product__actions-title text-xs font-bold uppercase tracking-wider text-zinc-500 mb-3">
                        Wholesale Inquiry &amp; Purchasing
                    </h2>

                    <div class="pk-product__actions flex flex-wrap items-center gap-3">
                        @if ($isVerifiedBuyer && $canAccessB2BQuotes)
                            <a
                                href="{{ route('shop.customers.account.quotes.index') }}"
                                class="pk-btn pk-btn--primary px-6 py-2.5 text-xs font-bold shadow-sm"
                            >
                                Buy Wholesale &rarr;
                            </a>
                        @elseif ($verificationStatus === 'pending')
                            <a
                                href="{{ route('shop.customers.account.buyer_verification.index') }}"
                                class="pk-btn bg-amber-500 text-white hover:bg-amber-600 px-5 py-2.5 text-xs font-semibold"
                                title="Your buyer verification application is currently under commercial review"
                            >
                                Verification Pending Review
                            </a>
                        @else
                            <a
                                href="{{ route('shop.customers.account.buyer_verification.index') }}"
                                class="pk-btn pk-btn--primary px-5 py-2.5 text-xs font-bold shadow-sm"
                                title="Submit business credentials to unlock wholesale purchasing"
                            >
                                Verify Account to Buy Wholesale
                            </a>
                        @endif

                        <span
                            class="pk-btn pk-btn--ghost text-zinc-400 border-zinc-200 cursor-not-allowed py-2.5 px-4 text-xs font-medium"
                            aria-disabled="true"
                            title="Long-term supply agreements require formal agreement execution"
                        >
                            Buy with Long-Term Contract
                        </span>

                        <a
                            href="{{ route('shop.customers.account.conversations.create', ['category' => 'product_inquiry', 'subject' => 'Inquiry: ' . $product->name . ' (SKU: ' . $product->sku . ')']) }}"
                            class="pk-btn pk-btn--ghost py-2.5 px-4 text-xs font-semibold"
                            title="Directly message PurNatureK administrators regarding this product"
                        >
                            Message Admin
                        </a>
                    </div>

                    <p class="pk-product__verification-note text-xs text-[#5a6f68] mt-3 leading-relaxed">
                        Official wholesale transactions and Master Supply Agreements are restricted to
                        <strong class="text-[#0f4a3e]">Verified Wholesale Buyers</strong> and remain subject to formal quotation, specification review, and mutual agreement.
                    </p>
                </div>
            </div>
        </div>

        <!-- Product Description & Overview -->
        @if (filled($product->description))
            <div class="pk-product__section pk-card p-6 md:p-8 mt-6">
                <h2 class="pk-product__section-title text-lg font-bold text-[#0f4a3e] border-b border-[#d9e6e1] pb-3 mb-4">
                    Product Description &amp; Overview
                </h2>

                <div class="pk-product__prose text-sm text-[#1e2d29] leading-relaxed">
                    {!! $product->description !!}
                </div>
            </div>
        @endif

        <!-- Product Specifications Table (Module 2.5) -->
        @if ($specs->isNotEmpty())
            <div class="pk-product__section pk-card p-6 md:p-8 mt-6">
                <h2 class="pk-product__section-title text-lg font-bold text-[#0f4a3e] border-b border-[#d9e6e1] pb-3 mb-4">
                    Product Specifications
                </h2>

                <dl class="pk-spec-list grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                    @foreach ($specs as $spec)
                        <div class="pk-spec-list__row flex items-baseline justify-between border-b border-[#d9e6e1]/60 py-2">
                            <dt class="text-zinc-500 font-medium">{{ $spec['label'] }}</dt>
                            <dd class="text-[#0f4a3e] font-semibold font-mono">{{ $spec['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif

        <!-- Product Documentation & Quality Certificates (Module 2.5) -->
        <div class="pk-product__section pk-card p-6 md:p-8 mt-6">
            <div class="border-b border-[#d9e6e1] pb-4 mb-6">
                <h2 class="pk-product__section-title text-lg font-bold text-[#0f4a3e]">
                    Product Documentation &amp; Quality Certificates
                </h2>
                <p class="text-xs text-[#5a6f68] mt-1">
                    Verified technical specifications, batch Certificates of Analysis (COA), Technical Data Sheets (TDS), and quality documentation.
                </p>
            </div>

            @if ($hasAnyDocuments)
                <div class="pk-doc-groups space-y-6">
                    @foreach ($groupedDocuments as $groupKey => $group)
                        @if ($group['documents']->isNotEmpty())
                            <div class="pk-doc-group">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-emerald-800 mb-3 flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                                    {{ $group['title'] }}
                                </h3>

                                <div class="pk-doc-list space-y-3">
                                    @foreach ($group['documents'] as $doc)
                                        <div class="pk-doc-card rounded-xl border border-[#d9e6e1] bg-[#f0f7f4]/40 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                            <div class="pk-doc-card__main">
                                                <div class="pk-doc-card__header flex flex-wrap items-center gap-2">
                                                    <h4 class="pk-doc-card__title text-sm font-bold text-[#0f4a3e]">{{ $doc->title }}</h4>
                                                    <span class="pk-badge bg-zinc-100 text-zinc-700 border border-zinc-200 text-[10px] px-2 py-0.5">
                                                        {{ $doc->type_label }}
                                                    </span>
                                                    @if (filled($doc->version))
                                                        <span class="pk-badge bg-emerald-100 text-emerald-800 text-[10px] px-2 py-0.5 font-mono">
                                                            {{ $doc->version }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="pk-doc-card__meta flex flex-wrap items-center gap-3 text-xs text-[#5a6f68] mt-1.5">
                                                    <span><strong>Format:</strong> {{ strtoupper($doc->mime_type === 'application/pdf' ? 'PDF' : ($doc->mime_type === 'image/webp' ? 'WEBP' : ($doc->mime_type === 'image/png' ? 'PNG' : 'JPG'))) }}</span>
                                                    <span><strong>Size:</strong> {{ $doc->formatted_file_size }}</span>
                                                    @if ($doc->issue_date)
                                                        <span><strong>Issued:</strong> {{ $doc->issue_date->format('M d, Y') }}</span>
                                                    @endif
                                                    @if ($doc->expiry_date)
                                                        <span><strong>Expires:</strong> {{ $doc->expiry_date->format('M d, Y') }}</span>
                                                    @endif
                                                </div>

                                                @if (filled($doc->description))
                                                    <p class="pk-doc-card__desc text-xs text-[#5a6f68] mt-2">{{ $doc->description }}</p>
                                                @endif
                                            </div>

                                            <div class="pk-doc-card__actions flex items-center gap-2 shrink-0">
                                                <a
                                                    href="{{ route('shop.customers.account.product_documents.preview', [$product->id, $doc->id]) }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="pk-btn pk-btn--ghost py-1.5 px-3 text-xs font-semibold"
                                                    title="Preview document in browser"
                                                >
                                                    Preview
                                                </a>

                                                <a
                                                    href="{{ route('shop.customers.account.product_documents.download', [$product->id, $doc->id]) }}"
                                                    class="pk-btn pk-btn--primary py-1.5 px-3 text-xs font-semibold"
                                                    title="Download document file securely"
                                                >
                                                    Download
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <!-- Clear Awaiting Document State -->
                <div class="pk-doc-empty rounded-xl bg-[#f0f7f4] border border-[#d9e6e1] p-6 text-center">
                    <p class="text-sm font-medium text-[#0f4a3e]">
                        No technical documents or batch certificates are currently published for this product.
                    </p>
                    <p class="text-xs text-[#5a6f68] mt-1.5 max-w-lg mx-auto leading-relaxed">
                        For customized technical specifications, lot-specific Certificates of Analysis (COA), or Technical Data Sheets (TDS), please contact your PurNatureK wholesale representative.
                    </p>
                    <div class="mt-4">
                        <a
                            href="{{ route('shop.customers.account.conversations.create', ['category' => 'product_inquiry', 'subject' => 'Document Request: ' . $product->name . ' (SKU: ' . $product->sku . ')']) }}"
                            class="pk-btn pk-btn--primary py-2 px-4 text-xs font-bold"
                        >
                            Request Document via Message Admin &rarr;
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-shop::layouts.account>

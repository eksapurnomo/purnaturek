@props([
    'title'           => 'PurNatureK Members Portal',
    'metaDescription' => 'PurNatureK Wholesale Members Portal — Commercial Botanical Access',
    'eyebrow'         => 'Commercial Portal Access',
    'heading',
    'subtitle'        => null,
    'visualEyebrow'   => 'PurNatureK B2B Sourcing',
    'visualHeading'   => 'Premium Kratom. Wholesale & Bulk Supply.',
    'visualLead'      => 'Your US-Based Wholesale Partner for Mitragyna speciosa Powder & Extract. Built for commercial formulators, botanical brands, and wholesale distributors.',
])

@push('meta')
    <meta name="description" content="{{ $metaDescription }}" />
@endPush

<x-shop::layouts
    :has-header="false"
    :has-feature="false"
    :has-footer="true"
>
    <x-slot:title>
        {{ $title }}
    </x-slot>

    <div class="pk-auth-shell">
        <header class="pk-auth-topbar">
            <div class="pk-auth-topbar__inner">
                <a href="{{ route('shop.home.index') }}" class="pk-brand" aria-label="PurNatureK Members Portal">
                    <img
                        src="{{ asset('themes/shop/purnaturek/images/logo-purnaturek-transparent.webp') }}"
                        alt="PUR NATUREK"
                        class="pk-brand__logo"
                        width="160"
                        height="39"
                    />
                    <span class="pk-brand__divider" aria-hidden="true"></span>
                    <span class="pk-brand__tag">Members Portal</span>
                </a>

                <div class="pk-header__actions">
                    @isset($topbarRight)
                        {{ $topbarRight }}
                    @else
                        <a
                            href="https://purnaturekbulk.com/"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="pk-header__link"
                        >
                            Corporate Site &rarr;
                        </a>
                    @endisset
                </div>
            </div>
        </header>

        <div class="pk-auth-grid">
            <div class="pk-auth-form-col">
                <div class="pk-auth-form-card">
                    <div>
                        @if ($eyebrow)
                            <span class="pk-eyebrow">{{ $eyebrow }}</span>
                        @endif

                        <h1 class="pk-auth-title">{{ $heading }}</h1>

                        @if ($subtitle)
                            <p class="pk-auth-subtitle">{{ $subtitle }}</p>
                        @endif

                        @isset($headingExtra)
                            {{ $headingExtra }}
                        @endisset
                    </div>

                    <div class="pk-auth-divider"></div>

                    {{ $slot }}
                </div>
            </div>

            <aside class="pk-auth-visual-col" aria-label="PurNatureK Commercial Standards">
                <div class="pk-auth-visual-inner">
                    <div>
                        <span class="pk-eyebrow pk-eyebrow--light">{{ $visualEyebrow }}</span>
                        <h2 class="pk-auth-visual-heading" style="margin-top: 12px;">
                            {{ $visualHeading }}
                        </h2>
                        <p class="pk-auth-visual-lead" style="margin-top: 12px;">
                            {{ $visualLead }}
                        </p>
                    </div>

                    @isset($visualBody)
                        {{ $visualBody }}
                    @else
                        <div class="pk-auth-feature-list">
                            <div class="pk-auth-feature-item">
                                <p class="pk-auth-feature-item__title">Wholesale Product &amp; Spec Access</p>
                                <p class="pk-auth-feature-item__text">
                                    Standardized powder and extract grades, minimum order quantities (MOQ), and commercial packaging terms inside your member workspace.
                                </p>
                            </div>

                            <div class="pk-auth-feature-item">
                                <p class="pk-auth-feature-item__title">Quality &amp; Batch Documentation</p>
                                <p class="pk-auth-feature-item__text">
                                    Lot-specific Certificates of Analysis (COA), Technical Data Sheets (TDS), and compliance records under private member custody.
                                </p>
                            </div>

                            <div class="pk-auth-feature-item">
                                <p class="pk-auth-feature-item__title">Direct Chicago Commercial Desk</p>
                                <p class="pk-auth-feature-item__text">
                                    Direct member messaging with PurNatureK administrators for quotations, volume scheduling, and contract execution.
                                </p>
                            </div>
                        </div>
                    @endisset
                </div>

                <div class="pk-auth-visual-footer">
                    <span>Purnature Khatulistiwa LLC &bull; Chicago, IL</span>
                    <span>wholesale@purnaturek.com &bull; 21+ Commercial B2B</span>
                </div>
            </aside>
        </div>

        @isset($modals)
            {{ $modals }}
        @endisset
    </div>
</x-shop::layouts>

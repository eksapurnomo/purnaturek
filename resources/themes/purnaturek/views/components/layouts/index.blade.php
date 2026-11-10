@props([
    'hasHeader'  => true,
    'hasFeature' => false,
    'hasFooter'  => true,
])

@php
    $themeCssPath = public_path('themes/shop/purnaturek/css/theme.css');
    $themeCssVersion = is_file($themeCssPath) ? filemtime($themeCssPath) : '1';
@endphp

<!DOCTYPE html>
<html
    lang="{{ app()->getLocale() }}"
    dir="{{ core()->getCurrentLocale()->direction }}"
>
    <head>
        {!! view_render_event('bagisto.shop.layout.head.before') !!}

        <title>{{ $title ?? 'PurNatureK Members Portal' }}</title>

        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="content-language" content="{{ app()->getLocale() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="base-url" content="{{ url()->to('/') }}">
        <meta name="currency" content="{{ core()->getCurrentCurrency()->toJson() }}">
        <meta name="generator" content="PurNatureK Wholesale Members Platform">

        @stack('meta')

        <link
            rel="icon"
            sizes="16x16"
            href="{{ core()->getCurrentChannel()->favicon_url ?? bagisto_asset('images/favicon.ico') }}"
        />

        <!-- Web Fonts (Cormorant Garamond, Plus Jakarta Sans, JetBrains Mono) -->
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap"
        />

        <!-- Bagisto Precompiled Baseline (Vue 3, VeeValidate, Axios) -->
        @bagistoVite(['src/Resources/assets/css/app.css', 'src/Resources/assets/js/app.js'])

        <!-- PurNatureK Modern Botanical Trade Design System -->
        <link
            rel="stylesheet"
            href="{{ asset('themes/shop/purnaturek/css/theme.css') }}?v={{ $themeCssVersion }}"
        >

        @stack('styles')

        @if ($customCss = core()->getConfigData('general.content.custom_scripts.custom_css'))
            <style>{!! $customCss !!}</style>
        @endif

        {!! view_render_event('bagisto.shop.layout.head.after') !!}
    </head>

    <body style="background-color: #F7F4EC; color: #132A1F;" class="min-h-screen flex flex-col">
        {!! view_render_event('bagisto.shop.layout.body.before') !!}

        <a href="#main" class="pk-skip-link">
            Skip to main content
        </a>

        <div id="app" class="flex flex-col flex-1">
            <!-- Flash Notification Messages -->
            <x-shop::flash-group />

            <!-- Confirm Modal -->
            <x-shop::modal.confirm />

            <!-- Portal Header -->
            @if ($hasHeader)
                <x-shop::portal.header />
            @endif

            <!-- Main Content Slot -->
            <main id="main" class="flex-1">
                {{ $slot }}
            </main>

            <!-- Unified PurNatureK Wholesale Footer -->
            @if ($hasFooter)
                <footer class="pk-footer">
                    <div class="pk-footer__inner">
                        <div class="pk-footer__grid">
                            <div>
                                <a href="{{ route('shop.home.index') }}" aria-label="PurNatureK Members Portal" style="display:inline-block;">
                                    <img
                                        src="{{ asset('themes/shop/purnaturek/images/logo-purnaturek-on-dark.webp') }}"
                                        alt="PUR NATUREK"
                                        class="pk-footer__logo"
                                        width="180"
                                        height="44"
                                        loading="lazy"
                                    />
                                </a>
                                <p class="pk-footer__tagline">
                                    Your US-Based Wholesale Partner for Mitragyna speciosa Powder &amp; Extract. Private member portal for vetted commercial accounts, bulk procurement, and compliance documentation.
                                </p>
                            </div>

                            <div>
                                <p class="pk-footer__heading">Commercial Desk</p>
                                <ul class="pk-footer__list">
                                    <li><strong>Purnature Khatulistiwa LLC</strong></li>
                                    <li>505 N Lake Shore Dr, Suite 3404<br>Chicago, IL 60611, USA</li>
                                    <li>
                                        <a href="tel:+17734256646" class="pk-footer__link">+1 (773) 425-6646</a>
                                    </li>
                                    <li>
                                        <a href="mailto:wholesale@purnaturek.com" class="pk-footer__link">wholesale@purnaturek.com</a>
                                    </li>
                                </ul>
                            </div>

                            <div>
                                <p class="pk-footer__heading">Portal &amp; Corporate</p>
                                <ul class="pk-footer__list">
                                    @auth('customer')
                                        <li>
                                            <a href="{{ route('shop.customers.account.index') }}" class="pk-footer__link">Member Workspace</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('shop.customers.account.buyer_verification.index') }}" class="pk-footer__link">Buyer Verification</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('shop.customers.account.conversations.index') }}" class="pk-footer__link">Message Admin</a>
                                        </li>
                                    @else
                                        <li>
                                            <a href="{{ route('shop.customer.session.index') }}" class="pk-footer__link">Member Sign In</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('shop.customers.register.index') }}" class="pk-footer__link">Apply for Wholesale</a>
                                        </li>
                                    @endauth
                                    <li>
                                        <a href="https://purnaturekbulk.com/" target="_blank" rel="noopener noreferrer" class="pk-footer__link">
                                            Corporate Website (purnaturekbulk.com) &rarr;
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        @php
                            $configuredCopyright = trim((string) core()->getConfigData('general.content.footer.copyright_content'));
                        @endphp

                        <div class="pk-footer__bottom">
                            <span>
                                @if ($configuredCopyright !== '')
                                    {!! clean_content($configuredCopyright) !!}
                                @else
                                    &copy; {{ date('Y') }} Purnature Khatulistiwa LLC. All rights reserved.
                                @endif
                            </span>
                            <span>Authorized Commercial Accounts Only &bull; Age restricted. 21+ only.</span>
                        </div>
                    </div>
                </footer>
            @endif
        </div>

        @stack('scripts')

        {!! view_render_event('bagisto.shop.layout.body.after') !!}

        <!-- PurNatureK Wholesale AI Assistant Widget -->
        <script src="{{ asset('ai-assistant/widget.js') }}" async></script>
    </body>
</html>

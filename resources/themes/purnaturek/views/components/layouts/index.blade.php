@props([
    'hasHeader'  => true,
    'hasFeature' => false,
    'hasFooter'  => true,
])

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

        <!-- Bagisto Precompiled Baseline (Vue 3, VeeValidate, Axios, Tailwind utilities) -->
        @bagistoVite(['src/Resources/assets/css/app.css', 'src/Resources/assets/js/app.js'])

        <!-- PurNatureK Theme Stylesheet -->
        <link rel="stylesheet" href="{{ asset('themes/shop/purnaturek/css/theme.css') }}">

        <!-- Web Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap"
        />

        @stack('styles')

        <style>
            body {
                font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            }
            code, pre, .font-mono {
                font-family: 'JetBrains Mono', monospace;
            }
            {!! core()->getConfigData('general.content.custom_scripts.custom_css') !!}
        </style>

        {!! view_render_event('bagisto.shop.layout.head.after') !!}
    </head>

    <body class="bg-[#f7faf8] text-[#1e2d29] min-h-screen flex flex-col">
        {!! view_render_event('bagisto.shop.layout.body.before') !!}

        <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:bg-emerald-800 focus:text-white focus:px-4 focus:py-2 focus:rounded-md">
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

            <!-- B2B Wholesale Portal Footer -->
            @if ($hasFooter)
                <footer class="mt-auto border-t border-[#d9e6e1] bg-white py-8 text-xs text-[#5a6f68]">
                    <div class="max-w-[1360px] mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="font-extrabold text-sm text-[#0f4a3e]">PurNatureK</span>
                            <span class="text-[#8a9893]">|</span>
                            <span>Wholesale Members Portal</span>
                            <span class="rounded-full bg-[#fef9c3] px-2.5 py-0.5 text-[10px] font-bold text-[#854d0e] border border-[#fef08a]">
                                Authorized Commercial Accounts Only
                            </span>
                        </div>

                        <div class="flex items-center gap-6">
                            <span>&copy; {{ date('Y') }} PurNatureK Khatulistiwa Bulk & Wholesale. All rights reserved.</span>
                            <a href="https://purnaturekbulk.com" target="_blank" rel="noopener noreferrer" class="hover:text-[#0f4a3e] font-semibold underline decoration-dotted">
                                Corporate Website &rarr;
                            </a>
                        </div>
                    </div>
                </footer>
            @endif
        </div>

        @stack('scripts')

        {!! view_render_event('bagisto.shop.layout.body.after') !!}

        <!-- PurNatureK Wholesale AI Assistant Widget (Client title: "Assistant") -->
        <script src="{{ asset('ai-assistant/widget.js') }}" async></script>
    </body>
</html>


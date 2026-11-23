{{--
    PurNatureK Members Portal — Dedicated Wholesale Catalog Listing Fallback (/catalog)
    resources/views/vendor/shop/catalog/index.blade.php
--}}
<x-shop::layouts.account>
    <x-slot:title>
        Wholesale Product Catalog — PurNatureK Members Portal
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="pk-main pk-stack-md" id="catalog" v-pre>
        <div class="pk-card p-6 md:p-8">
            <h1 class="text-2xl font-extrabold text-[#0f4a3e]">Wholesale Product Catalog</h1>
            <p class="text-sm text-[#5a6f68] mt-1">Explore private commercial botanical listings.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($catalogCards as $card)
                <article class="pk-card flex flex-col overflow-hidden">
                    <a href="{{ $card['url'] }}" class="block aspect-[4/3] bg-[#f0f7f4] overflow-hidden">
                        @if (! empty($card['image']))
                            <img src="{{ $card['image'] }}" alt="{{ $card['alt'] ?? $card['name'] }}" class="w-full h-full object-cover" loading="lazy">
                        @endif
                    </a>
                    <div class="p-5 flex-1 flex flex-col gap-3">
                        <span class="text-xs font-mono text-zinc-500">SKU // {{ $card['sku'] }}</span>
                        <h2 class="text-lg font-bold text-[#0f4a3e]">
                            <a href="{{ $card['url'] }}">{{ $card['name'] }}</a>
                        </h2>
                        <div class="text-sm font-semibold text-emerald-800">{{ $card['pricing']['price_display'] ?? 'Contact for Price' }}</div>
                        @if (! empty($card['summary']))
                            <p class="text-xs text-[#5a6f68]">{{ $card['summary'] }}</p>
                        @endif
                        <a href="{{ $card['url'] }}" class="pk-btn pk-btn--secondary mt-auto text-center">View Product &rarr;</a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</x-shop::layouts.account>


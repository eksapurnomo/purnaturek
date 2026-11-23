@props([
    'card',
])

@php
    $pricing = $card['pricing'] ?? null;
@endphp

<article class="pk-card pk-card--clickable">
    <div class="pk-card__media">
        @if (! empty($card['image']))
            <img
                src="{{ $card['image'] }}"
                alt="{{ $card['alt'] ?? $card['name'] }}"
                loading="lazy"
            />
        @else
            <div class="pk-card__placeholder">
                <span class="pk-eyebrow">PUR NATUREK</span>
                <span class="text-xs font-semibold pk-text-primary" style="margin-top: 6px;">Wholesale Product</span>
                <span class="text-[11px] pk-text-muted" style="margin-top: 2px;">Image Pending</span>
            </div>
        @endif
    </div>

    <div class="pk-card__body">
        <span class="pk-card__sku">SKU // {{ $card['sku'] }}</span>

        <h2 class="pk-card__name">
            <a href="{{ $card['url'] }}" class="pk-card__title-link">
                {{ $card['name'] }}
            </a>
        </h2>

        @if ($pricing)
            <div class="pk-card__pricing-box">
                <span class="pk-card__price-label">{{ $pricing['headline_label'] ?? 'Wholesale Pricing' }}</span>

                @if (! empty($pricing['is_contact']))
                    <span class="pk-card__price-val pk-card__price-val--contact">{{ $pricing['price_display'] }}</span>
                @else
                    <span class="pk-card__price-val">{{ $pricing['price_display'] }}</span>
                @endif
            </div>

            @if (! empty($pricing['moq_display']) || ! empty($pricing['supply_availability']))
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
        @endif

        @if (! empty($card['summary']))
            <p class="pk-card__summary">{{ $card['summary'] }}</p>
        @endif

        <div class="pk-card__cta">
            <span class="pk-btn pk-btn--secondary pk-btn--block" aria-hidden="true">
                View Product &rarr;
            </span>
        </div>
    </div>
</article>


@props([
    'title'    => null,
    'subtitle' => null,
    'eyebrow'  => null,
    'alt'      => false,
    'flush'    => false,
])

@php
    $classes = trim(implode(' ', array_filter([
        'pk-panel',
        $alt ? 'pk-panel--alt' : null,
        $flush ? 'pk-panel--flush' : null,
    ])));
@endphp

<section {{ $attributes->merge(['class' => $classes]) }}>
    @if ($title || $eyebrow || isset($actions))
        <div class="pk-panel__head" @if ($flush) style="padding: 20px 24px 14px; margin-bottom: 0;" @endif>
            <div>
                @if ($eyebrow)
                    <span class="pk-eyebrow">{{ $eyebrow }}</span>
                @endif

                @if ($title)
                    <h2 class="pk-panel__title" @if ($eyebrow) style="margin-top: 4px;" @endif>{{ $title }}</h2>
                @endif

                @if ($subtitle)
                    <p class="pk-panel__subtitle">{{ $subtitle }}</p>
                @endif
            </div>

            @isset($actions)
                <div class="pk-page-header__actions">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif

    {{ $slot }}
</section>


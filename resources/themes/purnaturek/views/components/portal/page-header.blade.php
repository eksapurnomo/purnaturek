@props([
    'eyebrow'  => null,
    'title',
    'subtitle' => null,
])

<header {{ $attributes->merge(['class' => 'pk-page-header']) }}>
    <div class="pk-page-header__row">
        <div>
            @if ($eyebrow)
                <span class="pk-eyebrow">{{ $eyebrow }}</span>
            @endif

            <h1 class="pk-page-header__title">{{ $title }}</h1>

            @if ($subtitle)
                <p class="pk-page-header__subtitle">{{ $subtitle }}</p>
            @endif
        </div>

        @isset($actions)
            <div class="pk-page-header__actions">
                {{ $actions }}
            </div>
        @endisset
    </div>

    @isset($footer)
        {{ $footer }}
    @endisset
</header>


@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'pk-empty-state']) }}>
    <div class="pk-empty-state__icon" aria-hidden="true">
        @isset($icon)
            {{ $icon }}
        @else
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
            </svg>
        @endisset
    </div>

    <h3 class="pk-empty-state__title">{{ $title }}</h3>

    @if ($description)
        <p class="pk-empty-state__text">{{ $description }}</p>
    @endif

    @isset($actions)
        <div class="pk-empty-state__actions">
            {{ $actions }}
        </div>
    @endisset
</div>


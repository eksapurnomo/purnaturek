@props([
    'variant'  => 'primary',
    'size'     => 'md',
    'block'    => false,
    'href'     => null,
    'type'     => 'button',
    'disabled' => false,
])

@php
    $variantClass = match ($variant) {
        'secondary', 'ghost' => 'pk-btn--secondary',
        'earth', 'accent'    => 'pk-btn--earth',
        'danger'             => 'pk-btn--danger',
        default              => 'pk-btn--primary',
    };

    $classes = trim(implode(' ', array_filter([
        'pk-btn',
        $variantClass,
        $size === 'sm' ? 'pk-btn--sm' : null,
        $block ? 'pk-btn--block' : null,
    ])));
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        {{ $attributes->merge(['class' => $classes]) }}
        @if ($disabled) aria-disabled="true" @endif
    >
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        {{ $attributes->merge(['class' => $classes]) }}
        @if ($disabled) disabled @endif
    >
        {{ $slot }}
    </button>
@endif


@props([
    'variant' => 'neutral',
    'size'    => 'md',
])

@php
    $variantClass = match ($variant) {
        'verified', 'success' => 'pk-badge--verified',
        'pending', 'warning'  => 'pk-badge--pending',
        'action', 'danger'    => 'pk-badge--action',
        'info'                => 'pk-badge--info',
        'earth'               => 'pk-badge--earth',
        'counter'             => 'pk-badge--counter',
        default               => '',
    };

    $sizeClass = $size === 'sm' ? 'pk-badge--sm' : '';
@endphp

<span {{ $attributes->merge(['class' => trim("pk-badge {$variantClass} {$sizeClass}")]) }}>
    {{ $slot }}
</span>


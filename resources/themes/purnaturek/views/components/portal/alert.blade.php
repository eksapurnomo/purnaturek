@props([
    'variant' => 'info',
    'title'   => null,
])

@php
    $variantClass = match ($variant) {
        'success' => 'pk-alert--success',
        'warning' => 'pk-alert--warning',
        'danger'  => 'pk-alert--danger',
        default   => 'pk-alert--info',
    };
@endphp

<div {{ $attributes->merge(['class' => "pk-alert {$variantClass}"]) }} role="status">
    <div class="pk-alert__body">
        @if ($title)
            <p class="pk-alert__title">{{ $title }}</p>
        @endif
        <div>{{ $slot }}</div>
    </div>

    @isset($actions)
        <div class="pk-page-header__actions">
            {{ $actions }}
        </div>
    @endisset
</div>


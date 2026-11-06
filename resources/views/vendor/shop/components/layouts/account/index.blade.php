{{--
    PurNatureK Members Portal — account area layout override
    (shop::components.layouts.account).

    Used by every customer account page (core account pages and B2B Suite pages alike): swaps the
    retail storefront header for the portal header and wraps page content in the portal shell so
    the sidebar (components/layouts/account/navigation.blade.php) and the page sit side by side.
--}}
<x-shop::layouts
    :has-header="false"
    :has-feature="false"
>
    <!-- Page Title -->
    <x-slot:title>
        {{ $title ?? '' }}
    </x-slot>

    @push('styles')
        @include('shop::components.portal.styles')
    @endpush

    <!-- Portal Header -->
    <x-shop::portal.header />

    <!-- Breadcrumbs (only when the page provides them) -->
    @hasSection('breadcrumbs')
        <div style="max-width: 1320px; margin: 0 auto; padding: 0 24px;">
            <x-shop::layouts.account.breadcrumb />
        </div>
    @endif

    <div class="pk-shell">
        {{ $slot }}
    </div>
</x-shop::layouts>

{{--
    PurNatureK Members Portal — Account Area Layout
    (shop::components.layouts.account).

    Wraps customer account pages, B2B Suite pages, and Portal Modules 2.1–2.7 in the
    PurNatureK Members Portal shell with branded header, sidebar, and container layout.
--}}
<x-shop::layouts
    :has-header="false"
    :has-feature="false"
    :has-footer="true"
>
    <!-- Page Title -->
    <x-slot:title>
        {{ $title ?? 'PurNatureK Members Workspace' }}
    </x-slot>

    <!-- Portal Header -->
    <x-shop::portal.header />

    <!-- Breadcrumbs (when page provides them) -->
    @hasSection('breadcrumbs')
        <div class="max-w-[1360px] mx-auto px-6 pt-4">
            <x-shop::layouts.account.breadcrumb />
        </div>
    @endif

    <!-- Main Workspace Shell -->
    <div class="pk-shell">
        {{ $slot }}
    </div>
</x-shop::layouts>


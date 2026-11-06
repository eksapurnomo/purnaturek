{{--
    PurNatureK Members Portal header.

    Replaces the retail storefront header on customer account pages (see
    components/layouts/account/index.blade.php). Uses the channel logo when an admin has uploaded
    one; otherwise falls back to a text wordmark — no logo asset is invented here.
--}}
@php
    $portalCustomer = auth()->guard('customer')->user();
    $portalLogo = core()->getCurrentChannel()->logo_url;
@endphp

<header class="pk-header">
    <div class="pk-header__inner">
        <a
            href="{{ route('shop.customers.account.index') }}"
            class="pk-brand"
            aria-label="PurNatureK Members Portal"
        >
            @if ($portalLogo)
                <img
                    src="{{ $portalLogo }}"
                    alt="PurNatureK"
                >
            @else
                <span class="pk-brand__name">PurNatureK</span>
            @endif

            <span class="pk-brand__tag">Members Portal</span>
        </a>

        <div class="pk-header__actions">
            @if ($portalCustomer)
                <span class="pk-header__user" title="{{ $portalCustomer->email }}">
                    {{ $portalCustomer->first_name }}
                </span>

                <a
                    href="{{ route('shop.customers.account.profile.index') }}"
                    class="pk-header__link"
                >
                    My Account
                </a>

                <!-- Logout uses the existing customer session route (DELETE) -->
                <form
                    method="POST"
                    action="{{ route('shop.customer.session.destroy') }}"
                    style="margin: 0;"
                >
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="pk-header__btn">
                        @lang('shop::app.components.layouts.header.desktop.bottom.logout')
                    </button>
                </form>
            @endif
        </div>
    </div>
</header>


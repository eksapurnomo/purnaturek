@php
    $portalCustomer = auth()->guard('customer')->user();
    $portalLogo = core()->getCurrentChannel()->logo_url;

    $verificationStatus = null;
    $verificationBadge = null;
    $verificationBadgeClass = null;

    if ($portalCustomer) {
        $verificationStatus = \App\Support\BuyerVerificationManager::getVerificationStatus($portalCustomer);

        if ($verificationStatus === 'approved') {
            $verificationBadge = 'Verified Buyer';
            $verificationBadgeClass = 'pk-badge pk-badge--verified';
        } elseif ($verificationStatus === 'pending') {
            $verificationBadge = 'Pending Review';
            $verificationBadgeClass = 'pk-badge pk-badge--pending';
        } elseif ($verificationStatus === 'rejected') {
            $verificationBadge = 'Action Req.';
            $verificationBadgeClass = 'pk-badge pk-badge--action';
        } else {
            $verificationBadge = 'Registered Member';
            $verificationBadgeClass = 'pk-badge bg-zinc-100 text-zinc-700 border border-zinc-200';
        }
    }
@endphp

<header class="pk-header">
    <div class="pk-header__inner">
        <!-- Brand Wordmark / Channel Logo -->
        <a
            href="{{ $portalCustomer ? route('shop.customers.account.index') : route('shop.home.index') }}"
            class="pk-brand"
            aria-label="PurNatureK Members Portal"
        >
            @if ($portalLogo)
                <img
                    src="{{ $portalLogo }}"
                    alt="PurNatureK"
                    class="pk-brand__logo"
                >
            @else
                <span class="pk-brand__name">
                    PurNature<span class="pk-brand__name-leaf">K</span>
                </span>
            @endif

            <span class="pk-brand__tag">Members Portal</span>
        </a>

        <!-- Header Actions -->
        <div class="pk-header__actions">
            @if ($portalCustomer)
                <!-- Member Identity & Status Badge -->
                <div class="hidden sm:flex items-center gap-2">
                    <span class="pk-header__user-pill" title="{{ $portalCustomer->email }}">
                        <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                        <span>{{ $portalCustomer->first_name }} {{ $portalCustomer->last_name }}</span>
                    </span>

                    @if ($verificationBadge)
                        <span class="{{ $verificationBadgeClass }}">
                            {{ $verificationBadge }}
                        </span>
                    @endif
                </div>

                <a
                    href="{{ route('shop.customers.account.index') }}"
                    class="pk-header__link"
                >
                    Workspace
                </a>

                <a
                    href="{{ route('shop.customers.account.profile.index') }}"
                    class="pk-header__link"
                >
                    Account
                </a>

                <!-- Logout uses the existing customer session route (DELETE) -->
                <form
                    method="POST"
                    action="{{ route('shop.customer.session.destroy') }}"
                    class="inline m-0"
                >
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="pk-header__logout-btn">
                        Sign Out
                    </button>
                </form>

            @else
                <!-- Guest State Header Actions -->
                <a
                    href="https://purnaturekbulk.com"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="hidden md:inline-flex items-center gap-1 text-xs font-semibold text-[#5a6f68] hover:text-[#0f4a3e]"
                >
                    Corporate Info &rarr;
                </a>

                <a
                    href="{{ route('shop.customer.session.index') }}"
                    class="pk-header__link font-bold text-[#0f4a3e]"
                >
                    Sign In
                </a>

                <a
                    href="{{ route('shop.customers.register.index') }}"
                    class="pk-btn pk-btn--primary text-xs py-2 px-3.5 min-h-0"
                >
                    Apply for Wholesale
                </a>
            @endif
        </div>
    </div>
</header>


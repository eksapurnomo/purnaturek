@props([
    'hasDrawer' => false,
])

@php
    $portalCustomer = auth()->guard('customer')->user();

    $verificationStatus = null;
    $verificationLabel = null;
    $verificationVariant = 'neutral';
    $unreadCount = 0;
    $pendingContracts = 0;
    $initials = 'PK';

    if ($portalCustomer) {
        $verificationStatus = \App\Support\BuyerVerificationManager::getVerificationStatus($portalCustomer);
        $unreadCount = \App\Support\MemberConversationManager::getUnreadCountForMember($portalCustomer);
        $pendingContracts = \App\Models\MemberContract::query()
            ->forCustomer((int) $portalCustomer->id)
            ->where('status', \App\Models\MemberContract::STATUS_SENT)
            ->count();

        if ($verificationStatus === 'approved') {
            $verificationLabel = 'Verified Buyer';
            $verificationVariant = 'verified';
        } elseif ($verificationStatus === 'pending') {
            $verificationLabel = 'Pending Review';
            $verificationVariant = 'pending';
        } elseif ($verificationStatus === 'rejected') {
            $verificationLabel = 'Action Required';
            $verificationVariant = 'action';
        } else {
            $verificationLabel = 'Tier 1 Member';
            $verificationVariant = 'neutral';
        }

        $firstInitial = mb_substr(trim((string) $portalCustomer->first_name), 0, 1);
        $lastInitial = mb_substr(trim((string) $portalCustomer->last_name), 0, 1);
        $initials = mb_strtoupper($firstInitial . $lastInitial) ?: 'PK';
    }
@endphp

<header class="pk-header" v-pre>
    <div class="pk-header__inner">
        <div class="pk-header__left">
            @if ($hasDrawer && $portalCustomer)
                <button
                    type="button"
                    id="pk-mobile-drawer-open"
                    class="pk-header__menu-btn"
                    aria-controls="pk-mobile-drawer"
                    aria-expanded="false"
                >
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <span>Menu</span>
                </button>
            @endif

            <a
                href="{{ $portalCustomer ? route('shop.customers.account.index') : route('shop.home.index') }}"
                class="pk-brand"
                aria-label="PurNatureK Members Portal"
            >
                <img
                    src="{{ asset('themes/shop/purnaturek/images/logo-purnaturek-transparent.webp') }}"
                    alt="PUR NATUREK"
                    class="pk-brand__logo"
                    width="156"
                    height="38"
                />
                <span class="pk-brand__divider" aria-hidden="true"></span>
                <span class="pk-brand__tag">Members Portal</span>
            </a>
        </div>

        <div class="pk-header__actions">
            @if ($portalCustomer)
                <div class="pk-header__desktop-links flex items-center gap-1">
                    <a
                        href="{{ route('shop.customers.account.index') }}#catalog"
                        class="pk-header__link"
                    >
                        Catalog
                    </a>

                    <a
                        href="{{ route('shop.customers.account.contracts.index') }}"
                        class="pk-header__link inline-flex items-center gap-1.5"
                    >
                        <span>Contracts</span>
                        @if ($pendingContracts > 0)
                            <span class="pk-badge pk-badge--pending pk-badge--sm">{{ $pendingContracts }}</span>
                        @endif
                    </a>

                    <a
                        href="{{ route('shop.customers.account.conversations.index') }}"
                        class="pk-header__link inline-flex items-center gap-1.5"
                    >
                        <span>Messages</span>
                        @if ($unreadCount > 0)
                            <span class="pk-badge pk-badge--counter pk-badge--sm">{{ $unreadCount }}</span>
                        @endif
                    </a>
                </div>

                @if ($verificationLabel)
                    <span class="hidden lg:inline-flex">
                        <x-shop::portal.badge :variant="$verificationVariant">
                            {{ $verificationLabel }}
                        </x-shop::portal.badge>
                    </span>
                @endif

                <!-- Member Account Dropdown -->
                <div class="pk-account-menu" id="pk-account-menu">
                    <button
                        type="button"
                        id="pk-account-menu-trigger"
                        class="pk-account-menu__trigger"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-controls="pk-account-menu-panel"
                    >
                        <span class="pk-account-menu__avatar" aria-hidden="true">{{ $initials }}</span>
                        <span class="pk-account-menu__identity">
                            <span class="pk-account-menu__name">{{ $portalCustomer->first_name }} {{ $portalCustomer->last_name }}</span>
                            <span class="pk-account-menu__tier">{{ $verificationLabel }}</span>
                        </span>
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>

                    <div
                        id="pk-account-menu-panel"
                        class="pk-account-menu__panel"
                        role="menu"
                        hidden
                    >
                        <div class="pk-account-menu__header">
                            <div class="text-xs font-bold pk-text-primary">
                                {{ $portalCustomer->first_name }} {{ $portalCustomer->last_name }}
                            </div>
                            <div class="pk-account-menu__email">{{ $portalCustomer->email }}</div>
                        </div>

                        <a
                            href="{{ route('shop.customers.account.index') }}"
                            class="pk-account-menu__item"
                            role="menuitem"
                        >
                            <span>Member Workspace</span>
                        </a>

                        <a
                            href="{{ route('shop.customers.account.profile.index') }}"
                            class="pk-account-menu__item"
                            role="menuitem"
                        >
                            <span>My Account Profile</span>
                        </a>

                        <a
                            href="{{ route('shop.customers.account.buyer_verification.index') }}"
                            class="pk-account-menu__item"
                            role="menuitem"
                        >
                            <span>Buyer Verification</span>
                            <x-shop::portal.badge :variant="$verificationVariant" size="sm">
                                {{ $verificationStatus ? ucfirst($verificationStatus) : 'Tier 1' }}
                            </x-shop::portal.badge>
                        </a>

                        <a
                            href="{{ route('shop.customers.account.conversations.index') }}"
                            class="pk-account-menu__item"
                            role="menuitem"
                        >
                            <span>Message Admin</span>
                            @if ($unreadCount > 0)
                                <x-shop::portal.badge variant="counter" size="sm">{{ $unreadCount }}</x-shop::portal.badge>
                            @endif
                        </a>

                        <div style="height: 1px; background: var(--pk-border); margin: 4px 0;"></div>

                        <form
                            method="POST"
                            action="{{ route('shop.customer.session.destroy') }}"
                            class="m-0"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="pk-account-menu__item pk-account-menu__item--danger"
                                role="menuitem"
                            >
                                <span>Sign Out</span>
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a
                    href="https://purnaturekbulk.com/"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="pk-header__desktop-links pk-header__link"
                >
                    Corporate Site &rarr;
                </a>

                <a
                    href="{{ route('shop.customer.session.index') }}"
                    class="pk-header__link"
                    style="color: var(--pk-primary);"
                >
                    Sign In
                </a>

                <a
                    href="{{ route('shop.customers.register.index') }}"
                    class="pk-btn pk-btn--primary pk-btn--sm"
                >
                    Apply for Wholesale
                </a>
            @endif
        </div>
    </div>
</header>

@if ($portalCustomer)
    @pushOnce('scripts')
        <script>
            (function () {
                function initAccountDropdown() {
                    var trigger = document.getElementById('pk-account-menu-trigger');
                    var panel = document.getElementById('pk-account-menu-panel');
                    var root = document.getElementById('pk-account-menu');
                    if (!trigger || !panel || !root || trigger.dataset.pkBound) return;
                    trigger.dataset.pkBound = '1';

                    function closeMenu(restoreFocus) {
                        panel.hidden = true;
                        trigger.setAttribute('aria-expanded', 'false');
                        if (restoreFocus) trigger.focus();
                    }

                    function toggleMenu() {
                        var isOpen = !panel.hidden;
                        panel.hidden = isOpen;
                        trigger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
                    }

                    trigger.addEventListener('click', function (e) {
                        e.stopPropagation();
                        toggleMenu();
                    });

                    document.addEventListener('click', function (e) {
                        if (!panel.hidden && !root.contains(e.target)) {
                            closeMenu(false);
                        }
                    });

                    document.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape' && !panel.hidden) {
                            closeMenu(true);
                        }
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initAccountDropdown);
                } else {
                    initAccountDropdown();
                }
            })();
        </script>
    @endPushOnce
@endif

{{--
    PurNatureK Members Portal — Account Navigation Sidebar
    resources/themes/purnaturek/views/components/layouts/account/navigation.blade.php
--}}
@php
    $menuItems = [];
    foreach (menu()->getItems('customer') as $topLevelItem) {
        foreach ($topLevelItem->getChildren() as $childItem) {
            $menuItems[$childItem->getKey()] = $childItem;
        }
    }

    $onHome = request()->routeIs('shop.customers.account.index');
    $isFiltering = request()->hasAny(['query', 'category_id', 'page']);
    $homeUrl = route('shop.customers.account.index');

    $currentCustomer = auth()->guard('customer')->user();
    $verificationStatus = $currentCustomer ? \App\Support\BuyerVerificationManager::getVerificationStatus($currentCustomer) : null;
    $unreadMessagesCount = $currentCustomer ? \App\Support\MemberConversationManager::getUnreadCountForMember($currentCustomer) : 0;
    $pendingContractsCount = $currentCustomer ? \App\Models\MemberContract::where('customer_id', $currentCustomer->id)->where('status', \App\Models\MemberContract::STATUS_SENT)->count() : 0;

    $verificationBadge = null;
    $verificationBadgeClass = null;

    if ($verificationStatus === 'approved') {
        $verificationBadge = 'Verified';
        $verificationBadgeClass = 'pk-badge pk-badge--verified text-[10px] px-2 py-0.5';
    } elseif ($verificationStatus === 'pending') {
        $verificationBadge = 'Pending';
        $verificationBadgeClass = 'pk-badge pk-badge--pending text-[10px] px-2 py-0.5';
    } elseif ($verificationStatus === 'rejected') {
        $verificationBadge = 'Action Req.';
        $verificationBadgeClass = 'pk-badge pk-badge--action text-[10px] px-2 py-0.5';
    }

    $groups = [
        'Main Portal' => [
            ['label' => 'Member Workspace', 'url' => $homeUrl, 'active' => $onHome && ! $isFiltering],
            ['label' => 'Wholesale Catalog', 'url' => $homeUrl . '#catalog', 'active' => $onHome && $isFiltering],
            [
                'label'  => 'Company & Supply Information',
                'url'    => route('shop.customers.account.company_supply.index'),
                'active' => request()->routeIs('shop.customers.account.company_supply.*'),
            ],
            [
                'label'  => 'Wholesale Partnership Information',
                'url'    => route('shop.customers.account.partnership_information.index'),
                'active' => request()->routeIs('shop.customers.account.partnership_information.*'),
            ],
        ],

        'B2B Commerce' => [
            ['label' => 'RFQ & Quotations', 'key' => 'account.quotes'],
            ['label' => 'Wholesale Purchase', 'key' => 'account.quick_orders'],
            [
                'label'       => 'Contracts & Agreements',
                'url'         => route('shop.customers.account.contracts.index'),
                'active'      => request()->routeIs('shop.customers.account.contracts.*'),
                'badge'       => $pendingContractsCount > 0 ? (string) $pendingContractsCount : null,
                'badge_class' => $pendingContractsCount > 0 ? 'pk-badge bg-amber-500 text-white font-bold text-[10px] px-2 py-0.5' : null,
            ],
            ['label' => 'Purchase Orders', 'key' => 'account.purchase_orders'],
            ['label' => 'Orders & Transactions', 'key' => 'account.orders'],
        ],

        'Member Services' => [
            [
                'label'       => 'Buyer Verification',
                'url'         => route('shop.customers.account.buyer_verification.index'),
                'active'      => request()->routeIs('shop.customers.account.buyer_verification.*'),
                'badge'       => $verificationBadge,
                'badge_class' => $verificationBadgeClass,
            ],
            [
                'label'       => 'Business Documents',
                'url'         => route('shop.customers.account.business_documents.index'),
                'active'      => request()->routeIs('shop.customers.account.business_documents.*'),
            ],
            [
                'label'       => 'Product Documentation',
                'url'         => $homeUrl . '#catalog',
                'active'      => false,
            ],
            [
                'label'       => 'Message Admin',
                'url'         => route('shop.customers.account.conversations.index'),
                'active'      => request()->routeIs('shop.customers.account.conversations.*'),
                'badge'       => $unreadMessagesCount > 0 ? (string) $unreadMessagesCount : null,
                'badge_class' => $unreadMessagesCount > 0 ? 'pk-badge pk-badge--counter text-[10px] px-2 py-0.5' : null,
            ],
            ['label' => 'My Account Profile', 'key' => 'account.profile'],
        ],
    ];

    $usedKeys = [];
    $resolved = [];

    foreach ($groups as $groupTitle => $entries) {
        foreach ($entries as $entry) {
            if (! empty($entry['soon']) || ! empty($entry['url'])) {
                $resolved[$groupTitle][] = $entry;
                continue;
            }

            $menuItem = $menuItems[$entry['key']] ?? null;
            if (! $menuItem) {
                continue;
            }

            $usedKeys[] = $entry['key'];
            $resolved[$groupTitle][] = [
                'label'  => $entry['label'],
                'url'    => $menuItem->getUrl(),
                'active' => $menuItem->isActive(),
            ];
        }
    }

    foreach ($menuItems as $key => $menuItem) {
        if (in_array($key, $usedKeys, true)) {
            continue;
        }

        $resolved['Account & Company Tools'][] = [
            'label'  => $menuItem->getName(),
            'url'    => $menuItem->getUrl(),
            'active' => $menuItem->isActive(),
        ];
    }
@endphp

<nav
    class="pk-sidebar"
    aria-label="Members Portal navigation"
    v-pre
>
    <!-- Member Identity Header Card -->
    @if ($currentCustomer)
        <div class="pk-sidebar__user">
            <div class="pk-sidebar__user-name">
                {{ $currentCustomer->first_name }} {{ $currentCustomer->last_name }}
            </div>
            <div class="text-[11px] text-[#5a6f68] truncate mt-0.5">
                {{ $currentCustomer->email }}
            </div>
            <div class="mt-2.5 flex items-center justify-between">
                @if ($verificationStatus === 'approved')
                    <span class="pk-badge pk-badge--verified text-[10px] px-2 py-0.5">
                        &check; Verified Buyer
                    </span>
                @elseif ($verificationStatus === 'pending')
                    <span class="pk-badge pk-badge--pending text-[10px] px-2 py-0.5">
                        &bull; Verification Pending
                    </span>
                @elseif ($verificationStatus === 'rejected')
                    <span class="pk-badge pk-badge--action text-[10px] px-2 py-0.5">
                        &excl; Action Required
                    </span>
                @else
                    <span class="pk-badge bg-zinc-100 text-zinc-700 border border-zinc-200 text-[10px] px-2 py-0.5">
                        Registered Member
                    </span>
                @endif

                <a
                    href="{{ route('shop.customers.account.profile.index') }}"
                    class="text-[11px] text-emerald-700 font-semibold hover:underline"
                >
                    Settings &rarr;
                </a>
            </div>
        </div>
    @endif

    <!-- Mobile collapsible toggle -->
    <input
        type="checkbox"
        id="pk-nav-toggle"
        class="pk-sidebar__toggle-input"
        aria-label="Toggle portal menu"
    >

    <label
        for="pk-nav-toggle"
        class="pk-sidebar__toggle"
    >
        <span>Portal Navigation Menu</span>
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </label>

    <div class="pk-sidebar__groups">
        @foreach ($resolved as $groupTitle => $rows)
            <div class="pk-nav-group">
                <p class="pk-nav-group__title">{{ $groupTitle }}</p>

                @foreach ($rows as $row)
                    @if (! empty($row['soon']))
                        <span
                            class="pk-nav-item is-soon"
                            aria-disabled="true"
                        >
                            <span>{{ $row['label'] }}</span>
                            <span class="pk-nav-soon">Soon</span>
                        </span>
                    @else
                        <a
                            href="{{ $row['url'] }}"
                            class="pk-nav-item {{ ! empty($row['active']) ? 'is-active' : '' }} flex items-center justify-between"
                            @if (! empty($row['active'])) aria-current="page" @endif
                        >
                            <span>{{ $row['label'] }}</span>

                            @if (! empty($row['badge']))
                                <span class="{{ $row['badge_class'] ?? 'pk-nav-soon' }}">{{ $row['badge'] }}</span>
                            @endif
                        </a>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>
</nav>


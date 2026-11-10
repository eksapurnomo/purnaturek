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
    $verificationStatus = $currentCustomer
        ? \App\Support\BuyerVerificationManager::getVerificationStatus($currentCustomer)
        : null;
    $unreadMessagesCount = $currentCustomer
        ? \App\Support\MemberConversationManager::getUnreadCountForMember($currentCustomer)
        : 0;
    $pendingContractsCount = $currentCustomer
        ? \App\Models\MemberContract::query()
            ->forCustomer((int) $currentCustomer->id)
            ->where('status', \App\Models\MemberContract::STATUS_SENT)
            ->count()
        : 0;

    $verificationBadge = null;
    $verificationBadgeVariant = 'neutral';

    if ($verificationStatus === 'approved') {
        $verificationBadge = 'Verified';
        $verificationBadgeVariant = 'verified';
    } elseif ($verificationStatus === 'pending') {
        $verificationBadge = 'Pending';
        $verificationBadgeVariant = 'pending';
    } elseif ($verificationStatus === 'rejected') {
        $verificationBadge = 'Action Req.';
        $verificationBadgeVariant = 'action';
    }

    $groups = [
        'Main Portal' => [
            ['label' => 'Member Workspace', 'url' => $homeUrl, 'active' => $onHome && ! $isFiltering],
            ['label' => 'Wholesale Catalog', 'url' => $homeUrl . '#catalog', 'active' => $onHome && $isFiltering],
            [
                'label'  => 'Company & Supply Info',
                'url'    => route('shop.customers.account.company_supply.index'),
                'active' => request()->routeIs('shop.customers.account.company_supply.*'),
            ],
            [
                'label'  => 'Partnership Information',
                'url'    => route('shop.customers.account.partnership_information.index'),
                'active' => request()->routeIs('shop.customers.account.partnership_information.*'),
            ],
        ],

        'B2B Commerce' => [
            ['label' => 'RFQ & Quotations', 'key' => 'account.quotes'],
            ['label' => 'Wholesale Purchase', 'key' => 'account.quick_orders'],
            [
                'label'         => 'Contracts & Agreements',
                'url'           => route('shop.customers.account.contracts.index'),
                'active'        => request()->routeIs('shop.customers.account.contracts.*'),
                'badge'         => $pendingContractsCount > 0 ? (string) $pendingContractsCount : null,
                'badge_variant' => 'pending',
            ],
            ['label' => 'Purchase Orders', 'key' => 'account.purchase_orders'],
            ['label' => 'Orders & Transactions', 'key' => 'account.orders'],
        ],

        'Member Services' => [
            [
                'label'         => 'Buyer Verification',
                'url'           => route('shop.customers.account.buyer_verification.index'),
                'active'        => request()->routeIs('shop.customers.account.buyer_verification.*'),
                'badge'         => $verificationBadge,
                'badge_variant' => $verificationBadgeVariant,
            ],
            [
                'label'  => 'Business Documents',
                'url'    => route('shop.customers.account.business_documents.index'),
                'active' => request()->routeIs('shop.customers.account.business_documents.*'),
            ],
            [
                'label'  => 'Product Documentation',
                'url'    => $homeUrl . '#catalog',
                'active' => false,
            ],
            [
                'label'         => 'Message Admin',
                'url'           => route('shop.customers.account.conversations.index'),
                'active'        => request()->routeIs('shop.customers.account.conversations.*'),
                'badge'         => $unreadMessagesCount > 0 ? (string) $unreadMessagesCount : null,
                'badge_variant' => 'counter',
            ],
            ['label' => 'My Account Profile', 'key' => 'account.profile'],
        ],
    ];

    $usedKeys = [];
    $resolved = [];

    foreach ($groups as $groupTitle => $entries) {
        foreach ($entries as $entry) {
            if (! empty($entry['url'])) {
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
    @if ($currentCustomer)
        <div class="pk-sidebar__user">
            <div class="pk-sidebar__user-name">
                {{ $currentCustomer->first_name }} {{ $currentCustomer->last_name }}
            </div>
            <div class="pk-sidebar__user-email" title="{{ $currentCustomer->email }}">
                {{ $currentCustomer->email }}
            </div>
            <div class="pk-sidebar__user-meta">
                @if ($verificationStatus === 'approved')
                    <x-shop::portal.badge variant="verified" size="sm">&check; Verified Buyer</x-shop::portal.badge>
                @elseif ($verificationStatus === 'pending')
                    <x-shop::portal.badge variant="pending" size="sm">&bull; Pending</x-shop::portal.badge>
                @elseif ($verificationStatus === 'rejected')
                    <x-shop::portal.badge variant="action" size="sm">! Action Req.</x-shop::portal.badge>
                @else
                    <x-shop::portal.badge variant="neutral" size="sm">Tier 1 Member</x-shop::portal.badge>
                @endif

                <a
                    href="{{ route('shop.customers.account.profile.index') }}"
                    class="text-[11px] font-semibold pk-text-primary hover:underline"
                >
                    Profile &rarr;
                </a>
            </div>
        </div>
    @endif

    @foreach ($resolved as $groupTitle => $rows)
        <div class="pk-nav-group">
            <p class="pk-nav-group__title">{{ $groupTitle }}</p>

            @foreach ($rows as $row)
                <a
                    href="{{ $row['url'] }}"
                    class="pk-nav-item {{ ! empty($row['active']) ? 'is-active' : '' }}"
                    @if (! empty($row['active'])) aria-current="page" @endif
                >
                    <span>{{ $row['label'] }}</span>

                    @if (! empty($row['badge']))
                        <x-shop::portal.badge :variant="$row['badge_variant'] ?? 'neutral'" size="sm">
                            {{ $row['badge'] }}
                        </x-shop::portal.badge>
                    @endif
                </a>
            @endforeach
        </div>
    @endforeach
</nav>

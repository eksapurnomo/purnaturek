{{--
    PurNatureK Members Portal — Contracts & Commercial Agreements Index
    (shop::customers.account.contracts.index)
--}}
<x-shop::layouts.account>
    <x-slot:title>
        Contracts &amp; Commercial Agreements — PurNatureK Members Portal
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="pk-main pk-stack-md" v-pre>
        <!-- Flash Messages -->
        @if (session('success'))
            <x-shop::portal.alert variant="success">
                {{ session('success') }}
            </x-shop::portal.alert>
        @endif

        @if (session('warning'))
            <x-shop::portal.alert variant="warning">
                {{ session('warning') }}
            </x-shop::portal.alert>
        @endif

        @if (session('info'))
            <x-shop::portal.alert variant="info">
                {{ session('info') }}
            </x-shop::portal.alert>
        @endif

        <!-- Pending Action Callout -->
        @if ($pendingActionCount > 0)
            <x-shop::portal.alert
                variant="warning"
                :title="'Action Required: ' . $pendingActionCount . ' Agreement(s) Pending Review'"
            >
                Please inspect the commercial terms and record your electronic acknowledgment to enable wholesale fulfillment.
            </x-shop::portal.alert>
        @endif

        <!-- Page Header -->
        <x-shop::portal.page-header
            eyebrow="Commercial Governance"
            title="Contracts & Commercial Agreements"
            subtitle="Review issued supply contracts, non-disclosure agreements, and approved wholesale reference templates."
        >
            <x-slot:actions>
                <x-shop::portal.button
                    :href="route('shop.customers.account.contracts.templates')"
                    variant="secondary"
                    size="sm"
                >
                    Reference Templates ({{ $templates->count() }})
                </x-shop::portal.button>
            </x-slot:actions>
        </x-shop::portal.page-header>

        <!-- Issued Contracts Table -->
        <x-shop::portal.card
            eyebrow="Executed & Issued Records"
            title="Commercial Agreements"
        >
            @if ($contracts->isEmpty())
                <x-shop::portal.empty-state
                    title="No Issued Agreements On Record"
                    description="When PurNatureK administration issues a formal supply agreement or commercial document for your account, it will appear here for inspection and electronic acknowledgment."
                >
                    @if ($templates->isNotEmpty())
                        <x-slot:actions>
                            <x-shop::portal.button
                                :href="route('shop.customers.account.contracts.templates')"
                                variant="secondary"
                            >
                                Browse Reference Templates
                            </x-shop::portal.button>
                        </x-slot:actions>
                    @endif
                </x-shop::portal.empty-state>
            @else
                <div class="pk-table-wrap">
                    <table class="pk-table">
                        <thead>
                            <tr>
                                <th>Reference #</th>
                                <th>Agreement Title</th>
                                <th>Status</th>
                                <th>Issued Date</th>
                                <th>Deadline / Expiry</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contracts as $contract)
                                <tr>
                                    <td class="pk-mono" style="font-weight: 700;">
                                        <a
                                            href="{{ route('shop.customers.account.contracts.view', $contract->id) }}"
                                            style="color: var(--pk-primary); text-decoration: none;"
                                        >
                                            {{ $contract->reference_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--pk-primary);">{{ $contract->title }}</div>
                                        @if ($contract->template)
                                            <div style="font-size: 11.5px; color: var(--pk-muted); margin-top: 2px;">
                                                Template: {{ $contract->template->title }} (v{{ $contract->template_version ?? '1.0' }})
                                            </div>
                                        @endif
                                    </td>
                                    <td style="white-space: nowrap;">
                                        @if ($contract->status === 'accepted')
                                            <x-shop::portal.badge variant="verified">Accepted</x-shop::portal.badge>
                                        @elseif ($contract->status === 'sent')
                                            <x-shop::portal.badge variant="pending">Pending Your Review</x-shop::portal.badge>
                                        @elseif ($contract->status === 'rejected')
                                            <x-shop::portal.badge variant="action">Declined</x-shop::portal.badge>
                                        @elseif ($contract->status === 'expired')
                                            <x-shop::portal.badge variant="neutral">Expired</x-shop::portal.badge>
                                        @else
                                            <x-shop::portal.badge variant="neutral">{{ ucfirst($contract->status) }}</x-shop::portal.badge>
                                        @endif
                                    </td>
                                    <td style="font-size: 12.5px; color: var(--pk-muted); white-space: nowrap;">
                                        {{ $contract->sent_at ? $contract->sent_at->format('M d, Y') : ($contract->created_at ? $contract->created_at->format('M d, Y') : '—') }}
                                    </td>
                                    <td style="font-size: 12.5px; white-space: nowrap;">
                                        @if ($contract->expires_at)
                                            <span style="color: {{ $contract->expires_at->isPast() ? 'var(--pk-danger-ink)' : 'var(--pk-ink)' }}; font-weight: {{ $contract->expires_at->isPast() ? '700' : '500' }};">
                                                {{ $contract->expires_at->format('M d, Y') }}
                                            </span>
                                        @else
                                            <span style="color: var(--pk-muted);">No Hard Expiry</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <div style="display: inline-flex; align-items: center; gap: 8px;">
                                            <x-shop::portal.button
                                                :href="route('shop.customers.account.contracts.download', $contract->id)"
                                                variant="secondary"
                                                size="sm"
                                                title="Download Agreement Document"
                                            >
                                                Download
                                            </x-shop::portal.button>

                                            @if ($contract->canBeActionedByMember())
                                                <x-shop::portal.button
                                                    :href="route('shop.customers.account.contracts.view', $contract->id)"
                                                    variant="primary"
                                                    size="sm"
                                                >
                                                    Review &amp; Acknowledge
                                                </x-shop::portal.button>
                                            @else
                                                <x-shop::portal.button
                                                    :href="route('shop.customers.account.contracts.view', $contract->id)"
                                                    variant="secondary"
                                                    size="sm"
                                                >
                                                    View Details
                                                </x-shop::portal.button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($contracts->hasPages())
                    <div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--pk-border);">
                        {{ $contracts->links() }}
                    </div>
                @endif
            @endif
        </x-shop::portal.card>

        <!-- Reference Templates Summary Library -->
        @if ($templates->isNotEmpty())
            <x-shop::portal.card
                alt
                eyebrow="Standard Reference Library"
                title="Reference Agreement Templates"
            >
                <x-slot:actions>
                    <x-shop::portal.button
                        :href="route('shop.customers.account.contracts.templates')"
                        variant="secondary"
                        size="sm"
                    >
                        Browse All Templates &rarr;
                    </x-shop::portal.button>
                </x-slot:actions>

                <div class="pk-grid-3">
                    @foreach ($templates->take(3) as $tpl)
                        <div class="pk-template-card">
                            <div>
                                <div class="pk-template-card__top">
                                    <x-shop::portal.badge variant="neutral" size="sm">
                                        v{{ $tpl->version ?: '1.0' }}
                                    </x-shop::portal.badge>
                                    <x-shop::portal.badge variant="earth" size="sm">
                                        {{ \App\Models\ContractTemplate::CATEGORIES[$tpl->category] ?? ucfirst($tpl->category) }}
                                    </x-shop::portal.badge>
                                </div>
                                <h3 class="pk-template-card__title">{{ $tpl->title }}</h3>
                                <p class="pk-template-card__desc">
                                    {{ $tpl->description ?: 'Standard wholesale commercial agreement template.' }}
                                </p>
                            </div>

                            <div class="pk-template-card__foot">
                                <span style="font-size: 11.5px; color: var(--pk-muted);">
                                    {{ $tpl->effective_date ? 'Effective ' . $tpl->effective_date->format('M Y') : 'Reference Spec' }}
                                </span>

                                @if ($tpl->hasFile())
                                    <x-shop::portal.button
                                        :href="route('shop.customers.account.contracts.templates.download', $tpl->id)"
                                        variant="secondary"
                                        size="sm"
                                    >
                                        Download
                                    </x-shop::portal.button>
                                @else
                                    <span style="font-size: 11.5px; color: var(--pk-muted);">Pending File</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-shop::portal.card>
        @endif
    </div>
</x-shop::layouts.account>

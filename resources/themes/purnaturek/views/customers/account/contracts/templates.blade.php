{{--
    PurNatureK Members Portal — Reference Contract Templates Library
    (shop::customers.account.contracts.templates)
--}}
<x-shop::layouts.account>
    <x-slot:title>
        Contract Templates Library — PurNatureK Members Portal
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="pk-main pk-stack-md" v-pre>
        <x-shop::portal.page-header
            eyebrow="Legal & Commercial Reference"
            title="Reference Contract Templates"
            subtitle="Download standard reference copies of PurNatureK commercial agreements, non-disclosure agreements, and wholesale terms."
        >
            <x-slot:actions>
                <x-shop::portal.button
                    :href="route('shop.customers.account.contracts.index')"
                    variant="secondary"
                    size="sm"
                >
                    My Issued Agreements &rarr;
                </x-shop::portal.button>
            </x-slot:actions>
        </x-shop::portal.page-header>

        <x-shop::portal.card
            eyebrow="Published Templates"
            :title="'Standard Reference Agreements (' . $templates->count() . ')'"
        >
            @if ($templates->isEmpty())
                <x-shop::portal.empty-state
                    title="No Reference Templates Published"
                    description="Standard wholesale agreement templates will appear here once published by PurNatureK administration for member reference."
                >
                    <x-slot:actions>
                        <x-shop::portal.button
                            :href="route('shop.customers.account.contracts.index')"
                            variant="secondary"
                        >
                            Return to My Agreements
                        </x-shop::portal.button>
                    </x-slot:actions>
                </x-shop::portal.empty-state>
            @else
                <div class="pk-grid-3">
                    @foreach ($templates as $tpl)
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

                                <h2 class="pk-template-card__title">{{ $tpl->title }}</h2>

                                <p class="pk-template-card__desc">
                                    {{ $tpl->description ?: 'Standard template agreement provided for member reference.' }}
                                </p>
                            </div>

                            <div class="pk-template-card__foot">
                                <span style="font-size: 11.5px; color: var(--pk-muted);">
                                    {{ $tpl->effective_date ? 'Effective ' . $tpl->effective_date->format('M Y') : 'Reference Spec' }}
                                </span>

                                @if ($tpl->hasFile())
                                    <x-shop::portal.button
                                        :href="route('shop.customers.account.contracts.templates.download', $tpl->id)"
                                        variant="primary"
                                        size="sm"
                                    >
                                        Download ({{ $tpl->formattedFileSize() }})
                                    </x-shop::portal.button>
                                @else
                                    <span style="font-size: 11.5px; color: var(--pk-muted); font-style: italic;">Approved File Pending</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-shop::portal.card>
    </div>
</x-shop::layouts.account>

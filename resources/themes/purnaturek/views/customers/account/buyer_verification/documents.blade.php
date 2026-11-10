{{--
    PurNatureK Members Portal — Business Documents Archive
    (shop::customers.account.buyer_verification.documents)
--}}
<x-shop::layouts.account>
    <x-slot:title>
        Business Documents — PurNatureK Members Portal
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="pk-main pk-stack-md" v-pre>
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

        <x-shop::portal.page-header
            eyebrow="Compliance Records"
            title="Business Documents"
            subtitle="All business credentials and regulatory records uploaded for wholesale account verification."
        >
            <x-slot:actions>
                <x-shop::portal.button
                    :href="route('shop.customers.account.buyer_verification.index')"
                    variant="secondary"
                    size="sm"
                >
                    Verification Overview
                </x-shop::portal.button>
            </x-slot:actions>
        </x-shop::portal.page-header>

        <x-shop::portal.card
            eyebrow="Document Vault"
            :title="'Uploaded Credentials (' . $documents->count() . ')'"
        >
            @if ($documents->isEmpty())
                <x-shop::portal.empty-state
                    title="No Business Documents Uploaded"
                    description="You have not uploaded any business verification credentials yet. Submit your application to unlock Tier 2 wholesale ordering workflows."
                >
                    <x-slot:actions>
                        <x-shop::portal.button
                            :href="route('shop.customers.account.buyer_verification.index')"
                            variant="primary"
                        >
                            Submit Verification Application
                        </x-shop::portal.button>
                    </x-slot:actions>
                </x-shop::portal.empty-state>
            @else
                <div class="pk-table-wrap">
                    <table class="pk-table">
                        <thead>
                            <tr>
                                <th>Document</th>
                                <th>Credential Type</th>
                                <th>File Size</th>
                                <th>Verification Status</th>
                                <th>Upload Date</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($documents as $doc)
                                @php
                                    $appStatus = $doc->buyerVerification?->status;
                                @endphp
                                <tr>
                                    <td style="font-weight: 600; color: var(--pk-primary);">
                                        {{ $doc->file_name }}
                                    </td>
                                    <td>
                                        <x-shop::portal.badge variant="neutral">
                                            {{ $doc->document_type_label }}
                                        </x-shop::portal.badge>
                                    </td>
                                    <td class="pk-mono" style="font-size: 12px; color: var(--pk-muted);">
                                        {{ $doc->formatted_file_size }}
                                    </td>
                                    <td>
                                        @if ($appStatus === 'approved')
                                            <x-shop::portal.badge variant="verified">Verified</x-shop::portal.badge>
                                        @elseif ($appStatus === 'pending')
                                            <x-shop::portal.badge variant="pending">Under Review</x-shop::portal.badge>
                                        @elseif ($appStatus === 'rejected')
                                            <x-shop::portal.badge variant="action">Rejected</x-shop::portal.badge>
                                        @else
                                            <x-shop::portal.badge variant="neutral">N/A</x-shop::portal.badge>
                                        @endif
                                    </td>
                                    <td style="font-size: 12.5px; color: var(--pk-muted); white-space: nowrap;">
                                        {{ $doc->created_at?->format('M d, Y H:i') ?? '—' }}
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <x-shop::portal.button
                                            :href="route('shop.customers.account.business_documents.download', $doc->id)"
                                            variant="secondary"
                                            size="sm"
                                            target="_blank"
                                        >
                                            Download
                                        </x-shop::portal.button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-shop::portal.card>
    </div>
</x-shop::layouts.account>

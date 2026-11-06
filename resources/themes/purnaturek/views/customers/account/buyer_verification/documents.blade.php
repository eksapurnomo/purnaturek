<x-shop::layouts.account>
    <x-slot:title>
        Business Documents — PurNatureK Members Portal
    </x-slot>

    <x-shop::layouts.account.navigation />

    <div class="pk-main">
        <div class="flex flex-col gap-6">

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    {{ session('warning') }}
                </div>
            @endif

            <!-- Page Header -->
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-zinc-200 bg-white p-6 max-sm:p-4">
                <div>
                    <h1 class="text-xl font-bold text-zinc-900">Business Documents</h1>
                    <p class="text-sm text-zinc-500 mt-1">
                        All business credentials and compliance records uploaded for wholesale account verification.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a
                        href="{{ route('shop.customers.account.buyer_verification.index') }}"
                        class="secondary-button text-sm"
                    >
                        Verification Overview
                    </a>
                </div>
            </div>

            <!-- Documents Table -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 max-sm:p-4">
                @if ($documents->isEmpty())
                    <div class="text-center py-12">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 text-zinc-400">
                            <span class="icon-file text-3xl"></span>
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-zinc-900">No Business Documents Uploaded</h3>
                        <p class="mt-1 text-sm text-zinc-500">
                            You have not uploaded any business verification documents yet.
                        </p>
                        <div class="mt-6">
                            <a
                                href="{{ route('shop.customers.account.buyer_verification.index') }}"
                                class="primary-button text-sm"
                            >
                                Submit Verification Application
                            </a>
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-zinc-600">
                            <thead class="border-b border-zinc-200 bg-zinc-50 text-xs uppercase font-semibold text-zinc-700">
                                <tr>
                                    <th class="px-4 py-3">Document</th>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3">Size</th>
                                    <th class="px-4 py-3">Verification Status</th>
                                    <th class="px-4 py-3">Upload Date</th>
                                    <th class="px-4 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200">
                                @foreach ($documents as $doc)
                                    <tr class="hover:bg-zinc-50 transition-colors">
                                        <td class="px-4 py-4 font-medium text-zinc-900">
                                            <div class="flex items-center gap-2">
                                                <span class="icon-file text-xl text-zinc-400"></span>
                                                <span>{{ $doc->file_name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex rounded-md bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700">
                                                {{ $doc->document_type_label }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-zinc-500">
                                            {{ $doc->formatted_file_size }}
                                        </td>
                                        <td class="px-4 py-4">
                                            @php
                                                $appStatus = $doc->buyerVerification?->status;
                                            @endphp
                                            @if ($appStatus === 'approved')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                                    Verified
                                                </span>
                                            @elseif ($appStatus === 'pending')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-600"></span>
                                                    Under Review
                                                </span>
                                            @elseif ($appStatus === 'rejected')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-rose-600"></span>
                                                    Rejected
                                                </span>
                                            @else
                                                <span class="text-xs text-zinc-400">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-zinc-500">
                                            {{ $doc->created_at?->format('M d, Y H:i') ?? '—' }}
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <a
                                                href="{{ route('shop.customers.account.business_documents.download', $doc->id) }}"
                                                class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-50 transition-colors"
                                                target="_blank"
                                            >
                                                <span class="icon-download text-sm"></span>
                                                Download
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-shop::layouts.account>


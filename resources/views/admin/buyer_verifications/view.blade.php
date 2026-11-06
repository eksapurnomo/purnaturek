<x-admin::layouts>
    <x-slot:title>
        Review Buyer Verification #{{ $verification->id }} — {{ $verification->company_name }}
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                Buyer Verification #{{ $verification->id }}
            </h1>

            @if ($verification->status === 'approved')
                <span class="label-active text-sm">
                    Approved (Verified Wholesale Buyer)
                </span>
            @elseif ($verification->status === 'pending')
                <span class="label-pending text-sm">
                    Pending Review
                </span>
            @else
                <span class="label-canceled text-sm">
                    Rejected
                </span>
            @endif
        </div>

        <a
            href="{{ route('admin.b2b.buyer_verifications.index') }}"
            class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
        >
            Back to List
        </a>
    </div>

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="mt-4 rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 dark:bg-emerald-950 dark:border-emerald-800 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="mt-4 rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 dark:bg-amber-950 dark:border-amber-800 dark:text-amber-300">
            {{ session('warning') }}
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="mt-4 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 dark:bg-rose-950 dark:border-rose-800 dark:text-rose-300">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Main Details (2 cols) -->
        <div class="lg:col-span-2 flex flex-col gap-6">

            <!-- Business Information Card -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white border-b border-gray-200 pb-3 dark:border-gray-800">
                    Business Information
                </h2>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Company Name</p>
                        <p class="font-medium text-gray-800 dark:text-white mt-1">{{ $verification->company_name }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Tax ID / Registration Number</p>
                        <p class="font-medium text-gray-800 dark:text-white mt-1">{{ $verification->tax_id }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Business Type</p>
                        <p class="font-medium text-gray-800 dark:text-white mt-1">{{ $verification->business_type_label }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Business Phone</p>
                        <p class="font-medium text-gray-800 dark:text-white mt-1">{{ $verification->phone ?? '—' }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <p class="text-gray-500 dark:text-gray-400">Business Address</p>
                        <p class="font-medium text-gray-800 dark:text-white mt-1 whitespace-pre-line">{{ $verification->address ?? '—' }}</p>
                    </div>

                    @if ($verification->notes)
                        <div class="sm:col-span-2">
                            <p class="text-gray-500 dark:text-gray-400">Applicant Notes / Trade References</p>
                            <p class="font-medium text-gray-800 dark:text-white mt-1 whitespace-pre-line">{{ $verification->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Uploaded Business Documents Card -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white border-b border-gray-200 pb-3 dark:border-gray-800">
                    Uploaded Business Credentials ({{ $verification->documents->count() }})
                </h2>

                @if ($verification->documents->isEmpty())
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        No documents were attached to this application.
                    </p>
                @else
                    <div class="mt-4 divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach ($verification->documents as $doc)
                            <div class="flex items-center justify-between py-3">
                                <div class="flex items-center gap-3">
                                    <span class="icon-file text-2xl text-gray-400 dark:text-gray-500"></span>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800 dark:text-white">
                                            {{ $doc->file_name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $doc->document_type_label }} &bull; {{ $doc->formatted_file_size }} &bull; {{ $doc->created_at?->format('M d, Y H:i') }}
                                        </p>
                                    </div>
                                </div>

                                <a
                                    href="{{ route('admin.b2b.buyer_verifications.download_document', $doc->id) }}"
                                    class="secondary-button text-xs"
                                    target="_blank"
                                >
                                    Download Securely
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Review History / Rejection Reason -->
            @if ($verification->reviewed_at)
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white border-b border-gray-200 pb-3 dark:border-gray-800">
                        Review History
                    </h2>

                    <div class="mt-4 space-y-3 text-sm">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Reviewer</p>
                            <p class="font-medium text-gray-800 dark:text-white mt-0.5">
                                {{ $verification->reviewer?->name ?? 'Administrator' }} ({{ $verification->reviewer?->email ?? 'N/A' }})
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Reviewed On</p>
                            <p class="font-medium text-gray-800 dark:text-white mt-0.5">
                                {{ $verification->reviewed_at->format('M d, Y H:i') }}
                            </p>
                        </div>

                        @if ($verification->rejection_reason)
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Recorded Rejection Reason</p>
                                <div class="mt-1 rounded-md bg-rose-50 p-3 text-rose-800 border border-rose-200 dark:bg-rose-950 dark:border-rose-900 dark:text-rose-200">
                                    {{ $verification->rejection_reason }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        <!-- Sidebar / Review Actions (1 col) -->
        <div class="flex flex-col gap-6">

            <!-- Applicant Summary Card -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white border-b border-gray-200 pb-3 dark:border-gray-800">
                    Applicant Profile
                </h2>

                <div class="mt-4 space-y-3 text-sm">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Member Name</p>
                        <p class="font-medium text-gray-800 dark:text-white mt-0.5">
                            {{ $verification->customer?->first_name }} {{ $verification->customer?->last_name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Member Email</p>
                        <p class="font-medium text-gray-800 dark:text-white mt-0.5">
                            {{ $verification->customer?->email }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Customer ID</p>
                        <p class="font-medium text-gray-800 dark:text-white mt-0.5">
                            #{{ $verification->customer_id }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Submission Date</p>
                        <p class="font-medium text-gray-800 dark:text-white mt-0.5">
                            {{ $verification->submitted_at?->format('M d, Y H:i') ?? '—' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Review Actions Card -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white border-b border-gray-200 pb-3 dark:border-gray-800">
                    Review Actions
                </h2>

                <div class="mt-4 space-y-4">
                    <!-- Approve Button & Form -->
                    <form
                        method="POST"
                        action="{{ route('admin.b2b.buyer_verifications.approve', $verification->id) }}"
                        onsubmit="return confirm('Are you sure you want to approve this buyer verification? This will grant the member full access to wholesale transactions and RFQs.');"
                    >
                        @csrf
                        <button
                            type="submit"
                            class="w-full primary-button bg-emerald-600 hover:bg-emerald-700 text-white justify-center py-2.5"
                            {{ $verification->status === 'approved' ? 'disabled' : '' }}
                        >
                            {{ $verification->status === 'approved' ? 'Already Approved' : 'Approve as Verified Buyer' }}
                        </button>
                    </form>

                    <!-- Reject Form -->
                    <form
                        method="POST"
                        action="{{ route('admin.b2b.buyer_verifications.reject', $verification->id) }}"
                        onsubmit="return confirm('Are you sure you want to reject this buyer verification application?');"
                        class="space-y-3 pt-2 border-t border-gray-200 dark:border-gray-800"
                    >
                        @csrf

                        <div>
                            <label for="rejection_reason" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                Rejection Reason <span class="text-rose-500">*</span>
                            </label>
                            <textarea
                                id="rejection_reason"
                                name="rejection_reason"
                                rows="3"
                                class="mt-1 w-full rounded-md border border-gray-300 p-2 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="State clearly what documents or details are required for re-submission..."
                                required
                            >{{ old('rejection_reason', $verification->rejection_reason) }}</textarea>
                        </div>

                        <button
                            type="submit"
                            class="w-full transparent-button border border-rose-500 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950 justify-center py-2"
                        >
                            Reject Application
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-admin::layouts>


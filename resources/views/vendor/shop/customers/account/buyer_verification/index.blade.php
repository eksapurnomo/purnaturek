<x-shop::layouts.account>
    <x-slot:title>
        Buyer Verification — PurNatureK Members Portal
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

            @if (isset($errors) && $errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    <p class="font-semibold mb-1">Please correct the following errors:</p>
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Status Banner & Overview Card -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 max-sm:p-4">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-200 pb-4">
                    <div>
                        <h2 class="text-xl font-bold text-zinc-900">Buyer Verification Status</h2>
                        <p class="text-sm text-zinc-500 mt-1">
                            PurNatureK Wholesale Commercial Account Verification
                        </p>
                    </div>

                    <div>
                        @if ($status === 'approved')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                                <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                                Verified Wholesale Buyer
                            </span>
                        @elseif ($status === 'pending')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                <span class="h-2 w-2 rounded-full bg-amber-600"></span>
                                Pending Review
                            </span>
                        @elseif ($status === 'rejected')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-800">
                                <span class="h-2 w-2 rounded-full bg-rose-600"></span>
                                Action Required / Rejected
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700">
                                <span class="h-2 w-2 rounded-full bg-zinc-400"></span>
                                Registered Member (Unverified)
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Status Explanations -->
                @if ($status === 'approved')
                    <div class="mt-6 rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-emerald-900">
                        <h4 class="font-semibold text-base mb-1">Your Account is Fully Verified</h4>
                        <p class="text-sm">
                            Congratulations! Your business credentials for <strong>{{ $latestVerification->company_name }}</strong> have been verified. You have full access to wholesale commercial transactions, formal RFQs, sample requests, and purchase orders.
                        </p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <a href="{{ route('shop.customers.account.index') }}#catalog" class="primary-button text-sm px-4 py-2">
                                Browse Wholesale Catalog
                            </a>
                            <a href="{{ route('shop.customers.account.business_documents.index') }}" class="secondary-button text-sm px-4 py-2 border-zinc-300">
                                View Business Documents
                            </a>
                        </div>
                    </div>

                @elseif ($status === 'pending')
                    <div class="mt-6 rounded-lg bg-amber-50 border border-amber-200 p-4 text-amber-900">
                        <h4 class="font-semibold text-base mb-1">Application Under Compliance Review</h4>
                        <p class="text-sm">
                            Your verification request submitted on <strong>{{ $latestVerification->submitted_at?->format('M d, Y H:i') }}</strong> is currently under review by our commercial team. Typical verification turnaround is 1–2 business days.
                        </p>
                        <div class="mt-2.5 text-xs text-amber-900 space-y-0.5 bg-amber-100/60 p-2.5 rounded border border-amber-200">
                            <p><strong>Company:</strong> {{ $latestVerification->company_name }}</p>
                            <p><strong>Tax ID:</strong> {{ $latestVerification->tax_id }}</p>
                        </div>
                        <p class="text-sm mt-2 text-amber-800">
                            Your member portal access remains fully active. You may continue to browse wholesale product specifications and pricing ranges while review is underway.
                        </p>
                        <div class="mt-4">
                            <a href="{{ route('shop.customers.account.business_documents.index') }}" class="secondary-button text-sm px-4 py-2 border-zinc-300 bg-white">
                                View Submitted Credentials
                            </a>
                        </div>
                    </div>

                @elseif ($status === 'rejected')
                    <div class="mt-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-rose-900">
                        <h4 class="font-semibold text-base mb-1">Application Requires Attention</h4>
                        <p class="text-sm">
                            Our compliance team reviewed your application and noted the following reason:
                        </p>
                        <div class="mt-2 rounded bg-white p-3 border border-rose-200 text-sm font-medium text-rose-800">
                            {{ $latestVerification->rejection_reason ?: 'Incomplete or unreadable documentation provided.' }}
                        </div>
                        <p class="text-sm mt-3">
                            Please update your business information or attach updated documentation using the form below to resubmit.
                        </p>
                    </div>
                @else
                    <div class="mt-6 rounded-lg bg-zinc-50 border border-zinc-200 p-4 text-zinc-800">
                        <h4 class="font-semibold text-base mb-1">Why Verify Your Wholesale Account?</h4>
                        <p class="text-sm">
                            As a Registered Member, you have access to private catalog pricing and product technical overviews. To initiate formal Requests for Quote (RFQ), receive contract pricing, request physical product samples, or place purchase orders, please submit your business credentials below.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Application Form (Shown if Unverified or Rejected) -->
            @if ($status === 'unverified' || $status === 'rejected')
                <div class="rounded-xl border border-zinc-200 bg-white p-6 max-sm:p-4">
                    <h3 class="text-lg font-semibold text-zinc-900 mb-4 border-b border-zinc-200 pb-3">
                        {{ $status === 'rejected' ? 'Update & Resubmit Verification Application' : 'Submit Business Verification Credentials' }}
                    </h3>

                    <form
                        action="{{ route('shop.customers.account.buyer_verification.store') }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="flex flex-col gap-5"
                    >
                        @csrf

                        <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1 required">
                                    Legal Company / Business Name
                                </label>
                                <input
                                    type="text"
                                    name="company_name"
                                    value="{{ old('company_name', $latestVerification?->company_name) }}"
                                    required
                                    class="w-full rounded-lg border border-zinc-300 px-3.5 py-2 text-sm text-zinc-800 focus:border-zinc-500 focus:outline-none"
                                    placeholder="e.g. Pharmako Life Sciences LLC"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1 required">
                                    Business Tax ID / VAT / EIN / Reg Number
                                </label>
                                <input
                                    type="text"
                                    name="tax_id"
                                    value="{{ old('tax_id', $latestVerification?->tax_id) }}"
                                    required
                                    class="w-full rounded-lg border border-zinc-300 px-3.5 py-2 text-sm text-zinc-800 focus:border-zinc-500 focus:outline-none"
                                    placeholder="e.g. US-EIN-987654321"
                                >
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1 required">
                                    Business Type / Sector
                                </label>
                                <select
                                    name="business_type"
                                    required
                                    class="w-full rounded-lg border border-zinc-300 px-3.5 py-2 text-sm text-zinc-800 focus:border-zinc-500 focus:outline-none bg-white"
                                >
                                    <option value="">Select your business type</option>
                                    @foreach (\App\Models\BuyerVerification::BUSINESS_TYPES as $code => $label)
                                        <option
                                            value="{{ $code }}"
                                            {{ old('business_type', $latestVerification?->business_type) === $code ? 'selected' : '' }}
                                        >
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1 required">
                                    Commercial Telephone Number
                                </label>
                                <input
                                    type="text"
                                    name="business_phone"
                                    value="{{ old('business_phone', $latestVerification?->business_phone) }}"
                                    required
                                    class="w-full rounded-lg border border-zinc-300 px-3.5 py-2 text-sm text-zinc-800 focus:border-zinc-500 focus:outline-none"
                                    placeholder="e.g. +1 (555) 019-2834"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1 required">
                                Commercial Registered Address
                            </label>
                            <textarea
                                name="business_address"
                                rows="2"
                                required
                                class="w-full rounded-lg border border-zinc-300 px-3.5 py-2 text-sm text-zinc-800 focus:border-zinc-500 focus:outline-none"
                                placeholder="Registered business address, city, state/province, postal code, country"
                            >{{ old('business_address', $latestVerification?->business_address) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">
                                Company Website (Optional)
                            </label>
                            <input
                                type="url"
                                name="website_url"
                                value="{{ old('website_url', $latestVerification?->website_url) }}"
                                class="w-full rounded-lg border border-zinc-300 px-3.5 py-2 text-sm text-zinc-800 focus:border-zinc-500 focus:outline-none"
                                placeholder="https://www.example.com"
                            >
                        </div>

                        <!-- Document Upload Section -->
                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4">
                            <h4 class="text-sm font-semibold text-zinc-900 mb-2">
                                Business Credential Documents (Required)
                            </h4>
                            <p class="text-xs text-zinc-500 mb-4">
                                Please attach at least one verifiable commercial document (Business License, Tax Registration, or Resale Permit). Supported formats: PDF, JPG, PNG, WEBP (Max 10 MB per file).
                            </p>

                            <div class="flex flex-col gap-3">
                                <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1 items-center bg-white p-3 rounded-lg border border-zinc-200">
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-600 mb-1">Document 1 Type</label>
                                        <select
                                            name="document_types[0]"
                                            class="w-full rounded border border-zinc-300 px-2.5 py-1.5 text-xs text-zinc-800 focus:outline-none"
                                        >
                                            <option value="business_license">Business Registration / License</option>
                                            <option value="tax_certificate">Tax ID / VAT / GST Certificate</option>
                                            <option value="resale_permit">Resale Certificate / Permit</option>
                                            <option value="identity_proof">Authorized Signatory ID</option>
                                            <option value="other">Other Supporting Document</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-600 mb-1">Choose File</label>
                                        <input
                                            type="file"
                                            name="documents[0]"
                                            required
                                            accept=".pdf,.jpg,.jpeg,.png,.webp"
                                            class="text-xs text-zinc-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-zinc-800 file:text-white hover:file:bg-zinc-700"
                                        >
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1 items-center bg-white p-3 rounded-lg border border-zinc-200">
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-600 mb-1">Document 2 Type (Optional)</label>
                                        <select
                                            name="document_types[1]"
                                            class="w-full rounded border border-zinc-300 px-2.5 py-1.5 text-xs text-zinc-800 focus:outline-none"
                                        >
                                            <option value="tax_certificate">Tax ID / VAT / GST Certificate</option>
                                            <option value="resale_permit">Resale Certificate / Permit</option>
                                            <option value="business_license">Business Registration / License</option>
                                            <option value="identity_proof">Authorized Signatory ID</option>
                                            <option value="other">Other Supporting Document</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-600 mb-1">Choose File</label>
                                        <input
                                            type="file"
                                            name="documents[1]"
                                            accept=".pdf,.jpg,.jpeg,.png,.webp"
                                            class="text-xs text-zinc-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-zinc-800 file:text-white hover:file:bg-zinc-700"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200">
                            <button
                                type="submit"
                                class="primary-button px-6 py-2.5 text-sm"
                            >
                                {{ $status === 'rejected' ? 'Submit Corrected Application' : 'Submit Application for Review' }}
                            </button>
                        </div>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-shop::layouts.account>


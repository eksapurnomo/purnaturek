{{--
    PurNatureK Members Portal — Buyer Verification
    (shop::customers.account.buyer_verification.index)
--}}
<x-shop::layouts.account>
    <x-slot:title>
        Buyer Verification — PurNatureK Members Portal
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="pk-main pk-stack-md" v-pre>
        <!-- Flash & Validation Alerts -->
        @if (session('success'))
            <x-shop::portal.alert variant="success" title="Submission Confirmed">
                {{ session('success') }}
            </x-shop::portal.alert>
        @endif

        @if (session('warning'))
            <x-shop::portal.alert variant="warning" title="Verification Notice">
                {{ session('warning') }}
            </x-shop::portal.alert>
        @endif

        @if (isset($errors) && $errors->any())
            <x-shop::portal.alert variant="danger" title="Please correct the highlighted fields">
                <ul style="margin: 6px 0 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-shop::portal.alert>
        @endif

        <!-- Page Header -->
        <x-shop::portal.page-header
            eyebrow="Tier 2 Compliance Onboarding"
            title="Wholesale Buyer Verification"
            subtitle="Commercial credential review unlocking formal RFQs, contract pricing agreements, sample requests, and wholesale purchase orders."
        >
            <x-slot:actions>
                @if ($status === 'approved')
                    <x-shop::portal.badge variant="verified">Verified Wholesale Buyer</x-shop::portal.badge>
                @elseif ($status === 'pending')
                    <x-shop::portal.badge variant="pending">Pending Commercial Review</x-shop::portal.badge>
                @elseif ($status === 'rejected')
                    <x-shop::portal.badge variant="action">Action Required / Rejected</x-shop::portal.badge>
                @else
                    <x-shop::portal.badge variant="neutral">Tier 1 Member (Unverified)</x-shop::portal.badge>
                @endif

                <x-shop::portal.button
                    :href="route('shop.customers.account.business_documents.index')"
                    variant="secondary"
                    size="sm"
                >
                    Uploaded Credentials
                </x-shop::portal.button>
            </x-slot:actions>
        </x-shop::portal.page-header>

        <!-- Onboarding Stepper & Status Summary -->
        <x-shop::portal.card
            eyebrow="Verification Progress"
            title="Account Qualification Stages"
        >
            @php
                $step1Class = $status === 'unverified' ? 'is-current' : 'is-done';
                $step2Class = $status === 'approved' ? 'is-done' : (in_array($status, ['pending', 'rejected'], true) ? 'is-current' : '');
                $step3Class = $status === 'approved' ? 'is-done' : '';
            @endphp

            <div class="pk-stepper">
                <div class="pk-step {{ $step1Class }}">
                    <span class="pk-step__num">Stage 01</span>
                    <span class="pk-step__label">Submit Business Credentials</span>
                </div>
                <div class="pk-step {{ $step2Class }}">
                    <span class="pk-step__num">Stage 02</span>
                    <span class="pk-step__label">Commercial Team Review</span>
                </div>
                <div class="pk-step {{ $step3Class }}">
                    <span class="pk-step__num">Stage 03</span>
                    <span class="pk-step__label">Tier 2 Purchasing Unlocked</span>
                </div>
            </div>

            <div style="margin-top: 18px;">
                @if ($status === 'approved')
                    <x-shop::portal.alert variant="success" title="Your Account is Fully Verified">
                        <p style="margin: 0;">
                            Your business credentials for <strong>{{ $latestVerification->company_name }}</strong> have been verified. You hold Tier 2 access for formal RFQs, sample inquiries, commercial contracts, and approved purchase orders.
                        </p>
                        <x-slot:actions>
                            <x-shop::portal.button :href="route('shop.customers.account.index') . '#catalog'" variant="primary" size="sm">
                                Browse Wholesale Catalog
                            </x-shop::portal.button>
                            <x-shop::portal.button :href="route('shop.customers.account.business_documents.index')" variant="secondary" size="sm">
                                Business Documents
                            </x-shop::portal.button>
                        </x-slot:actions>
                    </x-shop::portal.alert>
                @elseif ($status === 'pending')
                    <x-shop::portal.alert variant="warning" title="Application Under Compliance Review">
                        <p style="margin: 0 0 10px;">
                            Submitted on <strong>{{ $latestVerification->submitted_at?->format('M d, Y H:i') }}</strong>. Typical verification turnaround is 1–2 business days. Your Tier 1 member portal access remains active so you can browse specifications and indicative wholesale pricing ranges while review is underway.
                        </p>
                        <dl class="pk-dl-list" style="margin-top: 10px;">
                            <div class="pk-dl-item">
                                <dt>Submitted Entity</dt>
                                <dd>{{ $latestVerification->company_name }}</dd>
                            </div>
                            <div class="pk-dl-item">
                                <dt>Tax ID / Registration</dt>
                                <dd class="pk-mono">{{ $latestVerification->tax_id }}</dd>
                            </div>
                        </dl>
                        <x-slot:actions>
                            <x-shop::portal.button :href="route('shop.customers.account.business_documents.index')" variant="secondary" size="sm">
                                View Submitted Credentials
                            </x-shop::portal.button>
                        </x-slot:actions>
                    </x-shop::portal.alert>
                @elseif ($status === 'rejected')
                    <x-shop::portal.alert variant="danger" title="Application Requires Correction & Resubmission">
                        <p style="margin: 0 0 8px;">
                            Our compliance team reviewed your application and recorded the following reason:
                        </p>
                        <div class="pk-note-box">
                            {{ $latestVerification->rejection_reason ?: 'Incomplete or unreadable documentation provided.' }}
                        </div>
                        <p style="margin: 8px 0 0;">
                            Please update your business details or attach clearer credential files in the form below to resubmit.
                        </p>
                    </x-shop::portal.alert>
                @else
                    <x-shop::portal.alert variant="info" title="Why Verify Your Wholesale Buyer Account?">
                        As a Tier 1 Member, you can inspect the private botanical catalog, indicative wholesale pricing ranges, and COA/TDS documentation. Completing commercial verification unlocks formal RFQs, custom contract pricing, physical sample inquiries, and purchase orders.
                    </x-shop::portal.alert>
                @endif
            </div>
        </x-shop::portal.card>

        <!-- Application Form (Unverified or Rejected) -->
        @if ($status === 'unverified' || $status === 'rejected')
            <x-shop::portal.card
                eyebrow="Credential Filing"
                :title="$status === 'rejected' ? 'Update & Resubmit Verification Application' : 'Submit Business Verification Credentials'"
                subtitle="Provide your legal entity details and upload at least one official commercial credential for verification."
            >
                <form
                    action="{{ route('shop.customers.account.buyer_verification.store') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    id="pk-buyer-verification-form"
                    data-pk-guard-submit="Submitting Application..."
                >
                    @csrf

                    <div class="pk-form-row">
                        <div class="pk-field">
                            <label for="bv_company_name" class="pk-label pk-label--required">
                                Legal Company / Business Name
                            </label>
                            <input
                                type="text"
                                id="bv_company_name"
                                name="company_name"
                                value="{{ old('company_name', $latestVerification?->company_name) }}"
                                required
                                placeholder="e.g. Pacific Botanicals Formulations LLC"
                                class="pk-input @error('company_name') pk-input--error @enderror"
                            >
                            @error('company_name')
                                <span class="pk-error-text">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="pk-field">
                            <label for="bv_tax_id" class="pk-label pk-label--required">
                                Business Tax ID / VAT / EIN / Reg Number
                            </label>
                            <input
                                type="text"
                                id="bv_tax_id"
                                name="tax_id"
                                value="{{ old('tax_id', $latestVerification?->tax_id) }}"
                                required
                                placeholder="e.g. US-EIN-98-7654321"
                                class="pk-input @error('tax_id') pk-input--error @enderror"
                            >
                            @error('tax_id')
                                <span class="pk-error-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="pk-form-row">
                        <div class="pk-field">
                            <label for="bv_business_type" class="pk-label pk-label--required">
                                Business Type / Sector
                            </label>
                            <select
                                id="bv_business_type"
                                name="business_type"
                                required
                                class="pk-select @error('business_type') pk-select--error @enderror"
                            >
                                <option value="">Select your business sector</option>
                                @foreach (\App\Models\BuyerVerification::BUSINESS_TYPES as $code => $label)
                                    <option
                                        value="{{ $code }}"
                                        {{ old('business_type', $latestVerification?->business_type) === $code ? 'selected' : '' }}
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('business_type')
                                <span class="pk-error-text">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="pk-field">
                            <label for="bv_business_phone" class="pk-label pk-label--required">
                                Commercial Telephone Number
                            </label>
                            <input
                                type="text"
                                id="bv_business_phone"
                                name="business_phone"
                                value="{{ old('business_phone', $latestVerification?->business_phone) }}"
                                required
                                placeholder="e.g. +1 (312) 555-0194"
                                class="pk-input @error('business_phone') pk-input--error @enderror"
                            >
                            @error('business_phone')
                                <span class="pk-error-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="pk-field">
                        <label for="bv_business_address" class="pk-label pk-label--required">
                            Commercial Registered Address
                        </label>
                        <textarea
                            id="bv_business_address"
                            name="business_address"
                            rows="3"
                            required
                            placeholder="Street address, suite/unit, city, state/province, postal code, country"
                            class="pk-textarea @error('business_address') pk-textarea--error @enderror"
                        >{{ old('business_address', $latestVerification?->business_address) }}</textarea>
                        @error('business_address')
                            <span class="pk-error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="pk-field">
                        <label for="bv_website_url" class="pk-label">
                            Company Website (Optional)
                        </label>
                        <input
                            type="url"
                            id="bv_website_url"
                            name="website_url"
                            value="{{ old('website_url', $latestVerification?->website_url) }}"
                            placeholder="https://www.example.com"
                            class="pk-input @error('website_url') pk-input--error @enderror"
                        >
                        @error('website_url')
                            <span class="pk-error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Credential File Upload Section -->
                    <div class="pk-upload-box">
                        <h3 class="pk-serif" style="margin: 0; font-size: 20px; font-weight: 600; color: var(--pk-primary);">
                            Business Credential Documents (Required)
                        </h3>
                        <p class="pk-help-text" style="margin: 4px 0 14px;">
                            Attach at least one verifiable commercial document (Business License, Tax Registration, or Resale Permit). Supported formats: PDF, JPG, PNG, WEBP (maximum 10 MB per file).
                        </p>

                        <div class="pk-stack-sm">
                            <div class="pk-file-slot">
                                <div class="pk-field" style="margin-bottom: 0;">
                                    <label for="bv_doc_type_0" class="pk-label pk-label--required">Document 1 Type</label>
                                    <select id="bv_doc_type_0" name="document_types[0]" class="pk-select">
                                        @php $docType0 = old('document_types.0', 'business_license'); @endphp
                                        <option value="business_license" {{ $docType0 === 'business_license' ? 'selected' : '' }}>Business Registration / License</option>
                                        <option value="tax_certificate" {{ $docType0 === 'tax_certificate' ? 'selected' : '' }}>Tax ID / VAT / GST Certificate</option>
                                        <option value="resale_permit" {{ $docType0 === 'resale_permit' ? 'selected' : '' }}>Resale Certificate / Permit</option>
                                        <option value="identity_proof" {{ $docType0 === 'identity_proof' ? 'selected' : '' }}>Authorized Signatory ID</option>
                                        <option value="other" {{ $docType0 === 'other' ? 'selected' : '' }}>Other Supporting Document</option>
                                    </select>
                                </div>

                                <div class="pk-field" style="margin-bottom: 0;">
                                    <label for="bv_doc_file_0" class="pk-label pk-label--required">Choose Credential File</label>
                                    <input
                                        type="file"
                                        id="bv_doc_file_0"
                                        name="documents[0]"
                                        required
                                        accept=".pdf,.jpg,.jpeg,.png,.webp"
                                        class="pk-file-input"
                                    >
                                </div>
                            </div>

                            <div class="pk-file-slot">
                                <div class="pk-field" style="margin-bottom: 0;">
                                    <label for="bv_doc_type_1" class="pk-label">Document 2 Type (Optional)</label>
                                    <select id="bv_doc_type_1" name="document_types[1]" class="pk-select">
                                        @php $docType1 = old('document_types.1', 'tax_certificate'); @endphp
                                        <option value="tax_certificate" {{ $docType1 === 'tax_certificate' ? 'selected' : '' }}>Tax ID / VAT / GST Certificate</option>
                                        <option value="resale_permit" {{ $docType1 === 'resale_permit' ? 'selected' : '' }}>Resale Certificate / Permit</option>
                                        <option value="business_license" {{ $docType1 === 'business_license' ? 'selected' : '' }}>Business Registration / License</option>
                                        <option value="identity_proof" {{ $docType1 === 'identity_proof' ? 'selected' : '' }}>Authorized Signatory ID</option>
                                        <option value="other" {{ $docType1 === 'other' ? 'selected' : '' }}>Other Supporting Document</option>
                                    </select>
                                </div>

                                <div class="pk-field" style="margin-bottom: 0;">
                                    <label for="bv_doc_file_1" class="pk-label">Additional File (Optional)</label>
                                    <input
                                        type="file"
                                        id="bv_doc_file_1"
                                        name="documents[1]"
                                        accept=".pdf,.jpg,.jpeg,.png,.webp"
                                        class="pk-file-input"
                                    >
                                </div>
                            </div>
                        </div>

                        @if ($errors->has('documents') || $errors->has('documents.*'))
                            <p class="pk-error-text" style="margin: 10px 0 0;">
                                {{ $errors->first('documents') ?: $errors->first('documents.*') }}
                            </p>
                        @endif
                    </div>

                    <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--pk-border); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
                        <span class="pk-help-text">
                            All uploaded credentials are encrypted at rest and reviewed strictly for trade compliance.
                        </span>

                        <x-shop::portal.button type="submit" variant="primary">
                            {{ $status === 'rejected' ? 'Submit Corrected Application' : 'Submit Application for Review' }}
                        </x-shop::portal.button>
                    </div>
                </form>
            </x-shop::portal.card>
        @endif
    </div>

    @pushOnce('scripts')
        <script>
            (function () {
                function bindVerificationGuard() {
                    var form = document.getElementById('pk-buyer-verification-form');
                    if (!form || form.dataset.pkBound) return;
                    form.dataset.pkBound = '1';

                    form.addEventListener('submit', function (e) {
                        if (form.dataset.pkSubmitted === '1') {
                            e.preventDefault();
                            return;
                        }
                        if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
                            return;
                        }
                        form.dataset.pkSubmitted = '1';
                        var btn = form.querySelector('button[type="submit"]');
                        if (btn) {
                            var label = form.getAttribute('data-pk-guard-submit') || 'Submitting...';
                            setTimeout(function () {
                                btn.disabled = true;
                                btn.textContent = label;
                            }, 0);
                        }
                    });
                }
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', bindVerificationGuard);
                } else {
                    bindVerificationGuard();
                }
            })();
        </script>
    @endPushOnce
</x-shop::layouts.account>

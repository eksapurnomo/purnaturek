<x-admin::layouts>
    <x-slot:title>
        Issue Member Contract
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div>
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                Issue Member Contract
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Generate and upload a binding commercial agreement for an individual wholesale member.
            </p>
        </div>

        <a
            href="{{ route('admin.b2b.member-contracts.index') }}"
            class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
        >
            Back to Agreements
        </a>
    </div>

    <!-- Error Messages -->
    @if (isset($errors) && $errors->any())
        <div class="mt-4 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 dark:bg-rose-950 dark:border-rose-800 dark:text-rose-300">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('admin.b2b.member-contracts.store') }}"
        enctype="multipart/form-data"
        class="mt-6"
    >
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <!-- Agreement Core Details -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white border-b border-gray-200 pb-3 dark:border-gray-800">
                        Agreement Information
                    </h2>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Target Wholesale Member <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="customer_id"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                required
                            >
                                <option value="">-- Select Member / Company Account --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->first_name }} {{ $c->last_name }} ({{ $c->email }}) [ID: #{{ $c->id }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Agreement Title <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="title"
                                value="{{ old('title') }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="e.g., Master Wholesale Supply Agreement 2026"
                                required
                            />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Associated Contract Template (Optional)
                                </label>
                                <select
                                    name="template_id"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                >
                                    <option value="">-- None / Standalone Custom Agreement --</option>
                                    @foreach($templates as $t)
                                        <option value="{{ $t->id }}" {{ old('template_id') == $t->id ? 'selected' : '' }}>
                                            {{ $t->title }} (v{{ $t->version ?: '1.0' }}) [{{ ucfirst($t->status) }}]
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-xs text-gray-500">Links this contract to a parent template version</span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Initial Status
                                </label>
                                <select
                                    name="status"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                >
                                    <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>
                                        Save as Draft (Do Not Send Yet)
                                    </option>
                                    <option value="sent" {{ old('status') === 'sent' ? 'selected' : '' }}>
                                        Issue & Send to Member Immediately
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Internal Administrator Notes
                            </label>
                            <textarea
                                name="admin_notes"
                                rows="3"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="Confidential commercial context, special negotiations, or approval references..."
                            >{{ old('admin_notes') }}</textarea>
                            <span class="text-xs text-amber-600 font-medium">Internal only: this note is never visible to the member.</span>
                        </div>
                    </div>
                </div>

                <!-- Document File Upload -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white border-b border-gray-200 pb-3 dark:border-gray-800">
                        Agreement Document File <span class="text-red-500">*</span>
                    </h2>

                    <div class="mt-4 space-y-4">
                        <div class="rounded-md bg-blue-50 p-3 text-xs text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                            <strong>Secure Document Handling:</strong> The file is stored securely on private storage outside the web root. A SHA-256 cryptographic digest will be computed upon upload to establish an immutable audit trail.
                        </div>

                        <div>
                            <input
                                type="file"
                                name="file"
                                accept=".pdf,.docx,.doc"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-gray-800 dark:file:text-emerald-400"
                                required
                            />
                            <span class="text-xs text-gray-500">Allowed formats: PDF, DOCX, DOC (max 15 MB)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dates Sidebar -->
            <div class="space-y-6">
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white border-b border-gray-200 pb-3 dark:border-gray-800">
                        Contract Schedule
                    </h2>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Issued Date
                            </label>
                            <input
                                type="date"
                                name="issued_date"
                                value="{{ old('issued_date', date('Y-m-d')) }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Effective Date
                            </label>
                            <input
                                type="date"
                                name="effective_date"
                                value="{{ old('effective_date') }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Expiration Date & Time
                            </label>
                            <input
                                type="datetime-local"
                                name="expires_at"
                                value="{{ old('expires_at') }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            <span class="text-xs text-gray-500">If unset, agreement remains open without hard expiry deadline</span>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-gray-200 pt-4 dark:border-gray-800">
                        <button
                            type="submit"
                            class="w-full primary-button"
                        >
                            Issue Commercial Contract
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-admin::layouts>


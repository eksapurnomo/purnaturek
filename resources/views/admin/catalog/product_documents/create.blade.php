<x-admin::layouts>
    <x-slot:title>
        Upload Product Document
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div>
            <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                Upload Product Document
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Attach technical documentation, COA, TDS, or quality sheets to a wholesale product.
            </p>
        </div>

        <a
            href="{{ route('admin.catalog.product_documents.index') }}"
            class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
        >
            Back to Documents
        </a>
    </div>

    <!-- Error Summary -->
    @if (isset($errors) && $errors->any())
        <div class="mt-4 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 dark:bg-rose-950 dark:border-rose-800 dark:text-rose-300">
            <p class="font-bold mb-2">Please correct the following errors:</p>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ route('admin.catalog.product_documents.store') }}"
        method="POST"
        enctype="multipart/form-data"
        class="mt-6"
    >
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main Document Metadata (2 cols) -->
            <div class="lg:col-span-2 flex flex-col gap-6">
                <!-- Core Information Card -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-4">
                        Document Details
                    </h2>

                    <div class="space-y-4">
                        <!-- Product Selection -->
                        <div>
                            <label for="product_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Associated Wholesale Product <span class="text-rose-600">*</span>
                            </label>
                            <select
                                id="product_id"
                                name="product_id"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                required
                            >
                                <option value="">Select a Product</option>
                                @foreach ($products as $p)
                                    <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->name ?: $p->sku }} (SKU: {{ $p->sku }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Document Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Document Title <span class="text-rose-600">*</span>
                            </label>
                            <input
                                type="text"
                                id="title"
                                name="title"
                                value="{{ old('title') }}"
                                placeholder="e.g. Lavender Essential Oil Batch 2026-A COA"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                required
                            >
                        </div>

                        <!-- Document Type -->
                        <div>
                            <label for="document_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Document Type / Classification <span class="text-rose-600">*</span>
                            </label>
                            <select
                                id="document_type"
                                name="document_type"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                required
                            >
                                @foreach ($documentTypes as $typeKey => $typeLabel)
                                    <option value="{{ $typeKey }}" {{ old('document_type', 'coa') === $typeKey ? 'selected' : '' }}>
                                        {{ $typeLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Description / Notes <span class="text-xs text-gray-500">(Optional)</span>
                            </label>
                            <textarea
                                id="description"
                                name="description"
                                rows="3"
                                placeholder="Brief notes on methodology, lot applicability, or laboratory testing standards..."
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- File Upload Card -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-2">
                        Document File <span class="text-rose-600">*</span>
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                        Uploaded documents are stored on a private storage disk outside public web root and can only be accessed via authenticated member tokens.
                    </p>

                    <div>
                        <input
                            type="file"
                            id="file"
                            name="file"
                            accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 dark:file:bg-gray-800 dark:file:text-green-400"
                            required
                        >
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Permitted formats: <strong>PDF, JPG, PNG, WEBP</strong>. Maximum file size: <strong>10 MB</strong>.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Settings & Publishing Sidebar (1 col) -->
            <div class="flex flex-col gap-6">
                <!-- Publishing & Visibility -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-4">
                        Publication Status
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Status <span class="text-rose-600">*</span>
                            </label>
                            <select
                                id="status"
                                name="status"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                required
                            >
                                <option value="published" {{ old('status', 'published') === 'published' ? 'selected' : '' }}>Published (Visible to Members)</option>
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft (Hidden from Members)</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Draft documents are strictly invisible to member accounts.
                            </p>
                        </div>

                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Sort Order
                            </label>
                            <input
                                type="number"
                                id="sort_order"
                                name="sort_order"
                                value="{{ old('sort_order', 0) }}"
                                min="0"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                        </div>
                    </div>
                </div>

                <!-- Version & Dates -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-4">
                        Version &amp; Lifecycle
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label for="version" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Document Version
                            </label>
                            <input
                                type="text"
                                id="version"
                                name="version"
                                value="{{ old('version') }}"
                                placeholder="e.g. v2.1 or Rev-C"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                        </div>

                        <div>
                            <label for="issue_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Issue Date
                            </label>
                            <input
                                type="date"
                                id="issue_date"
                                name="issue_date"
                                value="{{ old('issue_date') }}"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                        </div>

                        <div>
                            <label for="expiry_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Expiry Date
                            </label>
                            <input
                                type="date"
                                id="expiry_date"
                                name="expiry_date"
                                value="{{ old('expiry_date') }}"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center gap-3">
                    <button
                        type="submit"
                        class="primary-button w-full justify-center"
                    >
                        Save &amp; Publish Document
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-admin::layouts>


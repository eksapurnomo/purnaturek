<x-admin::layouts>
    <x-slot:title>
        Edit Product Document #{{ $document->id }} — {{ $document->title }}
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                    Edit Document #{{ $document->id }}
                </h1>
                @if ($document->status === 'published')
                    <span class="label-active text-xs">Published</span>
                @else
                    <span class="label-pending text-xs">Draft</span>
                @endif
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Product: <strong class="text-gray-700 dark:text-gray-300">{{ $document->product?->name ?: $document->product?->sku }}</strong> (SKU: {{ $document->product?->sku }})
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a
                href="{{ route('admin.catalog.product_documents.download', $document->id) }}"
                class="secondary-button"
            >
                Download Stored File
            </a>

            <a
                href="{{ route('admin.catalog.product_documents.index') }}"
                class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
            >
                Back to Documents
            </a>
        </div>
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
        action="{{ route('admin.catalog.product_documents.update', $document->id) }}"
        method="POST"
        enctype="multipart/form-data"
        class="mt-6"
    >
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main Document Metadata (2 cols) -->
            <div class="lg:col-span-2 flex flex-col gap-6">
                <!-- Current File Info Card -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-3">
                        Current Physical File
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-lg bg-gray-50 border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                        <div>
                            <span class="block text-xs uppercase text-gray-500 font-semibold mb-1">Original Filename</span>
                            <span class="font-mono text-sm text-gray-800 dark:text-gray-200 break-all">{{ $document->original_filename }}</span>
                        </div>
                        <div>
                            <span class="block text-xs uppercase text-gray-500 font-semibold mb-1">File Size</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $document->formatted_file_size }}</span>
                        </div>
                        <div>
                            <span class="block text-xs uppercase text-gray-500 font-semibold mb-1">MIME Type</span>
                            <span class="text-xs font-mono text-gray-600 dark:text-gray-400">{{ $document->mime_type }}</span>
                        </div>
                    </div>

                    <!-- Replace File -->
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Replace File <span class="text-xs text-gray-500 font-normal">(Leave blank to keep existing file)</span>
                        </label>
                        <input
                            type="file"
                            id="file"
                            name="file"
                            accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 dark:file:bg-gray-800 dark:file:text-green-400"
                        >
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Uploading a new file will automatically replace and permanently remove the old file from private storage.
                        </p>
                    </div>
                </div>

                <!-- Core Information Card -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-4">
                        Document Metadata
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
                                @foreach ($products as $p)
                                    <option value="{{ $p->id }}" {{ (old('product_id', $document->product_id) == $p->id) ? 'selected' : '' }}>
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
                                value="{{ old('title', $document->title) }}"
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
                                    <option value="{{ $typeKey }}" {{ (old('document_type', $document->document_type) === $typeKey) ? 'selected' : '' }}>
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
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >{{ old('description', $document->description) }}</textarea>
                        </div>
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
                                <option value="published" {{ old('status', $document->status) === 'published' ? 'selected' : '' }}>Published (Visible to Members)</option>
                                <option value="draft" {{ old('status', $document->status) === 'draft' ? 'selected' : '' }}>Draft (Hidden from Members)</option>
                            </select>
                        </div>

                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Sort Order
                            </label>
                            <input
                                type="number"
                                id="sort_order"
                                name="sort_order"
                                value="{{ old('sort_order', $document->sort_order) }}"
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
                                value="{{ old('version', $document->version) }}"
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
                                value="{{ old('issue_date', $document->issue_date?->format('Y-m-d')) }}"
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
                                value="{{ old('expiry_date', $document->expiry_date?->format('Y-m-d')) }}"
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
                        Update Document
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-admin::layouts>


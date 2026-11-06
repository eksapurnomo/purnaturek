<x-admin::layouts>
    <x-slot:title>
        Edit Contract Template — {{ $template->title }}
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div>
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                Edit Contract Template
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Update template details, publish or archive, and manage reference document files.
            </p>
        </div>

        <a
            href="{{ route('admin.b2b.contract-templates.index') }}"
            class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
        >
            Back to Templates
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
        action="{{ route('admin.b2b.contract-templates.update', $template->id) }}"
        enctype="multipart/form-data"
        class="mt-6"
    >
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <!-- Main Information -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white border-b border-gray-200 pb-3 dark:border-gray-800">
                        Template Details
                    </h2>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Template Title <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="title"
                                value="{{ old('title', $template->title) }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                required
                            />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Category <span class="text-red-500">*</span>
                                </label>
                                <select
                                    name="category"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    required
                                >
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}" {{ old('category', $template->category) === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Identifier (Slug)
                                </label>
                                <input
                                    type="text"
                                    name="identifier"
                                    value="{{ old('identifier', $template->identifier) }}"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Description
                            </label>
                            <textarea
                                name="description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >{{ old('description', $template->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Document File Upload -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white border-b border-gray-200 pb-3 dark:border-gray-800">
                        Template Document File
                    </h2>

                    <div class="mt-4 space-y-4">
                        @if($template->hasFile())
                            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white font-mono">
                                        {{ $template->original_filename }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $template->formattedFileSize() }} &bull; {{ $template->mime_type }}
                                    </p>
                                </div>

                                <a
                                    href="{{ route('admin.b2b.contract-templates.download', $template->id) }}"
                                    class="secondary-button text-xs"
                                >
                                    Download Current File
                                </a>
                            </div>
                        @else
                            <div class="rounded-md bg-amber-50 p-3 text-xs text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                <strong>Pending File:</strong> No document file is currently attached to this template. Upload an approved PDF, DOCX, or DOC file below.
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $template->hasFile() ? 'Replace Document File' : 'Upload Document File' }}
                            </label>
                            <input
                                type="file"
                                name="file"
                                accept=".pdf,.docx,.doc"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-gray-800 dark:file:text-emerald-400"
                            />
                            <span class="text-xs text-gray-500">Leave blank to retain existing file. Formats: PDF, DOCX, DOC (max 15 MB)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Sidebar -->
            <div class="space-y-6">
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white border-b border-gray-200 pb-3 dark:border-gray-800">
                        Settings & Visibility
                    </h2>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Template Version
                            </label>
                            <input
                                type="text"
                                name="version"
                                value="{{ old('version', $template->version) }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="status"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                required
                            >
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ old('status', $template->status) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Effective Date
                            </label>
                            <input
                                type="date"
                                name="effective_date"
                                value="{{ old('effective_date', $template->effective_date ? $template->effective_date->format('Y-m-d') : '') }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                        </div>

                        <div class="pt-2">
                            <label class="inline-flex items-center">
                                <input
                                    type="checkbox"
                                    name="member_accessible"
                                    value="1"
                                    {{ old('member_accessible', $template->member_accessible) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800"
                                />
                                <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Visible to Members (Reference Library)
                                </span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1 pl-6">
                                If enabled, authenticated wholesale members can view and download reference copies from the portal.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-gray-200 pt-4 dark:border-gray-800">
                        <button
                            type="submit"
                            class="w-full primary-button"
                        >
                            Update Contract Template
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-admin::layouts>


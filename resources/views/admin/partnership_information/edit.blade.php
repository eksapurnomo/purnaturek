<x-admin::layouts>
    <x-slot:title>
        Wholesale Partnership Information — Administration
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div>
            <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                Wholesale Partnership Information
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Manage member-facing wholesale buyer models, distributor cooperation, manufacturing partner arrangements, onboarding steps, and commercial models.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a
                href="{{ route('admin.b2b.partnership_information.preview') }}"
                target="_blank"
                class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800 flex items-center gap-1.5"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Preview Member Page
            </a>

            <button
                type="submit"
                form="partnership-information-form"
                class="primary-button"
            >
                Save Changes
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="mt-4 rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 dark:bg-emerald-950 dark:border-emerald-800 dark:text-emerald-300">
            {{ session('success') }}
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

    <form
        id="partnership-information-form"
        method="POST"
        action="{{ route('admin.b2b.partnership_information.update') }}"
        class="mt-6"
    >
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left Main Area: Sections -->
            <div class="lg:col-span-2 space-y-6">

                @foreach($page->sections as $index => $section)
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <input type="hidden" name="sections[{{ $index }}][id]" value="{{ $section->id }}">

                        <div class="flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-800">
                            <div class="flex items-center gap-3">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300">
                                    {{ $index + 1 }}
                                </span>
                                <h2 class="text-base font-semibold text-gray-800 dark:text-white">
                                    {{ $section->title }}
                                </h2>
                                <span class="font-mono text-xs text-gray-400">({{ $section->section_key }})</span>
                            </div>

                            <label class="inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="sections[{{ $index }}][is_enabled]"
                                    value="1"
                                    {{ old("sections.{$index}.is_enabled", $section->is_enabled) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800"
                                />
                                <span class="ml-2 text-xs font-medium text-gray-600 dark:text-gray-300">
                                    Section Enabled
                                </span>
                            </label>
                        </div>

                        <!-- Guidance Box for Admin -->
                        @if($section->guidance_notes)
                            <div class="mt-3 rounded-md bg-blue-50/70 p-3 text-xs text-blue-800 border border-blue-100 dark:bg-blue-950/40 dark:border-blue-900 dark:text-blue-300">
                                <strong>Admin Content Scope:</strong> {{ $section->guidance_notes }}
                            </div>
                        @endif

                        <div class="mt-4 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="md:col-span-3">
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Display Title <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="sections[{{ $index }}][title]"
                                        value="{{ old("sections.{$index}.title", $section->title) }}"
                                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Display Order
                                    </label>
                                    <input
                                        type="number"
                                        name="sections[{{ $index }}][sort_order]"
                                        value="{{ old("sections.{$index}.sort_order", $section->sort_order) }}"
                                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                        min="0"
                                        max="999"
                                        required
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Section Content (Leave empty if not yet finalized)
                                </label>
                                <textarea
                                    name="sections[{{ $index }}][content]"
                                    rows="6"
                                    class="mt-1 block w-full rounded-md border border-gray-300 p-3 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white font-sans leading-relaxed"
                                    placeholder="Enter accurate, administrator-verified information for this partnership section. Empty sections will remain hidden from members even when enabled."
                                >{{ old("sections.{$index}.content", $section->content) }}</textarea>
                                <span class="text-[11px] text-gray-500">Supports standard paragraphs (separate by blank line) and bullet points (lines starting with - or *). Content is strictly escaped for stored XSS safety.</span>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>

            <!-- Right Sidebar: Page Settings -->
            <div class="space-y-6">
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 sticky top-6">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white border-b border-gray-200 pb-3 dark:border-gray-800">
                        Publishing Status
                    </h2>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Page Title <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="title"
                                value="{{ old('title', $page->title) }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                required
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Overall Status <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="status"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                required
                            >
                                @foreach($statuses as $k => $label)
                                    <option value="{{ $k }}" {{ old('status', $page->status) === $k ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                When set to <strong>Draft</strong> or <strong>Unpublished</strong>, members see an informative preparation notice rather than incomplete terms.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Introductory Subtitle
                            </label>
                            <textarea
                                name="description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="Lead introduction text for authenticated wholesale members..."
                            >{{ old('description', $page->description) }}</textarea>
                        </div>

                        <div class="border-t border-gray-200 pt-3 text-xs text-gray-500 dark:border-gray-800 space-y-1">
                            <p><strong>Published Date:</strong> {{ $page->published_at ? $page->published_at->format('M d, Y H:i T') : 'Not published' }}</p>
                            <p><strong>Last Updated:</strong> {{ $page->updated_at ? $page->updated_at->format('M d, Y H:i T') : '—' }}</p>
                            @if($page->updater)
                                <p><strong>Updated By:</strong> {{ $page->updater->name }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 border-t border-gray-200 pt-4 dark:border-gray-800 space-y-3">
                        <button
                            type="submit"
                            class="w-full primary-button"
                        >
                            Save Changes
                        </button>

                        <a
                            href="{{ route('admin.b2b.partnership_information.preview') }}"
                            target="_blank"
                            class="w-full text-center block transparent-button hover:bg-gray-100 dark:hover:bg-gray-800 text-xs"
                        >
                            Open Preview Tab &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-admin::layouts>


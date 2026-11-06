<x-admin::layouts>
    <x-slot:title>
        Admin Preview — Wholesale Partnership Information
    </x-slot>

    <!-- Admin Preview Banner -->
    <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4 text-blue-900 dark:border-blue-800 dark:bg-blue-950 dark:text-blue-200 flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-200 text-blue-800 font-bold dark:bg-blue-800 dark:text-blue-200">
                &eye;
            </span>
            <div>
                <p class="font-bold text-sm">
                    Administrative Preview Mode (Current Status: <span class="uppercase tracking-wider font-mono">{{ $page->status }}</span>)
                </p>
                <p class="text-xs text-blue-700 dark:text-blue-300">
                    This preview displays enabled sections with content. Disabled or empty sections are excluded from member view.
                </p>
            </div>
        </div>

        <a
            href="{{ route('admin.b2b.partnership_information.edit') }}"
            class="secondary-button text-xs"
        >
            &larr; Back to Editor
        </a>
    </div>

    <!-- Rendered Page Container -->
    <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-800 dark:bg-gray-900 max-w-5xl mx-auto">
        <div class="border-b border-gray-200 pb-6 dark:border-gray-800">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $page->title }}</h1>
            @if($page->description)
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 max-w-3xl leading-relaxed">
                    {{ $page->description }}
                </p>
            @endif
        </div>

        @if($sections->isEmpty())
            <div class="py-12 text-center text-gray-400">
                <p class="font-medium text-base text-gray-600 dark:text-gray-300">No Partnership Content Published Yet</p>
                <p class="text-xs text-gray-400 mt-1">Populate content in the editor and ensure sections are enabled.</p>
            </div>
        @else
            <div class="mt-8 space-y-10">
                @foreach($sections as $section)
                    @if($section->hasContent())
                        <section class="border-b border-gray-100 pb-8 last:border-b-0 dark:border-gray-800">
                            <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2 mb-4">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                {{ $section->title }}
                            </h2>

                            <div class="prose max-w-none text-sm text-gray-700 dark:text-gray-300">
                                {!! \App\Support\WholesalePartnershipManager::formatSafeContent($section->content) !!}
                            </div>
                        </section>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</x-admin::layouts>


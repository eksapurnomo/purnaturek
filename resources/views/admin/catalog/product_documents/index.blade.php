<x-admin::layouts>
    <x-slot:title>
        Product Documents
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div>
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                Product Documents
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Manage, publish, and audit product specifications, COAs, TDS, and technical documentation for wholesale members.
            </p>
        </div>

        <a
            href="{{ route('admin.catalog.product_documents.create') }}"
            class="primary-button"
        >
            Upload New Document
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

    <div class="mt-6">
        <x-admin::datagrid :src="route('admin.catalog.product_documents.index')" />
    </div>
</x-admin::layouts>


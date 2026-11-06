<x-admin::layouts>
    <x-slot:title>
        Member Contracts & Agreements
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div>
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                Member Contracts & Commercial Agreements
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Issued commercial contracts, electronic acknowledgments, and immutable audit logs.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a
                href="{{ route('admin.b2b.contract-templates.index') }}"
                class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
            >
                Contract Templates
            </a>

            <a
                href="{{ route('admin.b2b.member-contracts.create') }}"
                class="primary-button"
            >
                Issue New Contract
            </a>
        </div>
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
        <x-admin::datagrid :src="route('admin.b2b.member-contracts.index')" />
    </div>
</x-admin::layouts>


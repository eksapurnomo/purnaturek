<x-shop::layouts.account>
    <x-slot:title>
        New Inquiry — Message Admin
    </x-slot>

    <x-shop::layouts.account.navigation />

    <div class="pk-main">
        <div class="flex flex-col gap-6">

            <!-- Breadcrumb / Back -->
            <div>
                <a
                    href="{{ route('shop.customers.account.conversations.index') }}"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-700 hover:text-emerald-900"
                >
                    &larr; Back to Conversations
                </a>
            </div>

            <!-- Error Summary -->
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

            <!-- Form Card -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 max-sm:p-4">
                <div class="border-b border-zinc-200 pb-4 mb-6">
                    <h1 class="text-xl font-bold text-zinc-900">Start a New Conversation</h1>
                    <p class="text-sm text-zinc-500 mt-1">
                        Send a message directly to PurNatureK administrators regarding products, pricing, supply, or documentation.
                    </p>
                </div>

                <form
                    action="{{ route('shop.customers.account.conversations.store') }}"
                    method="POST"
                    class="space-y-6"
                >
                    @csrf

                    <!-- Subject -->
                    <div>
                        <label for="subject" class="block text-sm font-semibold text-zinc-700 mb-1">
                            Inquiry Subject <span class="text-rose-600">*</span>
                        </label>
                        <input
                            type="text"
                            id="subject"
                            name="subject"
                            value="{{ old('subject', $prefilledSubject) }}"
                            placeholder="e.g. Inquiring regarding bulk delivery timeline for Lavender Essential Oil"
                            class="w-full rounded-lg border border-zinc-300 px-4 py-2.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                            required
                        >
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-sm font-semibold text-zinc-700 mb-1">
                            Inquiry Category <span class="text-rose-600">*</span>
                        </label>
                        <select
                            id="category"
                            name="category"
                            class="w-full rounded-lg border border-zinc-300 px-4 py-2.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                            required
                        >
                            @foreach ($categories as $catKey => $catLabel)
                                <option value="{{ $catKey }}" {{ old('category', $prefilledCategory) === $catKey ? 'selected' : '' }}>
                                    {{ $catLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Message Body -->
                    <div>
                        <label for="message" class="block text-sm font-semibold text-zinc-700 mb-1">
                            Message <span class="text-rose-600">*</span>
                        </label>
                        <textarea
                            id="message"
                            name="message"
                            rows="6"
                            placeholder="Please describe your wholesale inquiry or requirements in detail..."
                            class="w-full rounded-lg border border-zinc-300 px-4 py-2.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                            required
                        >{{ old('message') }}</textarea>
                        <p class="mt-1.5 text-xs text-zinc-400">
                            Minimum 5 characters, up to 5,000 characters. Text-only message.
                        </p>
                    </div>

                    <!-- Submit Actions -->
                    <div class="flex items-center gap-4 pt-2">
                        <button
                            type="submit"
                            class="rounded-lg bg-emerald-700 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800"
                        >
                            Send Message
                        </button>

                        <a
                            href="{{ route('shop.customers.account.conversations.index') }}"
                            class="text-sm font-semibold text-zinc-600 hover:text-zinc-800"
                        >
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-shop::layouts.account>


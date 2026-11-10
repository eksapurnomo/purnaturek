{{--
    PurNatureK Members Portal — New Inquiry Conversation
    (shop::customers.account.conversations.create)
--}}
<x-shop::layouts.account>
    <x-slot:title>
        New Inquiry — Message Admin
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="pk-main pk-stack-md" v-pre>
        <div>
            <a href="{{ route('shop.customers.account.conversations.index') }}" class="pk-back-link">
                &larr; Back to Conversations
            </a>
        </div>

        @if (isset($errors) && $errors->any())
            <x-shop::portal.alert variant="danger" title="Please correct the following errors">
                <ul style="margin: 6px 0 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-shop::portal.alert>
        @endif

        <x-shop::portal.page-header
            eyebrow="Direct Wholesale Desk"
            title="Start a New Conversation"
            subtitle="Send a private message directly to PurNatureK administration regarding botanical specifications, commercial pricing, or trade documentation."
        />

        <x-shop::portal.card
            eyebrow="Inquiry Details"
            title="Compose Wholesale Inquiry"
        >
            <form
                action="{{ route('shop.customers.account.conversations.store') }}"
                method="POST"
                id="pk-conversation-create-form"
                data-pk-guard-submit="Sending Message..."
            >
                @csrf

                <div class="pk-field">
                    <label for="subject" class="pk-label pk-label--required">
                        Inquiry Subject
                    </label>
                    <input
                        type="text"
                        id="subject"
                        name="subject"
                        value="{{ old('subject', $prefilledSubject) }}"
                        placeholder="e.g. Requesting batch COA and 100kg lead time for botanical extract"
                        class="pk-input @error('subject') pk-input--error @enderror"
                        required
                    >
                    @error('subject')
                        <span class="pk-error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="pk-field">
                    <label for="category" class="pk-label pk-label--required">
                        Inquiry Category
                    </label>
                    <select
                        id="category"
                        name="category"
                        class="pk-select @error('category') pk-select--error @enderror"
                        required
                    >
                        @foreach ($categories as $catKey => $catLabel)
                            <option value="{{ $catKey }}" {{ old('category', $prefilledCategory) === $catKey ? 'selected' : '' }}>
                                {{ $catLabel }}
                            </option>
                        @endforeach
                    </select>
                    @error('category')
                        <span class="pk-error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="pk-field">
                    <label for="message" class="pk-label pk-label--required">
                        Message
                    </label>
                    <textarea
                        id="message"
                        name="message"
                        rows="6"
                        placeholder="Please describe your wholesale volume, destination, documentation, or formulation requirements..."
                        class="pk-textarea @error('message') pk-textarea--error @enderror"
                        required
                    >{{ old('message') }}</textarea>
                    <span class="pk-help-text">
                        Minimum 5 characters, up to 5,000 characters. Plain-text inquiry thread.
                    </span>
                    @error('message')
                        <span class="pk-error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--pk-border); display: flex; flex-wrap: wrap; align-items: center; gap: 12px;">
                    <x-shop::portal.button type="submit" variant="primary">
                        Send Message
                    </x-shop::portal.button>

                    <x-shop::portal.button
                        :href="route('shop.customers.account.conversations.index')"
                        variant="secondary"
                    >
                        Cancel
                    </x-shop::portal.button>
                </div>
            </form>
        </x-shop::portal.card>
    </div>

    @pushOnce('scripts')
        <script>
            (function () {
                function bindCreateGuard() {
                    var form = document.getElementById('pk-conversation-create-form');
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
                            setTimeout(function () {
                                btn.disabled = true;
                                btn.textContent = form.getAttribute('data-pk-guard-submit') || 'Sending...';
                            }, 0);
                        }
                    });
                }
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', bindCreateGuard);
                } else {
                    bindCreateGuard();
                }
            })();
        </script>
    @endPushOnce
</x-shop::layouts.account>

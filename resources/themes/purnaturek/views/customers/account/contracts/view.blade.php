{{--
    PurNatureK Members Portal — Contract Detail & Electronic Acknowledgment
    (shop::customers.account.contracts.view)
--}}
<x-shop::layouts.account>
    <x-slot:title>
        {{ $contract->reference_number }} — {{ $contract->title }}
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="pk-main pk-stack-md" v-pre>
        <div>
            <a href="{{ route('shop.customers.account.contracts.index') }}" class="pk-back-link">
                &larr; Back to Contracts &amp; Agreements
            </a>
        </div>

        <!-- Flash & Validation Messages -->
        @if (session('success'))
            <x-shop::portal.alert variant="success">
                {{ session('success') }}
            </x-shop::portal.alert>
        @endif

        @if (session('info'))
            <x-shop::portal.alert variant="info">
                {{ session('info') }}
            </x-shop::portal.alert>
        @endif

        @if (session('warning'))
            <x-shop::portal.alert variant="warning">
                {{ session('warning') }}
            </x-shop::portal.alert>
        @endif

        @if (isset($errors) && $errors->any())
            <x-shop::portal.alert variant="danger" title="Submission Error">
                <ul style="margin: 6px 0 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-shop::portal.alert>
        @endif

        <!-- Agreement Header & Metadata Card -->
        <x-shop::portal.card
            :eyebrow="'Reference ' . $contract->reference_number"
            :title="$contract->title"
        >
            <x-slot:actions>
                @if ($contract->status === 'accepted')
                    <x-shop::portal.badge variant="verified">Accepted</x-shop::portal.badge>
                @elseif ($contract->status === 'sent')
                    <x-shop::portal.badge variant="pending">Pending Your Review</x-shop::portal.badge>
                @elseif ($contract->status === 'rejected')
                    <x-shop::portal.badge variant="action">Declined</x-shop::portal.badge>
                @elseif ($contract->status === 'expired')
                    <x-shop::portal.badge variant="neutral">Expired</x-shop::portal.badge>
                @else
                    <x-shop::portal.badge variant="neutral">{{ ucfirst($contract->status) }}</x-shop::portal.badge>
                @endif

                <x-shop::portal.button
                    :href="route('shop.customers.account.contracts.download', $contract->id)"
                    variant="primary"
                    size="sm"
                >
                    Download Agreement Document
                </x-shop::portal.button>
            </x-slot:actions>

            <!-- Cryptographic & Commercial Metadata -->
            <dl class="pk-dl-list">
                <div class="pk-dl-item">
                    <dt>Template Reference</dt>
                    <dd>
                        @if ($contract->template)
                            {{ $contract->template->title }} (v{{ $contract->template_version ?? '1.0' }})
                        @else
                            Individual Commercial Agreement
                        @endif
                    </dd>
                </div>

                <div class="pk-dl-item">
                    <dt>Issued Date</dt>
                    <dd>{{ $contract->issued_date ? $contract->issued_date->format('M d, Y') : '—' }}</dd>
                </div>

                <div class="pk-dl-item">
                    <dt>Response Deadline</dt>
                    <dd>
                        @if ($contract->expires_at)
                            <span style="color: {{ $contract->expires_at->isPast() ? 'var(--pk-danger-ink)' : 'inherit' }};">
                                {{ $contract->expires_at->format('M d, Y H:i T') }}
                            </span>
                        @else
                            <span style="color: var(--pk-muted);">No Hard Expiry</span>
                        @endif
                    </dd>
                </div>

                <div class="pk-dl-item">
                    <dt>Effective Date</dt>
                    <dd>{{ $contract->effective_date ? $contract->effective_date->format('M d, Y') : 'Upon Acceptance' }}</dd>
                </div>

                <div class="pk-dl-item">
                    <dt>Attached Document</dt>
                    <dd class="pk-mono" style="font-size: 12px;">
                        {{ $contract->original_filename }} ({{ $contract->formattedFileSize() }})
                    </dd>
                </div>

                <div class="pk-dl-item">
                    <dt>SHA-256 Digest</dt>
                    <dd class="pk-mono" style="font-size: 12px;" title="{{ $contract->file_hash }}">
                        {{ substr($contract->file_hash, 0, 22) }}...
                    </dd>
                </div>
            </dl>

            <!-- Status / Member Action Section -->
            <div style="margin-top: 22px;">
                @if ($contract->status === 'accepted')
                    <x-shop::portal.alert variant="success" title="Agreement Acknowledged & Accepted">
                        <p style="margin: 0;">
                            Recorded on <strong>{{ $contract->responded_at ? $contract->responded_at->format('M d, Y H:i:s T') : 'record' }}</strong>.
                        </p>

                        @if (! empty($contract->member_notes))
                            <div class="pk-note-box">
                                <div class="pk-mono" style="font-size: 10px; text-transform: uppercase; color: var(--pk-muted); margin-bottom: 4px;">
                                    Your Submitted Notes
                                </div>
                                <div style="white-space: pre-line;">{{ $contract->member_notes }}</div>
                            </div>
                        @endif

                        <p style="margin: 10px 0 0; font-size: 11.5px; color: var(--pk-muted);">
                            Acknowledgment archived with cryptographic document digest <code class="pk-mono">{{ $contract->file_hash }}</code>.
                        </p>
                    </x-shop::portal.alert>
                @elseif ($contract->status === 'rejected')
                    <x-shop::portal.alert variant="danger" title="Agreement Declined">
                        <p style="margin: 0;">
                            You declined this agreement on <strong>{{ $contract->responded_at ? $contract->responded_at->format('M d, Y H:i:s T') : 'record' }}</strong>.
                        </p>

                        @if (! empty($contract->member_notes))
                            <div class="pk-note-box">
                                <div class="pk-mono" style="font-size: 10px; text-transform: uppercase; color: var(--pk-muted); margin-bottom: 4px;">
                                    Reason / Feedback
                                </div>
                                <div style="white-space: pre-line;">{{ $contract->member_notes }}</div>
                            </div>
                        @endif
                    </x-shop::portal.alert>
                @elseif ($contract->status === 'expired')
                    <x-shop::portal.alert variant="info" title="Agreement Expired">
                        This agreement expired on {{ $contract->expires_at ? $contract->expires_at->format('M d, Y H:i T') : 'a prior date' }} and can no longer be electronically acknowledged. Please contact PurNatureK administration if you require an updated agreement.
                    </x-shop::portal.alert>
                @elseif ($contract->canBeActionedByMember())
                    <x-shop::portal.card
                        alt
                        eyebrow="Electronic Execution"
                        title="Review & Electronic Acknowledgment"
                        subtitle="Please download and inspect the agreement document carefully before submitting your decision."
                    >
                        <form
                            method="POST"
                            action="{{ route('shop.customers.account.contracts.action', $contract->id) }}"
                            id="pk-contract-action-form"
                        >
                            @csrf

                            <div class="pk-field">
                                <label for="contract_member_notes" class="pk-label">
                                    Member Notes / Comments (Optional)
                                </label>
                                <textarea
                                    id="contract_member_notes"
                                    name="notes"
                                    rows="3"
                                    placeholder="Add purchase order references, authorized corporate signatory title, or comments..."
                                    class="pk-textarea"
                                >{{ old('notes') }}</textarea>
                            </div>

                            <div class="pk-legal-box">
                                <label class="pk-checkbox-row" for="terms_acknowledge">
                                    <input
                                        type="checkbox"
                                        id="terms_acknowledge"
                                        required
                                    />
                                    <span>
                                        <strong>Authorized Acknowledgment:</strong> I confirm that I am an authorized commercial representative of my organization and that I have read, understood, and accept the contractual terms set forth in document <strong>{{ $contract->original_filename }}</strong>. I understand that this electronic action is registered as an application-level acknowledgment with PurNatureK.
                                    </span>
                                </label>

                                <p class="pk-legal-box__notice">
                                    <em>Legal Notice:</em> Agreement acceptance records your customer identity, IP address, timestamp, and document cryptographic SHA-256 hash.
                                </p>
                            </div>

                            <div style="margin-top: 18px; display: flex; flex-wrap: wrap; align-items: center; gap: 12px;">
                                <x-shop::portal.button
                                    type="submit"
                                    name="action"
                                    value="accept"
                                    variant="primary"
                                    onclick="return confirm('Confirm acceptance of this commercial agreement?');"
                                >
                                    Accept &amp; Acknowledge Agreement
                                </x-shop::portal.button>

                                <x-shop::portal.button
                                    type="submit"
                                    name="action"
                                    value="reject"
                                    formnovalidate
                                    variant="danger"
                                    onclick="return confirm('Are you sure you wish to decline this agreement?');"
                                >
                                    Decline Agreement
                                </x-shop::portal.button>
                            </div>
                        </form>
                    </x-shop::portal.card>
                @else
                    <x-shop::portal.alert variant="info">
                        Status: <strong>{{ ucfirst($contract->status) }}</strong>. This agreement is not currently pending your acknowledgment.
                    </x-shop::portal.alert>
                @endif
            </div>
        </x-shop::portal.card>

        <!-- Audit Timeline -->
        <x-shop::portal.card
            eyebrow="Audit Trail"
            title="Agreement Activity Log"
        >
            @if ($contract->events->isEmpty())
                <p style="margin: 0; font-size: 13px; color: var(--pk-muted);">No activity logged yet.</p>
            @else
                <div class="pk-timeline">
                    @foreach ($contract->events as $event)
                        <div class="pk-timeline__item">
                            <div class="pk-timeline__row">
                                <div>
                                    <strong style="color: var(--pk-primary);">
                                        {{ \App\Models\MemberContractEvent::EVENT_LABELS[$event->event] ?? ucfirst($event->event) }}
                                    </strong>
                                    <span style="color: var(--pk-muted);">
                                        by {{ ucfirst($event->actor_type) }}
                                    </span>
                                    @if ($event->notes)
                                        <p style="margin: 4px 0 0; color: var(--pk-ink-soft);">{{ $event->notes }}</p>
                                    @endif
                                </div>
                                <span class="pk-mono" style="font-size: 11px; color: var(--pk-muted); white-space: nowrap;">
                                    {{ $event->created_at ? $event->created_at->format('M d, Y H:i T') : '—' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-shop::portal.card>
    </div>

    @pushOnce('scripts')
        <script>
            (function () {
                function bindContractFormGuard() {
                    var form = document.getElementById('pk-contract-action-form');
                    if (!form || form.dataset.pkBound) return;
                    form.dataset.pkBound = '1';

                    form.addEventListener('submit', function (e) {
                        if (form.dataset.pkSubmitted === '1') {
                            e.preventDefault();
                            return;
                        }
                        var submitter = e.submitter || null;
                        if (
                            submitter &&
                            submitter.value === 'accept' &&
                            typeof form.checkValidity === 'function' &&
                            !form.checkValidity()
                        ) {
                            return;
                        }
                        form.dataset.pkSubmitted = '1';
                        setTimeout(function () {
                            var buttons = form.querySelectorAll('button[type="submit"]');
                            buttons.forEach(function (btn) {
                                btn.disabled = true;
                            });
                            if (submitter) {
                                submitter.textContent = submitter.value === 'reject' ? 'Declining...' : 'Recording Acceptance...';
                            }
                        }, 0);
                    });
                }
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', bindContractFormGuard);
                } else {
                    bindContractFormGuard();
                }
            })();
        </script>
    @endPushOnce
</x-shop::layouts.account>

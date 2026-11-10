@php
    /**
     * Decide which account type tab should be open on load. Priority:
     *   1. The `account_type` from the last (failed) submission so errors stay in context.
     *   2. The `?type=company` query param used by sign-in page links.
     *   3. Default to the personal/member account tab.
     */
    $activeTab = old('account_type', request('type') === 'company' ? 'company' : 'personal');
    $b2bActive = (bool) core()->getConfigData('b2b.general.settings.active');
@endphp

@push('meta')
    <meta name="keywords" content="PurNatureK, wholesale application, b2b registration, bulk botanicals, commercial sourcing"/>
@endPush

<x-shop::portal.auth-shell
    title="Wholesale Member Registration — PurNatureK"
    meta-description="Register for PurNatureK Wholesale Members Portal — Commercial botanical powders, extracts, and verified B2B sourcing."
    eyebrow="Wholesale Onboarding"
    heading="Create Wholesale Account"
    subtitle="Build your wholesale partnership with verified sourcing for Mitragyna speciosa powder and extract."
    visual-eyebrow="PurNatureK B2B Sourcing"
    visual-heading="Quality. Compliance. Direct B2B Partnership."
    visual-lead="Direct commercial access to standardized botanical powders, active extracts, and tailored supply schedules for commercial formulators, botanical brands, and wholesale distributors."
>
    <x-slot:topbarRight>
        <div class="flex items-center gap-3 text-xs">
            <span class="hidden sm:inline pk-text-muted">Already a member?</span>
            <a href="{{ route('shop.customer.session.index') }}" class="pk-btn pk-btn--secondary pk-btn--sm">
                Sign In &rarr;
            </a>
            <a
                href="https://purnaturekbulk.com/"
                target="_blank"
                rel="noopener noreferrer"
                class="hidden md:inline-flex pk-header__link"
            >
                Corporate Site &rarr;
            </a>
        </div>
    </x-slot>

    @if ($b2bActive)
        <div class="pk-segment" role="tablist" aria-label="Account Registration Type">
            <button
                type="button"
                id="tab-personal"
                onclick="pkSwitchRegisterTab('personal')"
                class="pk-segment__btn {{ $activeTab === 'personal' ? 'is-active pk-segment--active' : '' }}"
                role="tab"
                aria-selected="{{ $activeTab === 'personal' ? 'true' : 'false' }}"
            >
                <span>Member Account</span>
                <span class="pk-segment__sub">Instant Tier 1 Access</span>
            </button>

            <button
                type="button"
                id="tab-company"
                onclick="pkSwitchRegisterTab('company')"
                class="pk-segment__btn {{ $activeTab === 'company' ? 'is-active pk-segment--active' : '' }}"
                role="tab"
                aria-selected="{{ $activeTab === 'company' ? 'true' : 'false' }}"
            >
                <span>Corporate Entity</span>
                <span class="pk-segment__sub">B2B Company Profile</span>
            </button>
        </div>
    @endif

    <div class="pk-tier-box">
        <p class="pk-tier-box__title">Two-Tier Membership Structure</p>
        <p class="pk-tier-box__row">
            <x-shop::portal.badge variant="verified" size="sm">Tier 1</x-shop::portal.badge>
            <strong>Registered Member:</strong> Instant access to Wholesale Product Specifications, Supply &amp; Product Documentation, and Direct Commercial Communication.
        </p>
        <p class="pk-tier-box__row">
            <x-shop::portal.badge variant="earth" size="sm">Tier 2</x-shop::portal.badge>
            <strong>Verified Wholesale Buyer:</strong> Unlocks quotation workflows, commercial RFQs, and purchase orders upon admin review.
        </p>
    </div>

    {{-- ============================================================== --}}
    {{-- PANEL 1: Member Account Form                                   --}}
    {{-- ============================================================== --}}
    <div id="panel-personal" class="{{ $activeTab === 'personal' ? '' : 'hidden' }}">
        <x-shop::form :action="route('shop.customers.register.store')">
            <input type="hidden" name="account_type" value="personal">

            {!! view_render_event('bagisto.shop.customers.signup_form_controls.before') !!}

            <div class="pk-form-row">
                <x-shop::form.control-group class="pk-field">
                    <x-shop::form.control-group.label class="required pk-label">
                        First Name
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="text"
                        class="pk-input"
                        name="first_name"
                        rules="required"
                        :value="old('first_name')"
                        label="First Name"
                        placeholder="Jane"
                        aria-label="First Name"
                        autocomplete="given-name"
                        aria-required="true"
                    />

                    <x-shop::form.control-group.error control-name="first_name" class="pk-error-text" />
                </x-shop::form.control-group>

                <x-shop::form.control-group class="pk-field">
                    <x-shop::form.control-group.label class="required pk-label">
                        Last Name
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="text"
                        class="pk-input"
                        name="last_name"
                        rules="required"
                        :value="old('last_name')"
                        label="Last Name"
                        placeholder="Doe"
                        aria-label="Last Name"
                        autocomplete="family-name"
                        aria-required="true"
                    />

                    <x-shop::form.control-group.error control-name="last_name" class="pk-error-text" />
                </x-shop::form.control-group>
            </div>

            <x-shop::form.control-group class="pk-field">
                <x-shop::form.control-group.label class="required pk-label">
                    Commercial Email Address
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="email"
                    class="pk-input"
                    name="email"
                    rules="required|email"
                    :value="old('email')"
                    label="Email Address"
                    placeholder="buyer@company.com"
                    aria-label="Commercial Email Address"
                    autocomplete="email"
                    aria-required="true"
                />

                <x-shop::form.control-group.error control-name="email" class="pk-error-text" />
            </x-shop::form.control-group>

            <x-shop::form.control-group class="pk-field">
                <x-shop::form.control-group.label class="required pk-label">
                    Password (min. 6 characters)
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="password"
                    class="pk-input"
                    id="personal-password"
                    name="password"
                    rules="required|min:6"
                    :value="old('password')"
                    label="Password"
                    placeholder="Create a password"
                    ref="password"
                    aria-label="Password"
                    autocomplete="new-password"
                    aria-required="true"
                />

                <x-shop::form.control-group.error control-name="password" class="pk-error-text" />
            </x-shop::form.control-group>

            <x-shop::form.control-group class="pk-field">
                <x-shop::form.control-group.label class="required pk-label">
                    Confirm Password
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="password"
                    class="pk-input"
                    id="personal-password-confirm"
                    name="password_confirmation"
                    rules="confirmed:@password"
                    value=""
                    label="Confirm Password"
                    placeholder="Repeat password"
                    aria-label="Confirm Password"
                    autocomplete="new-password"
                    aria-required="true"
                />

                <x-shop::form.control-group.error control-name="password_confirmation" class="pk-error-text" />
            </x-shop::form.control-group>

            @if (core()->getConfigData('customer.settings.create_new_account_options.news_letter'))
                <label for="is-subscribed-personal" class="pk-checkbox-row mb-3">
                    <input
                        type="checkbox"
                        name="is_subscribed"
                        id="is-subscribed-personal"
                    />
                    <span>Receive quarterly wholesale inventory updates, compliance notices, and supply schedules.</span>
                </label>
            @endif

            @if (core()->getConfigData('general.gdpr.settings.enabled') && core()->getConfigData('general.gdpr.agreement.enabled'))
                <div class="mb-4">
                    <div class="flex items-center gap-2 text-xs pk-text-muted">
                        <x-shop::form.control-group.control
                            type="checkbox"
                            name="agreement"
                            id="agreement-personal"
                            value="0"
                            rules="required"
                            for="agreement-personal"
                        />
                        <label for="agreement-personal" class="cursor-pointer select-none">
                            {{ core()->getConfigData('general.gdpr.agreement.agreement_label') }}
                        </label>
                        @if (core()->getConfigData('general.gdpr.agreement.agreement_content'))
                            <span
                                class="cursor-pointer font-semibold pk-text-primary hover:underline"
                                @click="$refs.termsModal.open()"
                            >
                                Terms &amp; Conditions
                            </span>
                        @endif
                    </div>
                    <x-shop::form.control-group.error control-name="agreement" class="pk-error-text mt-1" />
                </div>
            @endif

            @if (core()->getConfigData('customer.captcha.credentials.status'))
                <x-shop::form.control-group class="pk-field">
                    {!! \Webkul\Customer\Facades\Captcha::render() !!}
                    <x-shop::form.control-group.error control-name="recaptcha_token" class="pk-error-text" />
                </x-shop::form.control-group>
            @endif

            <div style="margin-top: 18px;">
                <button type="submit" class="pk-btn pk-btn--primary pk-btn--block" style="min-height: 46px;">
                    Create Wholesale Account &rarr;
                </button>
            </div>

            {!! view_render_event('bagisto.shop.customers.signup_form_controls.after') !!}
        </x-shop::form>
    </div>

    {{-- ============================================================== --}}
    {{-- PANEL 2: Corporate Account Form                                --}}
    {{-- ============================================================== --}}
    @if ($b2bActive)
        <div id="panel-company" class="{{ $activeTab === 'company' ? '' : 'hidden' }}">
            <x-shop::form
                :action="route('shop.companies.register.store')"
                enctype="multipart/form-data"
            >
                <input type="hidden" name="account_type" value="company">

                <div class="pk-form-row">
                    <x-shop::form.control-group class="pk-field">
                        <x-shop::form.control-group.label class="required pk-label">
                            Contact First Name
                        </x-shop::form.control-group.label>

                        <x-shop::form.control-group.control
                            type="text"
                            class="pk-input"
                            name="first_name"
                            rules="required"
                            :value="old('first_name')"
                            label="First Name"
                            placeholder="Jane"
                            aria-label="First Name"
                            autocomplete="given-name"
                            aria-required="true"
                        />

                        <x-shop::form.control-group.error control-name="first_name" class="pk-error-text" />
                    </x-shop::form.control-group>

                    <x-shop::form.control-group class="pk-field">
                        <x-shop::form.control-group.label class="required pk-label">
                            Contact Last Name
                        </x-shop::form.control-group.label>

                        <x-shop::form.control-group.control
                            type="text"
                            class="pk-input"
                            name="last_name"
                            rules="required"
                            :value="old('last_name')"
                            label="Last Name"
                            placeholder="Doe"
                            aria-label="Last Name"
                            autocomplete="family-name"
                            aria-required="true"
                        />

                        <x-shop::form.control-group.error control-name="last_name" class="pk-error-text" />
                    </x-shop::form.control-group>
                </div>

                <div class="pk-form-row">
                    <x-shop::form.control-group class="pk-field">
                        <x-shop::form.control-group.label class="required pk-label">
                            Business / Entity Name
                        </x-shop::form.control-group.label>

                        <x-shop::form.control-group.control
                            type="text"
                            class="pk-input"
                            name="business_name"
                            rules="required"
                            :value="old('business_name')"
                            label="Business Name"
                            placeholder="Acme Botanicals LLC"
                            aria-label="Business Name"
                            autocomplete="organization"
                            aria-required="true"
                        />

                        <x-shop::form.control-group.error control-name="business_name" class="pk-error-text" />
                    </x-shop::form.control-group>

                    <x-shop::form.control-group class="pk-field">
                        <x-shop::form.control-group.label class="required pk-label">
                            Business Phone Number
                        </x-shop::form.control-group.label>

                        <x-shop::form.control-group.control
                            type="text"
                            class="pk-input"
                            name="phone"
                            rules="required"
                            :value="old('phone')"
                            label="Phone Number"
                            placeholder="+1 (555) 000-0000"
                            aria-label="Business Phone Number"
                            autocomplete="tel"
                            aria-required="true"
                        />

                        <x-shop::form.control-group.error control-name="phone" class="pk-error-text" />
                    </x-shop::form.control-group>
                </div>

                <x-shop::form.control-group class="pk-field">
                    <x-shop::form.control-group.label class="required pk-label">
                        Corporate Email Address
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="email"
                        class="pk-input"
                        name="email"
                        rules="required|email"
                        :value="old('email')"
                        label="Corporate Email Address"
                        placeholder="purchasing@company.com"
                        aria-label="Corporate Email Address"
                        autocomplete="email"
                        aria-required="true"
                    />

                    <x-shop::form.control-group.error control-name="email" class="pk-error-text" />
                </x-shop::form.control-group>

                <x-shop::form.control-group class="pk-field">
                    <x-shop::form.control-group.label class="required pk-label">
                        Password (min. 6 characters)
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="password"
                        class="pk-input"
                        id="company-password"
                        name="password"
                        rules="required|min:6"
                        :value="old('password')"
                        label="Password"
                        placeholder="Create a password"
                        ref="companyPassword"
                        aria-label="Password"
                        autocomplete="new-password"
                        aria-required="true"
                    />

                    <x-shop::form.control-group.error control-name="password" class="pk-error-text" />
                </x-shop::form.control-group>

                <x-shop::form.control-group class="pk-field">
                    <x-shop::form.control-group.label class="required pk-label">
                        Confirm Password
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="password"
                        class="pk-input"
                        id="company-password-confirm"
                        name="password_confirmation"
                        rules="confirmed:@companyPassword"
                        value=""
                        label="Confirm Password"
                        placeholder="Repeat password"
                        aria-label="Confirm Password"
                        autocomplete="new-password"
                        aria-required="true"
                    />

                    <x-shop::form.control-group.error control-name="password_confirmation" class="pk-error-text" />
                </x-shop::form.control-group>

                @if (core()->getConfigData('customer.settings.create_new_account_options.news_letter'))
                    <label for="is-subscribed-company" class="pk-checkbox-row mb-3">
                        <input
                            type="checkbox"
                            name="is_subscribed"
                            id="is-subscribed-company"
                        />
                        <span>Receive quarterly wholesale inventory updates, compliance notices, and supply schedules.</span>
                    </label>
                @endif

                @if (core()->getConfigData('general.gdpr.settings.enabled') && core()->getConfigData('general.gdpr.agreement.enabled'))
                    <div class="mb-4">
                        <div class="flex items-center gap-2 text-xs pk-text-muted">
                            <x-shop::form.control-group.control
                                type="checkbox"
                                name="agreement"
                                id="agreement-company"
                                value="0"
                                rules="required"
                                for="agreement-company"
                            />
                            <label for="agreement-company" class="cursor-pointer select-none">
                                {{ core()->getConfigData('general.gdpr.agreement.agreement_label') }}
                            </label>
                            @if (core()->getConfigData('general.gdpr.agreement.agreement_content'))
                                <span
                                    class="cursor-pointer font-semibold pk-text-primary hover:underline"
                                    @click="$refs.termsModal.open()"
                                >
                                    Terms &amp; Conditions
                                </span>
                            @endif
                        </div>
                        <x-shop::form.control-group.error control-name="agreement" class="pk-error-text mt-1" />
                    </div>
                @endif

                @if (core()->getConfigData('customer.captcha.credentials.status'))
                    <x-shop::form.control-group class="pk-field">
                        {!! \Webkul\Customer\Facades\Captcha::render() !!}
                        <x-shop::form.control-group.error control-name="recaptcha_token" class="pk-error-text" />
                    </x-shop::form.control-group>
                @endif

                <div style="margin-top: 18px;">
                    <button type="submit" class="pk-btn pk-btn--primary pk-btn--block" style="min-height: 46px;">
                        Register Corporate Account &rarr;
                    </button>
                </div>
            </x-shop::form>
        </div>
    @endif

    <div id="future-sso-container" class="hidden"></div>

    <div class="pk-auth-divider"></div>

    <p class="text-center text-xs pk-text-muted">
        Already have a PurNatureK wholesale account?
        <a href="{{ route('shop.customer.session.index') }}" class="font-bold pk-text-primary hover:underline">
            Sign In &rarr;
        </a>
    </p>

    <x-slot:modals>
        <x-shop::modal ref="termsModal">
            <x-slot:toggle></x-slot>

            <x-slot:header>
                <p class="font-bold text-sm pk-text-primary">
                    Terms &amp; Conditions
                </p>
            </x-slot>

            <x-slot:content>
                <div class="max-h-[460px] overflow-auto text-xs pk-text-muted leading-relaxed">
                    {!! core()->getConfigData('general.gdpr.agreement.agreement_content') !!}
                </div>
            </x-slot>
        </x-shop::modal>
    </x-slot>

    @push('scripts')
        {!! \Webkul\Customer\Facades\Captcha::renderJS() !!}

        <script>
            /**
             * Toggle between the Member (personal) and Corporate (company) registration panels.
             *
             * @param {string} type - Either "personal" or "company".
             */
            function pkSwitchRegisterTab(type) {
                var personalPanel = document.getElementById('panel-personal');
                var companyPanel = document.getElementById('panel-company');
                var personalTab = document.getElementById('tab-personal');
                var companyTab = document.getElementById('tab-company');

                if (type === 'personal') {
                    if (personalPanel) personalPanel.classList.remove('hidden');
                    if (companyPanel) companyPanel.classList.add('hidden');
                    if (personalTab) {
                        personalTab.classList.add('is-active', 'pk-segment--active');
                        personalTab.setAttribute('aria-selected', 'true');
                    }
                    if (companyTab) {
                        companyTab.classList.remove('is-active', 'pk-segment--active');
                        companyTab.setAttribute('aria-selected', 'false');
                    }
                } else {
                    if (personalPanel) personalPanel.classList.add('hidden');
                    if (companyPanel) companyPanel.classList.remove('hidden');
                    if (companyTab) {
                        companyTab.classList.add('is-active', 'pk-segment--active');
                        companyTab.setAttribute('aria-selected', 'true');
                    }
                    if (personalTab) {
                        personalTab.classList.remove('is-active', 'pk-segment--active');
                        personalTab.setAttribute('aria-selected', 'false');
                    }
                }
            }

            window.b2bSwitchTab = pkSwitchRegisterTab;
        </script>
    @endpush
</x-shop::portal.auth-shell>

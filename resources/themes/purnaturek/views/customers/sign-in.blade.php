@php
    if (! session()->has('shop.url.intended')) {
        session()->put('shop.url.intended', route('shop.customers.account.index'));
    }

    $b2bActive = (bool) core()->getConfigData('b2b.general.settings.active');
@endphp

@push('meta')
    <meta name="keywords" content="PurNatureK, wholesale, login, b2b member, botanical extract"/>
@endPush

<x-shop::portal.auth-shell
    title="Wholesale Member Sign In — PurNatureK"
    meta-description="Sign in to PurNatureK Wholesale Members Portal"
    eyebrow="Commercial Portal Access"
    heading="Wholesale Member Sign In"
    subtitle="Enter your authorized wholesale credentials to access protected pricing, product specifications, and commercial agreements."
    visual-eyebrow="PurNatureK B2B Sourcing"
    visual-heading="Premium Kratom. Wholesale &amp; Bulk Supply."
    visual-lead="Your US-Based Wholesale Partner for Mitragyna speciosa Powder &amp; Extract. Built for commercial formulators, botanical brands, and wholesale distributors."
>
    <x-slot:topbarRight>
        <div class="flex items-center gap-3 text-xs">
            <span class="hidden sm:inline pk-text-muted">New wholesale buyer?</span>
            <a href="{{ route('shop.customers.register.index') }}" class="pk-btn pk-btn--secondary pk-btn--sm">
                Create Account
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

    {!! view_render_event('bagisto.shop.customers.login.before') !!}

    <x-shop::form :action="route('shop.customer.session.create')">
        {!! view_render_event('bagisto.shop.customers.login_form_controls.before') !!}

        <x-shop::form.control-group class="pk-field">
            <x-shop::form.control-group.label class="required pk-label">
                Email Address
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control
                type="email"
                class="pk-input"
                name="email"
                rules="required|email"
                value=""
                label="Email Address"
                placeholder="buyer@company.com"
                aria-label="Email Address"
                autocomplete="email"
                aria-required="true"
            />

            <x-shop::form.control-group.error control-name="email" class="pk-error-text" />
        </x-shop::form.control-group>

        <x-shop::form.control-group class="pk-field">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                <x-shop::form.control-group.label class="required pk-label" style="margin-bottom:0;">
                    Password
                </x-shop::form.control-group.label>

                <a
                    href="{{ route('shop.customers.forgot_password.create') }}"
                    class="text-xs font-semibold pk-text-primary hover:underline"
                >
                    Forgot password?
                </a>
            </div>

            <x-shop::form.control-group.control
                type="password"
                class="pk-input"
                id="password"
                name="password"
                rules="required|min:6"
                value=""
                label="Password"
                placeholder="Enter your password"
                aria-label="Password"
                autocomplete="current-password"
                aria-required="true"
            />

            <x-shop::form.control-group.error control-name="password" class="pk-error-text" />
        </x-shop::form.control-group>

        @if (core()->getConfigData('customer.captcha.credentials.status'))
            <x-shop::form.control-group class="pk-field">
                {!! \Webkul\Customer\Facades\Captcha::render() !!}
                <x-shop::form.control-group.error control-name="recaptcha_token" class="pk-error-text" />
            </x-shop::form.control-group>
        @endif

        <div style="margin-top: 20px;">
            <button
                type="submit"
                class="pk-btn pk-btn--primary pk-btn--block"
                style="min-height: 46px;"
            >
                Sign In to Members Workspace &rarr;
            </button>
        </div>

        {!! view_render_event('bagisto.shop.customers.login_form_controls.after') !!}
    </x-shop::form>

    {!! view_render_event('bagisto.shop.customers.login.after') !!}

    @if (
        request()->cookie('enable-resend')
        && request()->cookie('email-for-resend')
    )
        <p class="mt-4 text-xs pk-text-muted">
            <a
                class="font-semibold pk-text-primary hover:underline"
                href="{{ route('shop.customers.resend.verification_email', urlencode(request()->cookie('email-for-resend'))) }}"
            >
                @lang('shop::app.customers.login-form.resend-verification')
            </a>
        </p>
    @endif

    <div class="pk-auth-divider"></div>

    <div class="pk-stack-xs">
        <p class="pk-eyebrow" style="margin-bottom: 4px;">New Wholesale Buyers</p>

        <div class="pk-stack-xs">
            <a
                href="{{ route('shop.customers.register.index') }}"
                class="pk-btn pk-btn--secondary pk-btn--block"
            >
                Create Wholesale Account
            </a>

            @if ($b2bActive)
                <a
                    href="{{ route('shop.customers.register.index', ['type' => 'company']) }}"
                    class="pk-btn pk-btn--earth pk-btn--block"
                >
                    Register as B2B Corporate Entity
                </a>
            @endif
        </div>

        <p class="pk-help-text" style="margin-top: 8px;">
            Need onboarding assistance? Contact our Chicago trade desk at
            <a href="mailto:wholesale@purnaturek.com" class="font-semibold pk-text-primary hover:underline">wholesale@purnaturek.com</a>.
        </p>
    </div>

    <x-slot:visualBody>
        <div class="pk-auth-feature-list">
            <div class="pk-auth-feature-item">
                <p class="pk-auth-feature-item__title">Tier 1 &mdash; Registered Member</p>
                <p class="pk-auth-feature-item__text">
                    Immediate access to wholesale product specifications, indicative pricing models, documentation, and direct admin messaging.
                </p>
            </div>

            <div class="pk-auth-feature-item">
                <p class="pk-auth-feature-item__title">Tier 2 &mdash; Verified Wholesale Buyer</p>
                <p class="pk-auth-feature-item__text">
                    Unlocks formal quotations, commercial RFQs, purchase orders, and supply contracts upon credential verification.
                </p>
            </div>

            <div class="pk-auth-feature-item">
                <p class="pk-auth-feature-item__title">Age-Restricted Commercial Sourcing (21+)</p>
                <p class="pk-auth-feature-item__text">
                    Serves licensed commercial formulators, botanical brands, and wholesale distributors across the United States.
                </p>
            </div>
        </div>
    </x-slot>

    @push('scripts')
        {!! \Webkul\Customer\Facades\Captcha::renderJS() !!}
    @endpush
</x-shop::portal.auth-shell>

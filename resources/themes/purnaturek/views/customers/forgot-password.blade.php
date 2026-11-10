@push('meta')
    <meta name="keywords" content="@lang('shop::app.customers.forgot-password.title')" />
@endPush

<x-shop::portal.auth-shell
    :title="trans('shop::app.customers.forgot-password.title') . ' — PurNatureK'"
    :meta-description="trans('shop::app.customers.forgot-password.title')"
    eyebrow="Account Recovery"
    :heading="trans('shop::app.customers.forgot-password.title')"
    :subtitle="trans('shop::app.customers.forgot-password.forgot-password-text')"
    visual-eyebrow="PurNatureK Member Security"
    visual-heading="Protected Commercial Account Recovery"
    visual-lead="Password reset links are dispatched exclusively to your registered commercial email address to safeguard wholesale pricing and agreement custody."
>
    <x-slot:topbarRight>
        <a href="{{ route('shop.customer.session.index') }}" class="pk-btn pk-btn--secondary pk-btn--sm">
            &larr; Back to Sign In
        </a>
    </x-slot>

    {!! view_render_event('bagisto.shop.customers.forget_password.before') !!}

    <x-shop::form :action="route('shop.customers.forgot_password.store')">
        {!! view_render_event('bagisto.shop.customers.forget_password_form_controls.before') !!}

        <x-shop::form.control-group class="pk-field">
            <x-shop::form.control-group.label class="required pk-label">
                @lang('shop::app.customers.login-form.email')
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control
                type="email"
                class="pk-input"
                name="email"
                rules="required|email"
                value=""
                :label="trans('shop::app.customers.login-form.email')"
                placeholder="buyer@company.com"
                :aria-label="trans('shop::app.customers.login-form.email')"
                autocomplete="email"
                aria-required="true"
            />

            <x-shop::form.control-group.error control-name="email" class="pk-error-text" />
        </x-shop::form.control-group>

        {!! view_render_event('bagisto.shop.customers.forget_password_form_controls.email.after') !!}

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
                @lang('shop::app.customers.forgot-password.submit')
            </button>
        </div>

        {!! view_render_event('bagisto.shop.customers.forget_password_form_controls.after') !!}
    </x-shop::form>

    {!! view_render_event('bagisto.shop.customers.forget_password.after') !!}

    <div class="pk-auth-divider"></div>

    <p class="text-xs pk-text-muted text-center">
        @lang('shop::app.customers.forgot-password.back')
        <a
            class="font-bold pk-text-primary hover:underline"
            href="{{ route('shop.customer.session.index') }}"
        >
            @lang('shop::app.customers.forgot-password.sign-in-button') &rarr;
        </a>
    </p>

    @push('scripts')
        {!! \Webkul\Customer\Facades\Captcha::renderJS() !!}
    @endpush
</x-shop::portal.auth-shell>


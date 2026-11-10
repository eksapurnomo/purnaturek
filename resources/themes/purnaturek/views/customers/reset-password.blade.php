@push('meta')
    <meta name="keywords" content="@lang('shop::app.customers.reset-password.title')" />
@endPush

<x-shop::portal.auth-shell
    :title="trans('shop::app.customers.reset-password.title') . ' — PurNatureK'"
    :meta-description="trans('shop::app.customers.reset-password.title')"
    eyebrow="Credential Update"
    :heading="trans('shop::app.customers.reset-password.title')"
    subtitle="Confirm your commercial email address and set a new password to restore access to your wholesale member workspace."
    visual-eyebrow="PurNatureK Member Security"
    visual-heading="Restore Your Wholesale Access"
    visual-lead="Use a strong, unique passphrase for your B2B procurement profile, compliance custody, and commercial contracts."
>
    <x-slot:topbarRight>
        <a href="{{ route('shop.customer.session.index') }}" class="pk-btn pk-btn--secondary pk-btn--sm">
            &larr; Back to Sign In
        </a>
    </x-slot>

    {!! view_render_event('bagisto.shop.customers.reset_password.before') !!}

    <x-shop::form :action="route('shop.customers.reset_password.store')">
        <x-shop::form.control-group.control
            type="hidden"
            name="token"
            :value="$token"
        />

        {!! view_render_event('bagisto.shop.customers.reset_password_form_controls.before') !!}

        <x-shop::form.control-group class="pk-field">
            <x-shop::form.control-group.label class="required pk-label">
                @lang('shop::app.customers.reset-password.email')
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control
                type="email"
                class="pk-input"
                id="email"
                name="email"
                rules="required|email"
                :value="old('email')"
                :label="trans('shop::app.customers.reset-password.email')"
                placeholder="buyer@company.com"
                :aria-label="trans('shop::app.customers.reset-password.email')"
                autocomplete="email"
                aria-required="true"
            />

            <x-shop::form.control-group.error control-name="email" class="pk-error-text" />
        </x-shop::form.control-group>

        <x-shop::form.control-group class="pk-field">
            <x-shop::form.control-group.label class="required pk-label">
                @lang('shop::app.customers.reset-password.password')
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control
                type="password"
                class="pk-input"
                name="password"
                rules="required|min:6"
                value=""
                :label="trans('shop::app.customers.reset-password.password')"
                :placeholder="trans('shop::app.customers.reset-password.password')"
                ref="password"
                :aria-label="trans('shop::app.customers.reset-password.password')"
                autocomplete="new-password"
                aria-required="true"
            />

            <x-shop::form.control-group.error control-name="password" class="pk-error-text" />
        </x-shop::form.control-group>

        <x-shop::form.control-group class="pk-field">
            <x-shop::form.control-group.label class="required pk-label">
                @lang('shop::app.customers.reset-password.confirm-password')
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control
                type="password"
                class="pk-input"
                name="password_confirmation"
                rules="confirmed:@password"
                value=""
                :label="trans('shop::app.customers.reset-password.confirm-password')"
                :placeholder="trans('shop::app.customers.reset-password.confirm-password')"
                :aria-label="trans('shop::app.customers.reset-password.confirm-password')"
                autocomplete="new-password"
                aria-required="true"
            />

            <x-shop::form.control-group.error control-name="password_confirmation" class="pk-error-text" />
        </x-shop::form.control-group>

        {!! view_render_event('bagisto.shop.customers.reset_password_form_controls.after') !!}

        {!! view_render_event('bagisto.shop.customers.reset_password.submit_button.before') !!}

        <div style="margin-top: 20px;">
            <button
                type="submit"
                class="pk-btn pk-btn--primary pk-btn--block"
                style="min-height: 46px;"
            >
                @lang('shop::app.customers.reset-password.submit-btn-title')
            </button>
        </div>

        {!! view_render_event('bagisto.shop.customers.reset_password.submit_button.after') !!}
    </x-shop::form>

    {!! view_render_event('bagisto.shop.customers.reset_password.after') !!}
</x-shop::portal.auth-shell>


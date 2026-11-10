{{--
    PurNatureK Members Portal — Account / Profile Overview
    resources/themes/purnaturek/views/customers/account/profile/index.blade.php

    Preserves B2B Company Details panel, Edit Profile link, Delete Account modal, and all Bagisto render events.
--}}
<x-shop::layouts.account>
    <x-slot:title>
        @lang('shop::app.customers.account.profile.index.title') — PurNatureK
    </x-slot>

    @if (core()->getConfigData('general.general.breadcrumbs.shop'))
        @section('breadcrumbs')
            <x-shop::breadcrumbs name="profile" />
        @endSection
    @endif

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="pk-stack-md">
        <x-shop::portal.page-header
            eyebrow="Account Settings"
            title="Account &amp; Corporate Profile"
            subtitle="Manage your personal member credentials and affiliated corporate buyer profile."
        >
            <x-slot:actions>
                {!! view_render_event('bagisto.shop.customers.account.profile.edit_button.before') !!}

                <x-shop::portal.button :href="route('shop.customers.account.profile.edit')" variant="primary" size="sm">
                    @lang('shop::app.customers.account.profile.index.edit')
                </x-shop::portal.button>

                {!! view_render_event('bagisto.shop.customers.account.profile.edit_button.after') !!}
            </x-slot>
        </x-shop::portal.page-header>

        <!-- Company Information (B2B Suite) -->
        @if ((bool) core()->getConfigData('b2b.general.settings.active'))
            @include('b2b::shop.customers.account.profile.company-details')
        @endif

        <!-- Personal Profile Details -->
        <x-shop::portal.card
            eyebrow="Member Credentials"
            :title="trans('b2b::app.shop.customers.account.profile.profile-information')"
        >
            <x-slot:actions>
                {!! view_render_event('bagisto.shop.customers.account.profile.delete.before') !!}

                <x-shop::form action="{{ route('shop.customers.account.profile.destroy') }}">
                    <x-shop::modal>
                        <x-slot:toggle>
                            <button
                                type="button"
                                class="pk-btn pk-btn--danger pk-btn--sm"
                            >
                                @lang('shop::app.customers.account.profile.index.delete-profile')
                            </button>
                        </x-slot>

                        <x-slot:header>
                            <h2 class="pk-panel__title">
                                @lang('shop::app.customers.account.profile.index.enter-password')
                            </h2>
                        </x-slot>

                        <x-slot:content>
                            <x-shop::form.control-group class="!mb-0">
                                <x-shop::form.control-group.control
                                    type="password"
                                    name="password"
                                    class="pk-input"
                                    rules="required"
                                    :placeholder="trans('shop::app.customers.account.profile.index.enter-password')"
                                />

                                <x-shop::form.control-group.error
                                    class="pk-error-text ltr:text-left rtl:text-right"
                                    control-name="password"
                                />
                            </x-shop::form.control-group>
                        </x-slot>

                        <x-slot:footer>
                            <button
                                type="submit"
                                class="pk-btn pk-btn--danger"
                            >
                                @lang('shop::app.customers.account.profile.index.delete')
                            </button>
                        </x-slot>
                    </x-shop::modal>
                </x-shop::form>

                {!! view_render_event('bagisto.shop.customers.account.profile.delete.after') !!}
            </x-slot>

            <dl class="pk-dl-list">
                {!! view_render_event('bagisto.shop.customers.account.profile.first_name.before') !!}
                <div class="pk-dl-item">
                    <dt>@lang('shop::app.customers.account.profile.index.first-name')</dt>
                    <dd v-pre>{{ $customer->first_name }}</dd>
                </div>
                {!! view_render_event('bagisto.shop.customers.account.profile.first_name.after') !!}

                {!! view_render_event('bagisto.shop.customers.account.profile.last_name.before') !!}
                <div class="pk-dl-item">
                    <dt>@lang('shop::app.customers.account.profile.index.last-name')</dt>
                    <dd v-pre>{{ $customer->last_name }}</dd>
                </div>
                {!! view_render_event('bagisto.shop.customers.account.profile.last_name.after') !!}

                {!! view_render_event('bagisto.shop.customers.account.profile.email.before') !!}
                <div class="pk-dl-item">
                    <dt>@lang('shop::app.customers.account.profile.index.email')</dt>
                    <dd v-pre>{{ $customer->email }}</dd>
                </div>
                {!! view_render_event('bagisto.shop.customers.account.profile.email.after') !!}

                {!! view_render_event('bagisto.shop.customers.account.profile.gender.before') !!}
                <div class="pk-dl-item">
                    <dt>@lang('shop::app.customers.account.profile.index.gender')</dt>
                    <dd v-pre>{{ $customer->gender ?: '—' }}</dd>
                </div>
                {!! view_render_event('bagisto.shop.customers.account.profile.gender.after') !!}

                {!! view_render_event('bagisto.shop.customers.account.profile.date_of_birth.before') !!}
                <div class="pk-dl-item">
                    <dt>@lang('shop::app.customers.account.profile.index.dob')</dt>
                    <dd v-pre>{{ $customer->date_of_birth ?: '—' }}</dd>
                </div>
                {!! view_render_event('bagisto.shop.customers.account.profile.date_of_birth.after') !!}
            </dl>
        </x-shop::portal.card>
    </div>
</x-shop::layouts.account>


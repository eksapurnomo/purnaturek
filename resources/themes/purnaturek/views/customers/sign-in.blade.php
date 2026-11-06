@php
    if (! session()->has('shop.url.intended')) {
        session()->put('shop.url.intended', route('shop.customers.account.index'));
    }
@endphp

<!-- SEO Meta Content -->
@push('meta')
    <meta name="description" content="Sign in to PurNatureK Wholesale Members Portal"/>
    <meta name="keywords" content="PurNatureK, wholesale, login, b2b member"/>
@endPush

<x-shop::layouts
    :has-header="false"
    :has-feature="false"
    :has-footer="true"
>
    <x-slot:title>
        Wholesale Member Sign In — PurNatureK
    </x-slot>

    <!-- Branded Top Navigation Bar for Auth Pages -->
    <header class="bg-white border-b border-[#d9e6e1] py-4 px-6 shadow-sm">
        <div class="max-w-[1180px] mx-auto flex items-center justify-between">
            <a href="{{ route('shop.home.index') }}" class="pk-brand" aria-label="PurNatureK">
                @if ($logo = core()->getCurrentChannel()->logo_url)
                    <img src="{{ $logo }}" alt="PurNatureK" class="pk-brand__logo">
                @else
                    <span class="pk-brand__name text-xl">PurNature<span class="pk-brand__name-leaf">K</span></span>
                @endif
                <span class="pk-brand__tag">Members Portal</span>
            </a>

            <a href="https://purnaturekbulk.com" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-[#5a6f68] hover:text-[#0f4a3e]">
                Corporate Website &rarr;
            </a>
        </div>
    </header>

    <div class="max-w-[1100px] mx-auto px-6 py-12">
        <div class="bg-white border border-[#d9e6e1] rounded-2xl p-8 md:p-12 shadow-sm">
            <div class="border-b border-[#d9e6e1] pb-6 mb-8">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#f0f7f4] text-[#0f4a3e] border border-[#d9e6e1] mb-2">
                    Commercial Portal Access
                </span>
                <h1 class="text-2xl md:text-3xl font-extrabold text-[#0f4a3e]">
                    Wholesale Member Sign In
                </h1>
                <p class="text-sm text-[#5a6f68] mt-1">
                    Enter your authorized wholesale credentials to access protected pricing, inventory specifications, and commercial agreements.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">
                {{-- LEFT COLUMN: Login Form --}}
                <div>
                    <h2 class="text-lg font-bold text-[#0f4a3e] pb-2 border-b border-[#d9e6e1]">
                        Registered Commercial Members
                    </h2>

                    <p class="text-xs text-[#5a6f68] mt-2 mb-6">
                        If you have an active member account, sign in with your email address and password.
                    </p>

                    <x-shop::form :action="route('shop.customer.session.create')">
                        {!! view_render_event('bagisto.shop.customers.login_form_controls.before') !!}

                        <!-- Email -->
                        <x-shop::form.control-group class="mb-4">
                            <x-shop::form.control-group.label class="required font-semibold text-xs text-[#1e2d29]">
                                Email Address
                            </x-shop::form.control-group.label>

                            <x-shop::form.control-group.control
                                type="email"
                                class="w-full rounded-lg border border-[#d9e6e1] px-4 py-3 text-sm focus:border-emerald-600 focus:outline-none"
                                name="email"
                                rules="required|email"
                                value=""
                                label="Email Address"
                                placeholder="buyer@company.com"
                                aria-label="Email Address"
                                aria-required="true"
                            />

                            <x-shop::form.control-group.error control-name="email" />
                        </x-shop::form.control-group>

                        <!-- Password -->
                        <x-shop::form.control-group class="mb-4">
                            <x-shop::form.control-group.label class="required font-semibold text-xs text-[#1e2d29]">
                                Password
                            </x-shop::form.control-group.label>

                            <x-shop::form.control-group.control
                                type="password"
                                class="w-full rounded-lg border border-[#d9e6e1] px-4 py-3 text-sm focus:border-emerald-600 focus:outline-none"
                                id="password"
                                name="password"
                                rules="required|min:6"
                                value=""
                                label="Password"
                                placeholder="••••••••"
                                aria-label="Password"
                                aria-required="true"
                            />

                            <x-shop::form.control-group.error control-name="password" />
                        </x-shop::form.control-group>

                        <!-- Show Password & Forgot Password -->
                        <div class="flex items-center justify-between text-xs mt-2 mb-6">
                            <label class="inline-flex items-center gap-2 cursor-pointer text-[#5a6f68]">
                                <input
                                    type="checkbox"
                                    id="show-password"
                                    class="rounded border-[#d9e6e1] text-emerald-700 focus:ring-emerald-600"
                                    onchange="document.getElementById('password').type = this.checked ? 'text' : 'password'"
                                />
                                <span>Show password</span>
                            </label>

                            <a
                                href="{{ route('shop.customers.forgot_password.create') }}"
                                class="text-[#0f4a3e] font-semibold hover:underline"
                            >
                                Forgot your password?
                            </a>
                        </div>

                        <!-- Captcha if enabled -->
                        @if (core()->getConfigData('customer.captcha.credentials.status'))
                            <x-shop::form.control-group class="mb-4">
                                {!! \Webkul\Customer\Facades\Captcha::render() !!}
                                <x-shop::form.control-group.error control-name="recaptcha_token" />
                            </x-shop::form.control-group>
                        @endif

                        <!-- Submit Button -->
                        <div class="mt-6">
                            <button
                                type="submit"
                                class="pk-btn pk-btn--primary w-full py-3.5 text-sm font-bold shadow-sm"
                            >
                                Sign In to Members Workspace &rarr;
                            </button>
                        </div>

                        {!! view_render_event('bagisto.shop.customers.login_form_controls.after') !!}
                    </x-shop::form>
                </div>

                {{-- RIGHT COLUMN: Wholesale Account Onboarding Info --}}
                <div class="lg:border-l lg:border-[#d9e6e1] lg:pl-12 flex flex-col justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-[#0f4a3e] pb-2 border-b border-[#d9e6e1]">
                            New Wholesale Buyers
                        </h2>

                        <p class="text-xs text-[#5a6f68] mt-2 mb-4 leading-relaxed">
                            PurNatureK serves corporate wholesale buyers, distributors, food/supplement manufacturers, and institutional purchasing agents.
                        </p>

                        <div class="rounded-xl bg-[#f0f7f4] border border-[#d9e6e1] p-4 text-xs text-[#0f4a3e] space-y-2 mb-6">
                            <p class="font-bold">What wholesale registration includes:</p>
                            <ul class="space-y-1.5 pl-4 list-disc text-[#1e2d29]">
                                <li>Immediate Tier 1 access to browse wholesale product specifications</li>
                                <li>Eligible to submit business credentials for Tier 2 Verified Buyer status</li>
                                <li>Direct RFQ generation, quotation requests, and custom batch compounding</li>
                            </ul>
                        </div>

                        <a
                            href="{{ route('shop.customers.register.index') }}"
                            class="pk-btn pk-btn--ghost w-full py-3 text-sm font-semibold mb-4"
                        >
                            Create Wholesale Account
                        </a>

                        @if (core()->getConfigData('b2b.general.settings.active'))
                            <a
                                href="{{ route('shop.customers.register.index', ['type' => 'company']) }}"
                                class="pk-btn pk-btn--accent w-full py-3 text-sm font-semibold"
                            >
                                Register as B2B Corporate Entity
                            </a>
                        @endif
                    </div>

                    <div class="mt-8 pt-6 border-t border-[#d9e6e1] text-[11px] text-[#5a6f68]">
                        <p>
                            Need assistance with account onboarding or enterprise supply contracts?
                            Reach our support desk at <span class="font-semibold text-[#0f4a3e]">wholesale@purnaturekbulk.com</span>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-shop::layouts>


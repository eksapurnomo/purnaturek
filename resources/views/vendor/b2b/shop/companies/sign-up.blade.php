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

<!-- SEO Meta Content -->
@push('meta')
    <meta name="description" content="Register for PurNatureK Wholesale Members Portal — Commercial botanical extracts, bulk active ingredients, and verified B2B sourcing."/>
    <meta name="keywords" content="PurNatureK, wholesale application, b2b registration, bulk botanicals, botanical extracts, commercial sourcing"/>
@endPush

<x-shop::layouts
    :has-header="false"
    :has-feature="false"
    :has-footer="true"
>
    <x-slot:title>
        Wholesale Member Registration — PurNatureK
    </x-slot>

    <!-- Clean Top Navigation Bar (Full Width, 80-85px Desktop Height) -->
    <header class="pk-header-bar bg-white border-b border-[#e2e8f0] w-full h-[72px] lg:h-[84px] flex items-center px-4 sm:px-8 lg:px-12">
        <div class="w-full flex items-center justify-between">
            <a href="{{ route('shop.home.index') }}" class="flex items-center gap-2.5" aria-label="PurNatureK Bulk & Wholesale">
                <img
                    src="{{ asset('themes/shop/purnaturek/images/logopurnaturekbulk.png') }}"
                    alt="PurNatureK Bulk & Wholesale"
                    class="h-9 sm:h-10 lg:h-11 w-auto object-contain"
                    width="200"
                    height="89"
                >
                <span class="hidden sm:inline-block text-[11px] font-bold text-[#0f4a3e] bg-[#f0f7f4] border border-[#d9e6e1] px-2.5 py-0.5 rounded-md">
                    Members Portal
                </span>
            </a>

            <div class="flex items-center gap-5 text-xs sm:text-sm">
                <div class="flex items-center gap-2 text-[#475569]">
                    <span class="hidden sm:inline">Already a member?</span>
                    <a href="{{ route('shop.customer.session.index') }}" class="font-bold text-[#0f4a3e] hover:text-[#17705d] hover:underline inline-flex items-center gap-1">
                        <span>Sign In</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <a href="https://purnaturekbulk.com" target="_blank" rel="noopener noreferrer" class="hidden md:inline-block font-semibold text-[#64748b] hover:text-[#0f4a3e] transition-colors text-xs">
                    Corporate Site &rarr;
                </a>
            </div>
        </div>
    </header>

    <!-- Page Body: Two-Panel Full-Width Desktop Composition / Single-Column Mobile -->
    <div class="pk-register-page min-h-[calc(100vh-84px)] bg-[#f8faf8] flex flex-col justify-between">
        <div class="pk-register-layout">

            {{-- ========================================================== --}}
            {{-- LEFT PANEL: Registration Form (~46-48% width)               --}}
            {{-- ========================================================== --}}
            <div class="pk-register-form-panel">
                <div class="pk-register-card bg-white border border-[#e2e8f0] rounded-2xl shadow-[0_4px_24px_-2px_rgba(15,74,62,0.05),0_2px_8px_-1px_rgba(0,0,0,0.03)] w-full">
                        <!-- Brand Logo Above Registration Form -->
                        <div class="text-center mb-5">
                            <a href="{{ route('shop.home.index') }}" class="inline-block" aria-label="PurNatureK Bulk & Wholesale">
                                <img
                                    src="{{ asset('themes/shop/purnaturek/images/logopurnaturekbulk.png') }}"
                                    alt="PurNatureK Bulk & Wholesale"
                                    class="h-11 sm:h-12 w-auto mx-auto object-contain"
                                    width="200"
                                    height="89"
                                >
                            </a>
                        </div>

                        <!-- Headings -->
                        <div class="text-center mb-5">
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f4a3e] tracking-tight">
                                Create Your Wholesale Account
                            </h1>
                            <p class="text-xs sm:text-sm text-[#475569] mt-2 leading-relaxed">
                                Build Your Wholesale Partnership. Verified commercial sourcing for high-purity botanical extracts, bulk active ingredients, and volume contract supply.
                            </p>
                            <span class="inline-block text-[11px] font-bold text-[#17705d] uppercase tracking-wider mt-1.5">
                                Create Your Member Account
                            </span>
                        </div>

                        <!-- Account Type Selector (Compact Full-Width Segmented Control) -->
                        @if ($b2bActive)
                            <div class="p-1 bg-[#f1f5f3] rounded-xl flex gap-1 mb-5 border border-[#e2e8f0] w-full">
                                <button
                                    type="button"
                                    id="tab-personal"
                                    onclick="pkSwitchRegisterTab('personal')"
                                    class="pk-segment-tab {{ $activeTab === 'personal' ? 'pk-segment--active' : '' }}"
                                    role="tab"
                                    aria-selected="{{ $activeTab === 'personal' ? 'true' : 'false' }}"
                                >
                                    <span class="block">Member Account</span>
                                    <span class="text-[10px] font-normal opacity-75">Instant Tier 1 Access</span>
                                </button>

                                <button
                                    type="button"
                                    id="tab-company"
                                    onclick="pkSwitchRegisterTab('company')"
                                    class="pk-segment-tab {{ $activeTab === 'company' ? 'pk-segment--active' : '' }}"
                                    role="tab"
                                    aria-selected="{{ $activeTab === 'company' ? 'true' : 'false' }}"
                                >
                                    <span class="block">Corporate Entity</span>
                                    <span class="text-[10px] font-normal opacity-75">B2B Company Profile</span>
                                </button>
                            </div>
                        @endif

                        <!-- Compact Membership Tier Context (Clean Badges, Zero Large SVG Icons) -->
                        <div class="rounded-xl bg-[#f8faf9] border border-[#e2e8f0] p-3.5 mb-5 text-xs text-[#334155] space-y-1.5 w-full">
                            <div class="flex items-center gap-2 text-[11px] font-bold text-[#0f4a3e]">
                                <span class="inline-block w-2 h-2 rounded-full bg-[#17705d]"></span>
                                <span>Two-Tier Membership Structure</span>
                            </div>
                            <p class="text-[11px] leading-relaxed text-[#475569]">
                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#e0ede7] text-[#0f4a3e]">Tier 1</span>
                                <strong class="text-[#0f172a]">Registered Member:</strong> Instant access to Wholesale Product Access, Supply & Product Documentation, and Direct Commercial Communication.
                            </p>
                            <p class="text-[11px] leading-relaxed text-[#475569]">
                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#fef9c3] text-[#854d0e]">Tier 2</span>
                                <strong class="text-[#0f172a]">Verified Wholesale Buyer:</strong> Unlocks quotation workflows, commercial RFQs, and purchase orders upon admin verification.
                            </p>
                            <p class="text-[10px] text-[#64748b] italic border-t border-[#e2e8f0] pt-1.5">
                                Registration establishes Tier 1 Member status. Verified Buyer status requires review of valid commercial credentials.
                            </p>
                        </div>

                        {{-- ============================================================== --}}
                        {{-- PANEL 1: Member Account Form (Single-Column Vertical Flow)     --}}
                        {{-- ============================================================== --}}
                        <div id="panel-personal" class="{{ $activeTab === 'personal' ? '' : 'hidden' }} w-full">
                            <x-shop::form :action="route('shop.customers.register.store')">
                                <input type="hidden" name="account_type" value="personal">

                                {!! view_render_event('bagisto.shop.customers.signup_form_controls.before') !!}

                                <!-- First Name (Single Column) -->
                                <x-shop::form.control-group class="mb-3.5 w-full">
                                    <x-shop::form.control-group.label class="required font-semibold text-xs text-[#0f172a] mb-1.5 block">
                                        First Name
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="text"
                                        class="w-full h-11 rounded-lg border border-[#cbd5e1] px-3.5 text-sm text-[#0f172a] placeholder-[#94a3b8] focus:border-[#0f4a3e] focus:ring-1 focus:ring-[#0f4a3e] focus:outline-none"
                                        name="first_name"
                                        rules="required"
                                        :value="old('first_name')"
                                        label="First Name"
                                        placeholder="Jane"
                                        aria-label="First Name"
                                        autocomplete="given-name"
                                        aria-required="true"
                                    />

                                    <x-shop::form.control-group.error control-name="first_name" class="text-red-600 text-xs mt-1" />
                                </x-shop::form.control-group>

                                <!-- Last Name (Single Column) -->
                                <x-shop::form.control-group class="mb-3.5 w-full">
                                    <x-shop::form.control-group.label class="required font-semibold text-xs text-[#0f172a] mb-1.5 block">
                                        Last Name
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="text"
                                        class="w-full h-11 rounded-lg border border-[#cbd5e1] px-3.5 text-sm text-[#0f172a] placeholder-[#94a3b8] focus:border-[#0f4a3e] focus:ring-1 focus:ring-[#0f4a3e] focus:outline-none"
                                        name="last_name"
                                        rules="required"
                                        :value="old('last_name')"
                                        label="Last Name"
                                        placeholder="Doe"
                                        aria-label="Last Name"
                                        autocomplete="family-name"
                                        aria-required="true"
                                    />

                                    <x-shop::form.control-group.error control-name="last_name" class="text-red-600 text-xs mt-1" />
                                </x-shop::form.control-group>

                                <!-- Email Address (Single Column) -->
                                <x-shop::form.control-group class="mb-3.5 w-full">
                                    <x-shop::form.control-group.label class="required font-semibold text-xs text-[#0f172a] mb-1.5 block">
                                        Commercial Email Address
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="email"
                                        class="w-full h-11 rounded-lg border border-[#cbd5e1] px-3.5 text-sm text-[#0f172a] placeholder-[#94a3b8] focus:border-[#0f4a3e] focus:ring-1 focus:ring-[#0f4a3e] focus:outline-none"
                                        name="email"
                                        rules="required|email"
                                        :value="old('email')"
                                        label="Email Address"
                                        placeholder="buyer@company.com"
                                        aria-label="Commercial Email Address"
                                        autocomplete="email"
                                        aria-required="true"
                                    />

                                    <x-shop::form.control-group.error control-name="email" class="text-red-600 text-xs mt-1" />
                                </x-shop::form.control-group>

                                <!-- Password (Single Column) -->
                                <x-shop::form.control-group class="mb-3.5 w-full">
                                    <x-shop::form.control-group.label class="required font-semibold text-xs text-[#0f172a] mb-1.5 block">
                                        Password (min. 6 characters)
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="password"
                                        class="w-full h-11 rounded-lg border border-[#cbd5e1] px-3.5 text-sm text-[#0f172a] placeholder-[#94a3b8] focus:border-[#0f4a3e] focus:ring-1 focus:ring-[#0f4a3e] focus:outline-none"
                                        id="personal-password"
                                        name="password"
                                        rules="required|min:6"
                                        :value="old('password')"
                                        label="Password"
                                        placeholder="••••••••"
                                        ref="password"
                                        aria-label="Password"
                                        autocomplete="new-password"
                                        aria-required="true"
                                    />

                                    <x-shop::form.control-group.error control-name="password" class="text-red-600 text-xs mt-1" />
                                </x-shop::form.control-group>

                                <!-- Confirm Password (Single Column) -->
                                <x-shop::form.control-group class="mb-2.5 w-full">
                                    <x-shop::form.control-group.label class="required font-semibold text-xs text-[#0f172a] mb-1.5 block">
                                        Confirm Password
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="password"
                                        class="w-full h-11 rounded-lg border border-[#cbd5e1] px-3.5 text-sm text-[#0f172a] placeholder-[#94a3b8] focus:border-[#0f4a3e] focus:ring-1 focus:ring-[#0f4a3e] focus:outline-none"
                                        id="personal-password-confirm"
                                        name="password_confirmation"
                                        rules="confirmed:@password"
                                        value=""
                                        label="Confirm Password"
                                        placeholder="••••••••"
                                        aria-label="Confirm Password"
                                        autocomplete="new-password"
                                        aria-required="true"
                                    />

                                    <x-shop::form.control-group.error control-name="password_confirmation" class="text-red-600 text-xs mt-1" />
                                </x-shop::form.control-group>

                                <!-- Show Password Toggle -->
                                <div class="flex items-center gap-2 mb-4 text-xs text-[#475569]">
                                    <input
                                        type="checkbox"
                                        id="show-password-personal"
                                        class="rounded border-[#cbd5e1] text-[#0f4a3e] focus:ring-[#0f4a3e]"
                                        onchange="
                                            const type = this.checked ? 'text' : 'password';
                                            document.getElementById('personal-password').type = type;
                                            document.getElementById('personal-password-confirm').type = type;
                                        "
                                    />
                                    <label for="show-password-personal" class="cursor-pointer select-none">
                                        Show passwords
                                    </label>
                                </div>

                                <!-- Newsletter Subscription if enabled -->
                                @if (core()->getConfigData('customer.settings.create_new_account_options.news_letter'))
                                    <div class="mb-4 flex items-center gap-2 text-xs text-[#475569]">
                                        <input
                                            type="checkbox"
                                            name="is_subscribed"
                                            id="is-subscribed-personal"
                                            class="rounded border-[#cbd5e1] text-[#0f4a3e] focus:ring-[#0f4a3e]"
                                        />
                                        <label for="is-subscribed-personal" class="cursor-pointer select-none leading-relaxed">
                                            Receive quarterly wholesale harvest reports, bulk inventory schedules, and supply updates.
                                        </label>
                                    </div>
                                @endif

                                <!-- GDPR Agreement if enabled -->
                                @if(core()->getConfigData('general.gdpr.settings.enabled') && core()->getConfigData('general.gdpr.agreement.enabled'))
                                    <div class="mb-4">
                                        <div class="flex items-center gap-2 text-xs text-[#475569]">
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
                                                    class="cursor-pointer text-[#0f4a3e] font-semibold hover:underline"
                                                    @click="$refs.termsModal.open()"
                                                >
                                                    Terms & Conditions
                                                </span>
                                            @endif
                                        </div>
                                        <x-shop::form.control-group.error control-name="agreement" class="text-red-600 text-xs mt-1" />
                                    </div>
                                @endif

                                <!-- Captcha if enabled -->
                                @if (core()->getConfigData('customer.captcha.credentials.status'))
                                    <x-shop::form.control-group class="mb-4">
                                        {!! \Webkul\Customer\Facades\Captcha::render() !!}
                                        <x-shop::form.control-group.error control-name="recaptcha_token" class="text-red-600 text-xs mt-1" />
                                    </x-shop::form.control-group>
                                @endif

                                <!-- Submit Button (Full Width) -->
                                <div class="mt-5 w-full">
                                    <button
                                        type="submit"
                                        class="w-full h-11 sm:h-12 rounded-lg bg-[#0f4a3e] hover:bg-[#17705d] text-white font-bold text-sm shadow-sm transition-colors flex items-center justify-center gap-2 cursor-pointer"
                                    >
                                        <span>Create Wholesale Account</span>
                                        <span>&rarr;</span>
                                    </button>
                                </div>

                                {!! view_render_event('bagisto.shop.customers.signup_form_controls.after') !!}
                            </x-shop::form>
                        </div>

                        {{-- ============================================================== --}}
                        {{-- PANEL 2: Corporate Account Form (Single-Column Flow)           --}}
                        {{-- ============================================================== --}}
                        @if ($b2bActive)
                            <div id="panel-company" class="{{ $activeTab === 'company' ? '' : 'hidden' }} w-full">
                                <x-shop::form
                                    :action="route('shop.companies.register.store')"
                                    enctype="multipart/form-data"
                                >
                                    <input type="hidden" name="account_type" value="company">

                                    <!-- Contact First Name (Single Column) -->
                                    <x-shop::form.control-group class="mb-3.5 w-full">
                                        <x-shop::form.control-group.label class="required font-semibold text-xs text-[#0f172a] mb-1.5 block">
                                            Contact First Name
                                        </x-shop::form.control-group.label>

                                        <x-shop::form.control-group.control
                                            type="text"
                                            class="w-full h-11 rounded-lg border border-[#cbd5e1] px-3.5 text-sm text-[#0f172a] placeholder-[#94a3b8] focus:border-[#0f4a3e] focus:ring-1 focus:ring-[#0f4a3e] focus:outline-none"
                                            name="first_name"
                                            rules="required"
                                            :value="old('first_name')"
                                            label="First Name"
                                            placeholder="Jane"
                                            aria-label="First Name"
                                            autocomplete="given-name"
                                            aria-required="true"
                                        />

                                        <x-shop::form.control-group.error control-name="first_name" class="text-red-600 text-xs mt-1" />
                                    </x-shop::form.control-group>

                                    <!-- Contact Last Name (Single Column) -->
                                    <x-shop::form.control-group class="mb-3.5 w-full">
                                        <x-shop::form.control-group.label class="required font-semibold text-xs text-[#0f172a] mb-1.5 block">
                                            Contact Last Name
                                        </x-shop::form.control-group.label>

                                        <x-shop::form.control-group.control
                                            type="text"
                                            class="w-full h-11 rounded-lg border border-[#cbd5e1] px-3.5 text-sm text-[#0f172a] placeholder-[#94a3b8] focus:border-[#0f4a3e] focus:ring-1 focus:ring-[#0f4a3e] focus:outline-none"
                                            name="last_name"
                                            rules="required"
                                            :value="old('last_name')"
                                            label="Last Name"
                                            placeholder="Doe"
                                            aria-label="Last Name"
                                            autocomplete="family-name"
                                            aria-required="true"
                                        />

                                        <x-shop::form.control-group.error control-name="last_name" class="text-red-600 text-xs mt-1" />
                                    </x-shop::form.control-group>

                                    <!-- Business Name (Single Column) -->
                                    <x-shop::form.control-group class="mb-3.5 w-full">
                                        <x-shop::form.control-group.label class="required font-semibold text-xs text-[#0f172a] mb-1.5 block">
                                            Business / Entity Name
                                        </x-shop::form.control-group.label>

                                        <x-shop::form.control-group.control
                                            type="text"
                                            class="w-full h-11 rounded-lg border border-[#cbd5e1] px-3.5 text-sm text-[#0f172a] placeholder-[#94a3b8] focus:border-[#0f4a3e] focus:ring-1 focus:ring-[#0f4a3e] focus:outline-none"
                                            name="business_name"
                                            rules="required"
                                            :value="old('business_name')"
                                            label="Business Name"
                                            placeholder="Acme Botanicals LLC"
                                            aria-label="Business Name"
                                            autocomplete="organization"
                                            aria-required="true"
                                        />

                                        <x-shop::form.control-group.error control-name="business_name" class="text-red-600 text-xs mt-1" />
                                    </x-shop::form.control-group>

                                    <!-- Business Phone (Single Column) -->
                                    <x-shop::form.control-group class="mb-3.5 w-full">
                                        <x-shop::form.control-group.label class="required font-semibold text-xs text-[#0f172a] mb-1.5 block">
                                            Business Phone Number
                                        </x-shop::form.control-group.label>

                                        <x-shop::form.control-group.control
                                            type="text"
                                            class="w-full h-11 rounded-lg border border-[#cbd5e1] px-3.5 text-sm text-[#0f172a] placeholder-[#94a3b8] focus:border-[#0f4a3e] focus:ring-1 focus:ring-[#0f4a3e] focus:outline-none"
                                            name="phone"
                                            rules="required"
                                            :value="old('phone')"
                                            label="Phone Number"
                                            placeholder="+1 (555) 000-0000"
                                            aria-label="Business Phone Number"
                                            autocomplete="tel"
                                            aria-required="true"
                                        />

                                        <x-shop::form.control-group.error control-name="phone" class="text-red-600 text-xs mt-1" />
                                    </x-shop::form.control-group>

                                    <!-- Corporate Email Address (Single Column) -->
                                    <x-shop::form.control-group class="mb-3.5 w-full">
                                        <x-shop::form.control-group.label class="required font-semibold text-xs text-[#0f172a] mb-1.5 block">
                                            Corporate Email Address
                                        </x-shop::form.control-group.label>

                                        <x-shop::form.control-group.control
                                            type="email"
                                            class="w-full h-11 rounded-lg border border-[#cbd5e1] px-3.5 text-sm text-[#0f172a] placeholder-[#94a3b8] focus:border-[#0f4a3e] focus:ring-1 focus:ring-[#0f4a3e] focus:outline-none"
                                            name="email"
                                            rules="required|email"
                                            :value="old('email')"
                                            label="Corporate Email Address"
                                            placeholder="purchasing@company.com"
                                            aria-label="Corporate Email Address"
                                            autocomplete="email"
                                            aria-required="true"
                                        />

                                        <x-shop::form.control-group.error control-name="email" class="text-red-600 text-xs mt-1" />
                                    </x-shop::form.control-group>

                                    <!-- Password (Single Column) -->
                                    <x-shop::form.control-group class="mb-3.5 w-full">
                                        <x-shop::form.control-group.label class="required font-semibold text-xs text-[#0f172a] mb-1.5 block">
                                            Password (min. 6 characters)
                                        </x-shop::form.control-group.label>

                                        <x-shop::form.control-group.control
                                            type="password"
                                            class="w-full h-11 rounded-lg border border-[#cbd5e1] px-3.5 text-sm text-[#0f172a] placeholder-[#94a3b8] focus:border-[#0f4a3e] focus:ring-1 focus:ring-[#0f4a3e] focus:outline-none"
                                            id="company-password"
                                            name="password"
                                            rules="required|min:6"
                                            :value="old('password')"
                                            label="Password"
                                            placeholder="••••••••"
                                            ref="companyPassword"
                                            aria-label="Password"
                                            autocomplete="new-password"
                                            aria-required="true"
                                        />

                                        <x-shop::form.control-group.error control-name="password" class="text-red-600 text-xs mt-1" />
                                    </x-shop::form.control-group>

                                    <!-- Confirm Password (Single Column) -->
                                    <x-shop::form.control-group class="mb-2.5 w-full">
                                        <x-shop::form.control-group.label class="required font-semibold text-xs text-[#0f172a] mb-1.5 block">
                                            Confirm Password
                                        </x-shop::form.control-group.label>

                                        <x-shop::form.control-group.control
                                            type="password"
                                            class="w-full h-11 rounded-lg border border-[#cbd5e1] px-3.5 text-sm text-[#0f172a] placeholder-[#94a3b8] focus:border-[#0f4a3e] focus:ring-1 focus:ring-[#0f4a3e] focus:outline-none"
                                            id="company-password-confirm"
                                            name="password_confirmation"
                                            rules="confirmed:@companyPassword"
                                            value=""
                                            label="Confirm Password"
                                            placeholder="••••••••"
                                            aria-label="Confirm Password"
                                            autocomplete="new-password"
                                            aria-required="true"
                                        />

                                        <x-shop::form.control-group.error control-name="password_confirmation" class="text-red-600 text-xs mt-1" />
                                    </x-shop::form.control-group>

                                    <!-- Show Password Toggle -->
                                    <div class="flex items-center gap-2 mb-4 text-xs text-[#475569]">
                                        <input
                                            type="checkbox"
                                            id="show-password-company"
                                            class="rounded border-[#cbd5e1] text-[#0f4a3e] focus:ring-[#0f4a3e]"
                                            onchange="
                                                const type = this.checked ? 'text' : 'password';
                                                document.getElementById('company-password').type = type;
                                                document.getElementById('company-password-confirm').type = type;
                                            "
                                        />
                                        <label for="show-password-company" class="cursor-pointer select-none">
                                            Show passwords
                                        </label>
                                    </div>

                                    <!-- Newsletter Subscription if enabled -->
                                    @if (core()->getConfigData('customer.settings.create_new_account_options.news_letter'))
                                        <div class="mb-4 flex items-center gap-2 text-xs text-[#475569]">
                                            <input
                                                type="checkbox"
                                                name="is_subscribed"
                                                id="is-subscribed-company"
                                                class="rounded border-[#cbd5e1] text-[#0f4a3e] focus:ring-[#0f4a3e]"
                                            />
                                            <label for="is-subscribed-company" class="cursor-pointer select-none leading-relaxed">
                                                Receive quarterly wholesale harvest reports, bulk inventory schedules, and supply updates.
                                            </label>
                                        </div>
                                    @endif

                                    <!-- GDPR Agreement if enabled -->
                                    @if(core()->getConfigData('general.gdpr.settings.enabled') && core()->getConfigData('general.gdpr.agreement.enabled'))
                                        <div class="mb-4">
                                            <div class="flex items-center gap-2 text-xs text-[#475569]">
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
                                                        class="cursor-pointer text-[#0f4a3e] font-semibold hover:underline"
                                                        @click="$refs.termsModal.open()"
                                                    >
                                                        Terms & Conditions
                                                    </span>
                                                @endif
                                            </div>
                                            <x-shop::form.control-group.error control-name="agreement" class="text-red-600 text-xs mt-1" />
                                        </div>
                                    @endif

                                    <!-- Captcha if enabled -->
                                    @if (core()->getConfigData('customer.captcha.credentials.status'))
                                        <x-shop::form.control-group class="mb-4">
                                            {!! \Webkul\Customer\Facades\Captcha::render() !!}
                                            <x-shop::form.control-group.error control-name="recaptcha_token" class="text-red-600 text-xs mt-1" />
                                        </x-shop::form.control-group>
                                    @endif

                                    <!-- Submit Button (Full Width) -->
                                    <div class="mt-5 w-full">
                                        <button
                                            type="submit"
                                            class="w-full h-11 sm:h-12 rounded-lg bg-[#0f4a3e] hover:bg-[#17705d] text-white font-bold text-sm shadow-sm transition-colors flex items-center justify-center gap-2 cursor-pointer"
                                        >
                                            <span>Register Corporate Account</span>
                                            <span>&rarr;</span>
                                        </button>
                                    </div>
                                </x-shop::form>
                            </div>
                        @endif

                        <!-- Future SSO Placeholder Container (Clean, zero clutter, ready for OAuth integration) -->
                        <div id="future-sso-container" class="hidden"></div>

                        <!-- Footer Navigation Links Inside Card: "Already a member? Sign In" -->
                        <div class="mt-6 pt-5 border-t border-[#e2e8f0] text-center text-xs text-[#64748b]">
                            <p class="mb-1 text-[#64748b]">Already a member?</p>
                            <a href="{{ route('shop.customer.session.index') }}" class="font-bold text-[#0f4a3e] hover:text-[#17705d] hover:underline text-sm inline-flex items-center gap-1">
                                <span>Sign In</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- ========================================================== --}}
                {{-- RIGHT PANEL: Full-Height Botanical Composition (~52-54%)   --}}
                {{-- ========================================================== --}}
                <div class="pk-register-visual-panel">
                    <div class="pk-visual-backdrop">
                        <div class="pk-visual-overlay">

                            <!-- Upper Section: Hero Branding, Headings & Tagline -->
                            <div class="pk-visual-hero">
                                <div class="mb-4">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold text-[#0f4a3e] bg-white/90 border border-[#d9e6e1] shadow-sm">
                                        <span class="w-2 h-2 rounded-full bg-[#17705d]"></span>
                                        PurNatureK B2B Sourcing
                                    </span>
                                </div>

                                <h2 class="pk-visual-heading">
                                    Premium Botanical Solutions for Your Business
                                </h2>

                                <p class="pk-visual-tagline">
                                    Quality &bull; Compliance &bull; Partnership
                                </p>

                                <p class="pk-visual-description">
                                    Direct commercial access to pure, high-grade botanical extracts, active herbal compounds, and tailored bulk supply schedules designed for professional formulators and supplement manufacturers.
                                </p>
                            </div>

                            <!-- Middle Section: Three Distributed Benefit Cards -->
                            <div class="pk-visual-benefits-grid">
                                <!-- Benefit 1 -->
                                <div class="pk-benefit-card">
                                    <div class="pk-benefit-icon-box">
                                        <svg class="pk-benefit-svg" style="width: 16px; height: 16px; max-width: 16px; max-height: 16px;" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                    <div class="pk-benefit-content">
                                        <h3 class="pk-benefit-title">Wholesale Product Access</h3>
                                        <p class="pk-benefit-text">
                                            Direct access to wholesale catalog specifications, botanical grades, minimum order quantities (MOQ), and packaging configurations.
                                        </p>
                                    </div>
                                </div>

                                <!-- Benefit 2 -->
                                <div class="pk-benefit-card">
                                    <div class="pk-benefit-icon-box">
                                        <svg class="pk-benefit-svg" style="width: 16px; height: 16px; max-width: 16px; max-height: 16px;" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div class="pk-benefit-content">
                                        <h3 class="pk-benefit-title">Supply & Product Documentation</h3>
                                        <p class="pk-benefit-text">
                                            Direct member access to technical data sheets, MSDS documentation, batch test overviews, and certificates of analysis (COAs).
                                        </p>
                                    </div>
                                </div>

                                <!-- Benefit 3 -->
                                <div class="pk-benefit-card">
                                    <div class="pk-benefit-icon-box">
                                        <svg class="pk-benefit-svg" style="width: 16px; height: 16px; max-width: 16px; max-height: 16px;" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                    </div>
                                    <div class="pk-benefit-content">
                                        <h3 class="pk-benefit-title">Direct Commercial Communication</h3>
                                        <p class="pk-benefit-text">
                                            Integrated messaging with PurNatureK commercial administrators for volume inquiries, custom compounding, and logistics scheduling.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Embedded Backdrop Illustration for Semantics & Testing -->
                            <img
                                src="{{ asset('themes/shop/purnaturek/images/botanical-wholesale-illustration.jpg') }}"
                                alt="PurNatureK Wholesale Botanical Sourcing"
                                class="pk-register-visual-img sr-only"
                                width="896"
                                height="1200"
                                loading="lazy"
                            >

                            <!-- Lower / Bottom Attribution Note -->
                            <div class="pk-visual-bottom-note">
                                <span>PurNatureK Bulk &bull; Commercial Partner Services</span>
                                <span class="font-semibold text-[#0f4a3e]">Authorized B2B Portal</span>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <!-- Page Bottom Corporate Note (Mobile & Tablet) -->
            <div class="lg:hidden py-6 text-center text-xs text-[#64748b]">
                <span>PurNatureK Bulk & Wholesale &bull; Authorized B2B Commercial Sourcing</span>
            </div>
        </div>

    @push('styles')
        <style>
            /* Base Box Sizing & Page Reset */
            *, *::before, *::after {
                box-sizing: border-box;
            }
            .pk-register-page {
                width: 100%;
                min-height: calc(100vh - 84px);
                background-color: #f8faf8;
                overflow-x: hidden;
            }

            /* Header */
            .pk-header-bar {
                width: 100%;
                background-color: #ffffff;
                border-bottom: 1px solid #e2e8f0;
            }

            /* Container and Responsive Two-Panel Layout */
            .pk-register-layout {
                display: flex;
                flex-direction: column;
                align-items: stretch;
                justify-content: flex-start;
                width: 100%;
                min-height: calc(100vh - 84px);
            }

            /* Left Form Panel */
            .pk-register-form-panel {
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem 1.25rem;
                background-color: #f8faf8;
            }

            .pk-register-card {
                width: 100%;
                max-width: 580px;
                background-color: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 1.25rem;
                padding: 2rem 1.5rem;
                box-shadow: 0 4px 24px -2px rgba(15, 74, 62, 0.05), 0 2px 8px -1px rgba(0, 0, 0, 0.03);
                margin: 0 auto;
            }

            @media (min-width: 640px) {
                .pk-register-card {
                    padding: 2.5rem 2.25rem;
                }
            }

            /* Consistent 48px Input Heights */
            .pk-register-card input[type="text"],
            .pk-register-card input[type="email"],
            .pk-register-card input[type="password"],
            .pk-register-card input[type="tel"] {
                height: 48px !important;
                border-radius: 0.5rem !important;
                border-color: #cbd5e1 !important;
                font-size: 0.875rem !important;
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }

            .pk-register-card input:focus {
                border-color: #0f4a3e !important;
                outline: none !important;
                box-shadow: 0 0 0 2px rgba(15, 74, 62, 0.15) !important;
            }

            .pk-register-card button[type="submit"] {
                height: 48px !important;
                border-radius: 0.5rem !important;
                background-color: #0f4a3e !important;
                color: #ffffff !important;
                font-weight: 700 !important;
                font-size: 0.9375rem !important;
                letter-spacing: 0.02em !important;
                transition: background-color 0.15s ease-in-out !important;
            }

            .pk-register-card button[type="submit"]:hover {
                background-color: #17705d !important;
            }

            /* Right Visual Panel */
            .pk-register-visual-panel {
                display: none !important;
                width: 100%;
            }

            .pk-visual-backdrop {
                width: 100%;
                height: 100%;
                min-height: 100%;
                background-color: #f7f4ed;
                background-image:
                    linear-gradient(180deg, rgba(247, 244, 237, 0.93) 0%, rgba(247, 244, 237, 0.85) 45%, rgba(247, 244, 237, 0.35) 75%, rgba(247, 244, 237, 0.12) 100%),
                    url('{{ asset("themes/shop/purnaturek/images/botanical-wholesale-backdrop.jpg") }}');
                background-size: cover;
                background-position: center bottom;
                background-repeat: no-repeat;
                display: flex;
                flex-direction: column;
            }

            .pk-visual-overlay {
                width: 100%;
                height: 100%;
                min-height: 100%;
                padding: 3rem 2.5rem 2.5rem 2.5rem;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }

            .pk-visual-hero {
                max-width: 580px;
            }

            .pk-visual-heading {
                font-size: 1.875rem;
                line-height: 1.25;
                font-weight: 800;
                color: #0f4a3e;
                letter-spacing: -0.025em;
                margin-bottom: 0.5rem;
            }

            .pk-visual-tagline {
                font-size: 0.875rem;
                font-weight: 700;
                color: #17705d;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                margin-bottom: 0.75rem;
            }

            .pk-visual-description {
                font-size: 0.875rem;
                line-height: 1.6;
                color: #334155;
                margin-bottom: 1.75rem;
                max-width: 540px;
            }

            .pk-visual-benefits-grid {
                display: flex;
                flex-direction: column;
                gap: 0.875rem;
                max-width: 580px;
            }

            .pk-benefit-card {
                background: rgba(255, 255, 255, 0.92);
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
                border: 1px solid rgba(226, 232, 240, 0.85);
                border-radius: 0.875rem;
                padding: 1rem 1.25rem;
                display: flex;
                align-items: flex-start;
                gap: 1rem;
                box-shadow: 0 2px 10px rgba(15, 74, 62, 0.04);
            }

            .pk-benefit-icon-box {
                width: 36px;
                height: 36px;
                min-width: 36px;
                max-width: 36px;
                border-radius: 0.5rem;
                background-color: #f0f7f4;
                border: 1px solid #d9e6e1;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #0f4a3e;
                margin-top: 2px;
            }

            .pk-benefit-svg {
                width: 18px;
                height: 18px;
                max-width: 18px;
                max-height: 18px;
            }

            .pk-benefit-content {
                flex: 1;
            }

            .pk-benefit-title {
                font-size: 0.875rem;
                font-weight: 700;
                color: #0f172a;
                line-height: 1.3;
            }

            .pk-benefit-text {
                font-size: 0.8125rem;
                line-height: 1.45;
                color: #475569;
                margin-top: 0.25rem;
            }

            .pk-visual-bottom-note {
                margin-top: 2rem;
                padding-top: 1rem;
                border-top: 1px solid rgba(226, 232, 240, 0.8);
                font-size: 0.6875rem;
                color: #64748b;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            /* Desktop Breakpoint: min-width 1024px */
            @media (min-width: 1024px) {
                .pk-register-layout {
                    flex-direction: row;
                    align-items: stretch;
                }

                .pk-register-form-panel {
                    width: 48%;
                    flex: 0 0 48%;
                    padding: 3rem 2rem;
                }

                .pk-register-card {
                    max-width: 580px;
                    padding: 2.75rem 2.5rem;
                }

                .pk-register-visual-panel {
                    display: flex !important;
                    flex-direction: column;
                    width: 52%;
                    flex: 1 1 52%;
                    min-height: 100%;
                }
            }

            /* Large Desktop: min-width 1440px / 1648px */
            @media (min-width: 1440px) {
                .pk-register-form-panel {
                    width: 47%;
                    flex: 0 0 47%;
                    padding: 3.5rem 3rem;
                }

                .pk-register-card {
                    max-width: 600px;
                    padding: 3rem 2.75rem;
                }

                .pk-register-visual-panel {
                    width: 53%;
                    flex: 1 1 53%;
                }

                .pk-visual-overlay {
                    padding: 4rem 4rem 3rem 4rem;
                }

                .pk-visual-heading {
                    font-size: 2.25rem;
                }
            }

            /* Minimalist segmented tab control (Tokopedia-inspired) */
            .pk-segment-tab {
                flex: 1;
                padding: 0.625rem 0.5rem;
                font-size: 0.8125rem;
                font-weight: 600;
                text-align: center;
                border-radius: 0.5rem;
                color: #475569;
                background-color: transparent;
                transition: all 0.15s ease-in-out;
                cursor: pointer;
                border: 1px solid transparent;
                line-height: 1.25;
            }
            .pk-segment-tab:hover {
                color: #0f4a3e;
            }
            .pk-segment-tab.pk-segment--active {
                background-color: #ffffff;
                color: #0f4a3e;
                border-color: #e2e8f0;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                font-weight: 700;
            }
        </style>
    @endpush

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
                        personalTab.classList.add('pk-segment--active');
                        personalTab.setAttribute('aria-selected', 'true');
                    }
                    if (companyTab) {
                        companyTab.classList.remove('pk-segment--active');
                        companyTab.setAttribute('aria-selected', 'false');
                    }
                } else {
                    if (personalPanel) personalPanel.classList.add('hidden');
                    if (companyPanel) companyPanel.classList.remove('hidden');
                    if (companyTab) {
                        companyTab.classList.add('pk-segment--active');
                        companyTab.setAttribute('aria-selected', 'true');
                    }
                    if (personalTab) {
                        personalTab.classList.remove('pk-segment--active');
                        personalTab.setAttribute('aria-selected', 'false');
                    }
                }
            }

            // Provide backward compatibility alias if external scripts invoke b2bSwitchTab
            window.b2bSwitchTab = pkSwitchRegisterTab;
        </script>
    @endpush

    <!-- Terms & Conditions Modal (GDPR agreement) -->
    <x-shop::modal ref="termsModal">
        <x-slot:toggle></x-slot>

        <x-slot:header class="!p-5 border-b border-[#e2e8f0]">
            <p class="font-bold text-sm text-[#0f4a3e]">
                Terms & Conditions
            </p>
        </x-slot>

        <x-slot:content class="!p-5">
            <div class="max-h-[500px] overflow-auto text-xs text-[#475569] leading-relaxed">
                {!! core()->getConfigData('general.gdpr.agreement.agreement_content') !!}
            </div>
        </x-slot>
    </x-shop::modal>
</x-shop::layouts>

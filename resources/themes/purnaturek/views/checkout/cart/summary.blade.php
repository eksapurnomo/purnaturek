<div class="pk-panel w-[418px] max-w-full max-md:w-full" style="align-self: flex-start;">
    {!! view_render_event('bagisto.shop.checkout.cart.summary.title.before') !!}

    <p
        class="pk-panel__title"
        role="heading"
        aria-level="1"
    >
        @lang('shop::app.checkout.cart.summary.cart-summary')
    </p>

    {!! view_render_event('bagisto.shop.checkout.cart.summary.title.after') !!}

    <div class="mt-5 grid gap-3.5">
        @if (core()->getConfigData('sales.checkout.shopping_cart.estimate_shipping'))
            <template v-if="cart.have_stockable_items">
                @include('shop::checkout.cart.summary.estimate-shipping')
            </template>
        @endif

        {!! view_render_event('bagisto.shop.checkout.cart.summary.sub_total.before') !!}

        <template v-if="displayTax.subtotal == 'including_tax'">
            <div class="flex justify-between ltr:text-right rtl:text-left text-sm">
                <p>@lang('shop::app.checkout.cart.summary.sub-total')</p>
                <p class="font-semibold">@{{ cart.formatted_sub_total_incl_tax }}</p>
            </div>
        </template>

        <template v-else-if="displayTax.subtotal == 'both'">
            <div class="flex justify-between ltr:text-right rtl:text-left text-sm">
                <p>@lang('shop::app.checkout.cart.summary.sub-total')</p>
                <div>
                    <p class="font-semibold">@{{ cart.formatted_sub_total }}</p>
                    <p class="text-xs italic pk-text-muted">
                        @lang('shop::app.checkout.cart.summary.incl-tax') @{{ cart.formatted_sub_total_incl_tax }}
                    </p>
                </div>
            </div>
        </template>

        <template v-else>
            <div class="flex justify-between ltr:text-right rtl:text-left text-sm">
                <p>@lang('shop::app.checkout.cart.summary.sub-total')</p>
                <p class="font-semibold">@{{ cart.formatted_sub_total }}</p>
            </div>
        </template>

        {!! view_render_event('bagisto.shop.checkout.cart.summary.sub_total.after') !!}

        {!! view_render_event('bagisto.shop.checkout.cart.summary.discount_amount.before') !!}

        <template v-if="cart.discount_amount && parseFloat(cart.discount_amount) > 0">
            <div
                class="flex justify-between ltr:text-right rtl:text-left text-sm"
                v-if="parseFloat(cart.items_discount_amount || 0) <= 0 || parseFloat(cart.shipping_discount_amount || 0) <= 0"
            >
                <p class="text-red-700">@lang('shop::app.checkout.cart.summary.discount-amount')</p>
                <p class="font-semibold text-red-700">- @{{ cart.formatted_discount_amount }}</p>
            </div>

            <div class="flex flex-col gap-2 border-y border-zinc-200 py-2" v-else>
                <div
                    class="flex cursor-pointer justify-between ltr:text-right rtl:text-left text-sm"
                    @click="cart.show_discount_breakdown = ! cart.show_discount_breakdown"
                >
                    <p class="text-red-700">@lang('shop::app.checkout.cart.summary.discount-amount')</p>
                    <p class="flex items-center gap-1 font-semibold text-red-700">
                        - @{{ cart.formatted_discount_amount }}
                        <span class="text-lg" :class="{'icon-arrow-up': cart.show_discount_breakdown, 'icon-arrow-down': ! cart.show_discount_breakdown}"></span>
                    </p>
                </div>

                <div class="flex flex-col gap-1" v-show="cart.show_discount_breakdown">
                    <div class="flex justify-between gap-1 text-xs pk-text-muted">
                        <p>@lang('shop::app.checkout.cart.summary.items-discount')</p>
                        <p>- @{{ cart.formatted_items_discount_amount }}</p>
                    </div>
                    <div class="flex justify-between gap-1 text-xs pk-text-muted">
                        <p>@lang('shop::app.checkout.cart.summary.shipping-discount')</p>
                        <p>- @{{ cart.formatted_shipping_discount_amount }}</p>
                    </div>
                </div>
            </div>
        </template>

        {!! view_render_event('bagisto.shop.checkout.cart.summary.discount_amount.after') !!}

        {!! view_render_event('bagisto.shop.checkout.cart.summary.coupon.before') !!}

        @include('shop::checkout.coupon')

        {!! view_render_event('bagisto.shop.checkout.cart.summary.coupon.after') !!}

        {!! view_render_event('bagisto.shop.checkout.onepage.summary.delivery_charges.before') !!}

        <template v-if="displayTax.shipping == 'including_tax'">
            <div class="flex justify-between ltr:text-right rtl:text-left text-sm">
                <p>@lang('shop::app.checkout.cart.summary.delivery-charges')</p>
                <p class="font-semibold">+ @{{ cart.formatted_shipping_amount_incl_tax }}</p>
            </div>
        </template>
        <template v-else-if="displayTax.shipping == 'both'">
            <div class="flex justify-between ltr:text-right rtl:text-left text-sm">
                <p>@lang('shop::app.checkout.cart.summary.delivery-charges')</p>
                <div>
                    <p class="font-semibold">+ @{{ cart.formatted_shipping_amount }}</p>
                    <p class="text-xs italic pk-text-muted">
                        @lang('shop::app.checkout.cart.summary.incl-tax') @{{ cart.formatted_shipping_amount_incl_tax }}
                    </p>
                </div>
            </div>
        </template>
        <template v-else>
            <div class="flex justify-between ltr:text-right rtl:text-left text-sm">
                <p>@lang('shop::app.checkout.cart.summary.delivery-charges')</p>
                <p class="font-semibold">+ @{{ cart.formatted_shipping_amount }}</p>
            </div>
        </template>

        {!! view_render_event('bagisto.shop.checkout.onepage.summary.delivery_charges.after') !!}

        {!! view_render_event('bagisto.shop.checkout.cart.summary.tax.before') !!}

        <div class="flex justify-between ltr:text-right rtl:text-left text-sm" v-if="! cart.tax_total">
            <p>@lang('shop::app.checkout.cart.summary.tax')</p>
            <p class="font-semibold">+ @{{ cart.formatted_tax_total }}</p>
        </div>

        <div class="flex flex-col gap-2 border-y border-zinc-200 py-2" v-else>
            <div
                class="flex cursor-pointer justify-between ltr:text-right rtl:text-left text-sm"
                @click="cart.show_taxes = ! cart.show_taxes"
            >
                <p>@lang('shop::app.checkout.cart.summary.tax')</p>
                <p class="flex items-center gap-1.5 font-semibold">
                    <template v-if="displayTax.subtotal === 'including_tax'">
                        @{{ cart.formatted_tax_total }}
                        <span class="text-xs italic font-normal pk-text-muted">(@lang('shop::app.checkout.cart.summary.included'))</span>
                    </template>
                    <template v-else>+ @{{ cart.formatted_tax_total }}</template>
                    <span class="text-lg" :class="{'icon-arrow-up': cart.show_taxes, 'icon-arrow-down': ! cart.show_taxes}"></span>
                </p>
            </div>

            <div class="flex flex-col gap-1" v-show="cart.show_taxes">
                <div class="flex justify-between gap-1 text-xs pk-text-muted" v-for="(amount, index) in cart.applied_taxes">
                    <p>@{{ index }}</p>
                    <p>
                        <template v-if="displayTax.subtotal === 'including_tax'">@{{ amount }}</template>
                        <template v-else>+ @{{ amount }}</template>
                    </p>
                </div>
            </div>
        </div>

        {!! view_render_event('bagisto.shop.checkout.cart.summary.tax.after') !!}

        {!! view_render_event('bagisto.shop.checkout.cart.summary.grand_total.before') !!}

        <div class="flex justify-between border-t border-zinc-200 pt-3 ltr:text-right rtl:text-left">
            <p class="text-base font-bold pk-text-primary">@lang('shop::app.checkout.cart.summary.grand-total')</p>
            <p class="text-base font-bold pk-text-primary">@{{ cart.formatted_grand_total }}</p>
        </div>

        {!! view_render_event('bagisto.shop.checkout.cart.summary.grand_total.after') !!}

        {!! view_render_event('bagisto.shop.checkout.cart.summary.proceed_to_checkout.before') !!}

        @php
            $quotationBlockers = \App\Support\WholesalePricingManager::cartQuotationViolations(
                \Webkul\Checkout\Facades\Cart::getCart(),
                auth()->guard('customer')->id()
            );
        @endphp

        @if (count($quotationBlockers))
            <x-shop::portal.alert
                variant="warning"
                title="Approved Quotation Required"
            >
                Wholesale products require an approved commercial quotation before checkout. Submit a quotation request below; once approved, use &ldquo;Accept &amp; Add to Cart&rdquo; from the quotation to proceed.
            </x-shop::portal.alert>
        @else
            <a
                href="{{ route('shop.checkout.onepage.index') }}"
                class="pk-btn pk-btn--primary pk-btn--block mt-2"
            >
                @lang('shop::app.checkout.cart.summary.proceed-to-checkout') &rarr;
            </a>
        @endif

        {!! view_render_event('bagisto.shop.checkout.cart.summary.proceed_to_checkout.after') !!}

        @php
            $b2bCustomer = auth()->guard('customer')->user();
            $b2bMinimumAmount = (float) core()->getConfigData('b2b.quotes.settings.minimum_amount');
            $b2bMinimumAmountMessage = core()->getConfigData('b2b.quotes.settings.minimum_amount_message');
        @endphp

        @if (
            (bool) core()->getConfigData('b2b.general.settings.active')
            && $b2bCustomer
            && ($b2bCustomer->type === 'company' || $b2bCustomer->company_role_id)
        )
            <template v-if="cart && cart.items && cart.items.length">
                <button
                    v-if="parseFloat(cart.grand_total) >= {{ core()->convertPrice($b2bMinimumAmount) }}"
                    type="button"
                    class="pk-btn pk-btn--earth pk-btn--block mt-1"
                    @click="$emitter.emit('open-request-quote-modal')"
                >
                    @lang('b2b::app.shop.checkout.cart.request-quote-button')
                </button>

                <div v-else class="pk-alert pk-alert--danger mt-2">
                    <div class="pk-alert__body">
                        {{ $b2bMinimumAmountMessage ?: trans('b2b::app.shop.checkout.cart.minimum-amount-required') }}
                    </div>
                </div>

                @include('shop::checkout.cart.request-quote-modal')
            </template>
        @endif
    </div>
</div>


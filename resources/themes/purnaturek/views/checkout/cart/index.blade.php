<!-- SEO Meta Content -->
@push('meta')
    <meta name="description" content="@lang('shop::app.checkout.cart.index.cart')"/>
    <meta name="keywords" content="@lang('shop::app.checkout.cart.index.cart')"/>
@endPush

<x-shop::layouts
    :has-header="false"
    :has-feature="false"
    :has-footer="true"
>
    <x-slot:title>
        @lang('shop::app.checkout.cart.index.cart') — PurNatureK
    </x-slot>

    {!! view_render_event('bagisto.shop.checkout.cart.header.before') !!}

    <!-- PurNatureK Branded Cart Header -->
    <header class="pk-header">
        <div class="pk-header__inner">
            <div class="pk-header__left">
                {!! view_render_event('bagisto.shop.checkout.cart.logo.before') !!}

                <a
                    href="{{ auth()->guard('customer')->check() ? route('shop.customers.account.index') : route('shop.home.index') }}"
                    class="pk-brand"
                    aria-label="PurNatureK Members Portal"
                >
                    <img
                        src="{{ asset('themes/shop/purnaturek/images/logo-purnaturek-transparent.webp') }}"
                        alt="PUR NATUREK"
                        class="pk-brand__logo"
                        width="156"
                        height="38"
                    />
                    <span class="pk-brand__divider" aria-hidden="true"></span>
                    <span class="pk-brand__tag">Wholesale Cart</span>
                </a>

                {!! view_render_event('bagisto.shop.checkout.cart.logo.after') !!}
            </div>

            <div class="pk-header__actions">
                @auth('customer')
                    <a href="{{ route('shop.customers.account.index') }}" class="pk-header__link">
                        &larr; Member Workspace
                    </a>
                @endauth

                @guest('customer')
                    @include('shop::checkout.login')
                @endguest
            </div>
        </div>
    </header>

    {!! view_render_event('bagisto.shop.checkout.cart.header.after') !!}

    <div class="flex-auto" style="padding: 28px 0 56px;">
        <div class="pk-container">
            {!! view_render_event('bagisto.shop.checkout.cart.breadcrumbs.before') !!}

            @if (core()->getConfigData('general.general.breadcrumbs.shop'))
                <x-shop::breadcrumbs name="cart" />
            @endif

            {!! view_render_event('bagisto.shop.checkout.cart.breadcrumbs.after') !!}

            @php
                $errors = \Webkul\Checkout\Facades\Cart::getErrors();
            @endphp

            @if (! empty($errors) && $errors['error_code'] === 'MINIMUM_ORDER_AMOUNT')
                <div class="pk-alert pk-alert--warning mt-4">
                    <div class="pk-alert__body">{{ $errors['message'] }}: {{ $errors['amount'] }}</div>
                </div>
            @endif

            <v-cart ref="vCart">
                <x-shop::shimmer.checkout.cart :count="3" />
            </v-cart>
        </div>
    </div>

    @if (core()->getConfigData('sales.checkout.shopping_cart.cross_sell'))
        {!! view_render_event('bagisto.shop.checkout.cart.cross_sell_carousel.before') !!}

        <x-shop::products.carousel
            :title="trans('shop::app.checkout.cart.index.cross-sell.title')"
            :src="route('shop.api.checkout.cart.cross-sell.index')"
        />

        {!! view_render_event('bagisto.shop.checkout.cart.cross_sell_carousel.after') !!}
    @endif

    @pushOnce('scripts')
        <script type="text/x-template" id="v-cart-template">
            <div>
                <template v-if="isLoading">
                    <x-shop::shimmer.checkout.cart :count="3" />
                </template>

                <template v-else>
                    <div
                        class="mt-6 flex flex-wrap gap-10 pb-8 max-1060:flex-col max-md:mt-2 max-md:gap-6"
                        v-if="cart?.items?.length"
                    >
                        <div class="pk-panel flex flex-1 flex-col gap-5 min-w-0">
                            {!! view_render_event('bagisto.shop.checkout.cart.cart_mass_actions.before') !!}

                            <div class="flex items-center justify-between border-b border-zinc-200 pb-3">
                                <div class="flex select-none items-center">
                                    <input
                                        type="checkbox"
                                        id="select-all"
                                        class="peer hidden"
                                        v-model="allSelected"
                                        @change="selectAll"
                                    >

                                    <label
                                        class="icon-uncheck peer-checked:icon-check-box cursor-pointer text-2xl text-navyBlue peer-checked:text-navyBlue"
                                        for="select-all"
                                        tabindex="0"
                                        aria-label="@lang('shop::app.checkout.cart.index.select-all')"
                                    ></label>

                                    <span class="pk-serif text-xl font-semibold pk-text-primary ltr:ml-2.5 rtl:mr-2.5" role="heading" aria-level="2">
                                        @{{ "@lang('shop::app.checkout.cart.index.items-selected')".replace(':count', selectedItemsCount) }}
                                    </span>
                                </div>

                                <div v-if="selectedItemsCount">
                                    <span
                                        class="cursor-pointer text-sm font-semibold pk-text-primary hover:underline"
                                        role="button"
                                        tabindex="0"
                                        @click="removeSelectedItems"
                                    >
                                        @lang('shop::app.checkout.cart.index.remove')
                                    </span>

                                    @if (auth()->guard()->check())
                                        <span class="mx-2.5 ltr:border-r-2 rtl:border-l-2 border-zinc-200"></span>

                                        <span
                                            class="cursor-pointer text-sm font-semibold pk-text-primary hover:underline"
                                            role="button"
                                            tabindex="0"
                                            @click="moveToWishlistSelectedItems"
                                        >
                                            @lang('shop::app.checkout.cart.index.move-to-wishlist')
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {!! view_render_event('bagisto.shop.checkout.cart.cart_mass_actions.after') !!}

                            {!! view_render_event('bagisto.shop.checkout.cart.item.listing.before') !!}

                            <div class="grid gap-y-5" v-for="item in cart?.items">
                                <div class="flex justify-between gap-x-3 border-b border-zinc-200 pb-5">
                                    <div class="flex gap-x-4">
                                        <div class="mt-9 select-none">
                                            <input
                                                type="checkbox"
                                                :id="'item_' + item.id"
                                                class="peer hidden"
                                                v-model="item.selected"
                                                @change="updateAllSelected"
                                            >

                                            <label
                                                class="icon-uncheck peer-checked:icon-check-box cursor-pointer text-2xl text-navyBlue peer-checked:text-navyBlue"
                                                :for="'item_' + item.id"
                                                tabindex="0"
                                                aria-label="@lang('shop::app.checkout.cart.index.select-cart-item')"
                                            ></label>
                                        </div>

                                        {!! view_render_event('bagisto.shop.checkout.cart.item_image.before') !!}

                                        <a :href="'{{ route('shop.product_or_category.index', ':slug') }}'.replace(':slug', item.product_url_key)">
                                            <x-shop::media.images.lazy
                                                class="h-[96px] max-w-[96px] rounded border border-[#E4DDD0] max-md:h-20 max-md:max-w-20"
                                                ::src="item.base_image.small_image_url"
                                                ::alt="item.name"
                                                width="96"
                                                height="96"
                                                ::key="item.id"
                                                ::index="item.id"
                                            />
                                        </a>

                                        {!! view_render_event('bagisto.shop.checkout.cart.item_image.after') !!}

                                        <div class="grid place-content-start gap-y-2">
                                            {!! view_render_event('bagisto.shop.checkout.cart.item_name.before') !!}

                                            <a :href="'{{ route('shop.product_or_category.index', ':slug') }}'.replace(':slug', item.product_url_key)">
                                                <p class="text-base font-semibold pk-text-primary">
                                                    @{{ item.name }}
                                                </p>
                                            </a>

                                            {!! view_render_event('bagisto.shop.checkout.cart.item_name.after') !!}

                                            {!! view_render_event('bagisto.shop.checkout.cart.item_details.before') !!}

                                            <div class="grid select-none gap-1.5" v-if="item.options.length">
                                                <p
                                                    class="flex cursor-pointer items-center gap-x-2 text-xs font-semibold pk-text-muted"
                                                    @click="item.option_show = ! item.option_show"
                                                >
                                                    @lang('shop::app.checkout.cart.index.see-details')
                                                    <span class="text-lg" :class="{'icon-arrow-up': item.option_show, 'icon-arrow-down': ! item.option_show}"></span>
                                                </p>

                                                <div class="grid gap-1.5" v-show="item.option_show">
                                                    <template v-for="attribute in item.options">
                                                        <div>
                                                            <p class="text-xs font-semibold pk-text-muted">@{{ attribute.attribute_name + ':' }}</p>
                                                            <p class="text-xs">
                                                                <template v-if="attribute?.attribute_type === 'file'">
                                                                    <a :href="attribute.file_url" class="underline pk-text-primary" target="_blank" :download="attribute.file_name">
                                                                        @{{ attribute.file_name }}
                                                                    </a>
                                                                </template>
                                                                <template v-else>
                                                                    @{{ attribute.option_label }}
                                                                </template>
                                                            </p>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>

                                            {!! view_render_event('bagisto.shop.checkout.cart.item_details.after') !!}

                                            {!! view_render_event('bagisto.shop.checkout.cart.formatted_total.before') !!}

                                            <div class="md:hidden">
                                                <p class="text-base font-semibold pk-text-primary">
                                                    <template v-if="displayTax.prices == 'including_tax'">@{{ item.formatted_total_incl_tax }}</template>
                                                    <template v-else-if="displayTax.prices == 'both'">
                                                        @{{ item.formatted_total_incl_tax }}
                                                        <span class="text-xs font-normal">
                                                            @lang('shop::app.checkout.cart.index.excl-tax')
                                                            <span class="font-medium">@{{ item.formatted_total }}</span>
                                                        </span>
                                                    </template>
                                                    <template v-else>@{{ item.formatted_total }}</template>
                                                </p>
                                            </div>

                                            {!! view_render_event('bagisto.shop.checkout.cart.formatted_total.after') !!}

                                            {!! view_render_event('bagisto.shop.checkout.cart.quantity_changer.before') !!}

                                            <div class="flex items-center gap-3">
                                                <x-shop::quantity-changer
                                                    v-if="item.can_change_qty"
                                                    ::key="'qty-' + item.id + '-' + refreshKey"
                                                    class="flex max-w-max items-center gap-x-2 border border-[#1B3B2B] px-3 py-1"
                                                    name="quantity"
                                                    ::value="item?.quantity"
                                                    @change="setItemQuantity(item.id, $event)"
                                                />

                                                <span
                                                    class="hidden cursor-pointer text-xs font-semibold text-red-700 max-md:block"
                                                    role="button"
                                                    tabindex="0"
                                                    @click="removeItem(item.id)"
                                                >
                                                    @lang('shop::app.checkout.cart.index.remove')
                                                </span>
                                            </div>

                                            {!! view_render_event('bagisto.shop.checkout.cart.quantity_changer.after') !!}
                                        </div>
                                    </div>

                                    <div class="ltr:text-right rtl:text-left max-md:hidden">
                                        {!! view_render_event('bagisto.shop.checkout.cart.total.before') !!}

                                        <template v-if="displayTax.prices == 'including_tax'">
                                            <p class="text-lg font-semibold pk-text-primary">@{{ item.formatted_total_incl_tax }}</p>
                                        </template>
                                        <template v-else-if="displayTax.prices == 'both'">
                                            <p class="flex flex-col text-lg font-semibold pk-text-primary">
                                                @{{ item.formatted_total_incl_tax }}
                                                <span class="text-xs font-normal">
                                                    @lang('shop::app.checkout.cart.index.excl-tax')
                                                    <span class="font-medium">@{{ item.formatted_total }}</span>
                                                </span>
                                            </p>
                                        </template>
                                        <template v-else>
                                            <p class="text-lg font-semibold pk-text-primary">@{{ item.formatted_total }}</p>
                                        </template>

                                        {!! view_render_event('bagisto.shop.checkout.cart.total.after') !!}

                                        {!! view_render_event('bagisto.shop.checkout.cart.remove_button.before') !!}

                                        <span
                                            class="cursor-pointer text-xs font-semibold text-red-700 hover:underline"
                                            role="button"
                                            tabindex="0"
                                            @click="removeItem(item.id)"
                                        >
                                            @lang('shop::app.checkout.cart.index.remove')
                                        </span>

                                        {!! view_render_event('bagisto.shop.checkout.cart.remove_button.after') !!}
                                    </div>
                                </div>
                            </div>

                            {!! view_render_event('bagisto.shop.checkout.cart.item.listing.after') !!}

                            {!! view_render_event('bagisto.shop.checkout.cart.controls.before') !!}

                            <div class="flex flex-wrap justify-between gap-4 pt-2">
                                {!! view_render_event('bagisto.shop.checkout.cart.continue_shopping.before') !!}

                                <a
                                    class="pk-btn pk-btn--secondary"
                                    href="{{ auth()->guard('customer')->check() ? route('shop.customers.account.index') . '#catalog' : route('shop.home.index') }}"
                                >
                                    @lang('shop::app.checkout.cart.index.continue-shopping')
                                </a>

                                {!! view_render_event('bagisto.shop.checkout.cart.continue_shopping.after') !!}

                                {!! view_render_event('bagisto.shop.checkout.cart.update_cart.before') !!}

                                <x-shop::button
                                    class="pk-btn pk-btn--secondary"
                                    :title="trans('shop::app.checkout.cart.index.update-cart')"
                                    ::loading="isStoring"
                                    ::disabled="isStoring"
                                    @click="update()"
                                />

                                {!! view_render_event('bagisto.shop.checkout.cart.update_cart.after') !!}
                            </div>

                            {!! view_render_event('bagisto.shop.checkout.cart.controls.after') !!}
                        </div>

                        {!! view_render_event('bagisto.shop.checkout.cart.summary.before') !!}

                        @include('shop::checkout.cart.summary')

                        {!! view_render_event('bagisto.shop.checkout.cart.summary.after') !!}
                    </div>

                    <div class="pk-empty-state mt-6" v-else>
                        <h3 class="pk-empty-state__title">@lang('shop::app.checkout.cart.index.empty-product')</h3>
                        <p class="pk-empty-state__text">
                            Browse the wholesale product catalog or submit an RFQ from your member workspace.
                        </p>
                        <div class="pk-empty-state__actions">
                            <a
                                href="{{ auth()->guard('customer')->check() ? route('shop.customers.account.index') . '#catalog' : route('shop.home.index') }}"
                                class="pk-btn pk-btn--primary"
                            >
                                Return to Wholesale Portal &rarr;
                            </a>
                        </div>
                    </div>
                </template>
            </div>
        </script>

        <script type="module">
            app.component("v-cart", {
                template: '#v-cart-template',

                data() {
                    return {
                        refreshKey: 0,
                        cart: [],
                        allSelected: false,
                        applied: {
                            quantity: {},
                        },
                        displayTax: {
                            prices: "{{ core()->getConfigData('sales.taxes.shopping_cart.display_prices') }}",
                            subtotal: "{{ core()->getConfigData('sales.taxes.shopping_cart.display_subtotal') }}",
                            shipping: "{{ core()->getConfigData('sales.taxes.shopping_cart.display_shipping_amount') }}",
                        },
                        isLoading: true,
                        isStoring: false,
                    };
                },

                mounted() {
                    this.getCart();
                },

                computed: {
                    selectedItemsCount() {
                        return this.cart.items.filter(item => item.selected).length;
                    },
                },

                methods: {
                    getCart() {
                        this.$axios.get('{{ route('shop.api.checkout.cart.index') }}')
                            .then(response => {
                                this.cart = response.data.data;
                                this.isLoading = false;

                                if (response.data.message) {
                                    this.$emitter.emit('add-flash', { type: 'info', message: response.data.message });
                                }
                            })
                            .catch(error => {});
                    },

                    setCart(cart) {
                        this.cart = cart;
                    },

                    selectAll() {
                        for (let item of this.cart.items) {
                            item.selected = this.allSelected;
                        }
                    },

                    updateAllSelected() {
                        this.allSelected = this.cart.items.every(item => item.selected);
                    },

                    update() {
                        this.isStoring = true;

                        this.$axios.put('{{ route('shop.api.checkout.cart.update') }}', { qty: this.applied.quantity })
                            .then(response => {
                                if (response.data.data?.items !== undefined) {
                                    this.cart = response.data.data;
                                    this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                } else {
                                    this.$emitter.emit('add-flash', {
                                        type: 'warning',
                                        message: response.data.data?.message || response.data.message,
                                    });
                                }

                                this.isStoring = false;
                                this.applied.quantity = {};
                                this.refreshKey++;
                            })
                            .catch(error => {
                                this.isStoring = false;
                                this.applied.quantity = {};
                                this.refreshKey++;
                            });
                    },

                    setItemQuantity(itemId, quantity) {
                        this.applied.quantity[itemId] = quantity;
                    },

                    removeItem(itemId) {
                        this.$emitter.emit('open-confirm-modal', {
                            agree: () => {
                                this.$axios.post('{{ route('shop.api.checkout.cart.destroy') }}', {
                                        '_method': 'DELETE',
                                        'cart_item_id': itemId,
                                    })
                                    .then(response => {
                                        this.cart = response.data.data;
                                        this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                    })
                                    .catch(error => {});
                            }
                        });
                    },

                    removeSelectedItems() {
                        this.$emitter.emit('open-confirm-modal', {
                            agree: () => {
                                const selectedItemsIds = this.cart.items.flatMap(item => item.selected ? item.id : []);

                                this.$axios.post('{{ route('shop.api.checkout.cart.destroy_selected') }}', {
                                        '_method': 'DELETE',
                                        'ids': selectedItemsIds,
                                    })
                                    .then(response => {
                                        this.cart = response.data.data;
                                        this.$emitter.emit('update-mini-cart', response.data.data);
                                        this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                    })
                                    .catch(error => {});
                            }
                        });
                    },

                    moveToWishlistSelectedItems() {
                        this.$emitter.emit('open-confirm-modal', {
                            agree: () => {
                                const selectedItemsIds = this.cart.items.flatMap(item => item.selected ? item.id : []);
                                const selectedItemsQty = this.cart.items.filter(item => item.selected).map(item => this.applied.quantity[item.id] ?? item.quantity);

                                this.$axios.post('{{ route('shop.api.checkout.cart.move_to_wishlist') }}', {
                                        'ids': selectedItemsIds,
                                        'qty': selectedItemsQty
                                    })
                                    .then(response => {
                                        this.cart = response.data.data;
                                        this.$emitter.emit('update-mini-cart', response.data.data);
                                        this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                    })
                                    .catch(error => {});
                            }
                        });
                    },
                }
            });
        </script>
    @endpushOnce
</x-shop::layouts>


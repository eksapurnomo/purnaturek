<?php

namespace App\Providers;

use App\Http\Controllers\WholesaleQuickOrderController;
use App\Http\Controllers\WholesaleRequisitionListController;
use App\Http\Middleware\EnsureQuotationForCheckout;
use App\Http\Middleware\EnsureVerifiedBuyer;
use App\Http\Middleware\ProtectPrivateCatalog;
use App\Http\Requests\WholesaleProductForm;
use App\Support\WholesalePricingManager;
use App\View\Composers\MemberCatalogComposer;
use Barryvdh\Debugbar\Facades\Debugbar;
use Exception;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Webkul\Admin\Http\Requests\ProductForm;
use Webkul\B2BSuite\Http\Controllers\Shop\QuickOrderController;
use Webkul\B2BSuite\Http\Controllers\Shop\RequisitionListController;
use Webkul\Core\Http\Middleware\NoCacheMiddleware;
use Webkul\Shop\Http\Middleware\CacheResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Standard checkout routes guarded by EnsureQuotationForCheckout.
     */
    public const QUOTATION_GUARDED_ROUTES = [
        'shop.checkout.onepage.index',
        'shop.checkout.onepage.addresses.store',
        'shop.checkout.onepage.shipping_methods.store',
        'shop.checkout.onepage.payment_methods.store',
        'shop.checkout.onepage.summary',
        'shop.checkout.onepage.orders.store',
        'paypal.smart-button.create-order',
        'paypal.smart-button.capture-order',
        'paypal.standard.redirect',
        'paypal.standard.success',
        'stripe.standard.redirect',
        'stripe.payment.success',
    ];

    /**
     * Transaction routes guarded by EnsureVerifiedBuyer.
     * Only approved Verified Wholesale Buyers may initiate quotations, sample requests, purchase orders, or wholesale purchases.
     */
    public const VERIFIED_BUYER_GUARDED_ROUTES = [
        'b2b.shop.quotes.store',
        'shop.customers.account.quotes.submit_quote',
        'shop.customers.account.quotes.add_to_cart',
        'shop.customers.account.quotes.update',
        'shop.customers.account.purchase_orders.index',
        'shop.customers.account.purchase_orders.view',
        'shop.customers.account.quick_orders.index',
        'shop.customers.account.quick_orders.store',
        'shop.customers.account.requisitions.move_to_cart',
        'shop.checkout.onepage.index',
        'shop.checkout.onepage.orders.store',
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductForm::class, WholesaleProductForm::class);

        /**
         * Quick Order search / fetch-by-SKU must not disclose native or indicative prices of Model B products.
         */
        $this->app->bind(QuickOrderController::class, WholesaleQuickOrderController::class);

        /**
         * Requisition List must not disclose native Bagisto prices of Model B products.
         */
        $this->app->bind(RequisitionListController::class, WholesaleRequisitionListController::class);

        /**
         * Customer session controller override: secure session invalidation and token regeneration on logout.
         */
        $this->app->bind(
            \Webkul\Shop\Http\Controllers\Customer\SessionController::class,
            \App\Http\Controllers\WholesaleCustomerSessionController::class
        );

        $allowedIPs = array_map('trim', explode(',', config('app.debug_allowed_ips', '')));

        $allowedIPs = array_filter($allowedIPs);

        if (empty($allowedIPs)) {
            return;
        }

        if (in_array(Request::ip(), $allowedIPs)) {
            Debugbar::enable();
        } else {
            Debugbar::disable();
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ParallelTesting::setUpTestDatabase(function (string $database, int $token) {
            Artisan::call('db:seed');
        });

        /**
         * PurNatureK Members Portal: feed the catalog grid to the customer portal landing view.
         */
        View::composer('shop::customers.account.index', MemberCatalogComposer::class);

        /**
         * Ensure the wholesale pricing EAV attributes and group exist before rendering the Admin product edit view.
         */
        View::composer('admin::catalog.products.edit', function () {
            WholesalePricingManager::ensureAttributesProvisioned();
        });

        /**
         * PurNatureK Members Portal: register Buyer Verifications, Product Documents, and Message Inbox in admin menu and ACL.
         */
        config([
            'menu.admin' => array_merge(config('menu.admin', []), [
                [
                    'key'   => 'b2b.buyer-verifications',
                    'name'  => 'Buyer Verifications',
                    'route' => 'admin.b2b.buyer_verifications.index',
                    'sort'  => 5,
                    'icon'  => 'icon-customers',
                ],
                [
                    'key'   => 'b2b.messages',
                    'name'  => 'Message Inbox',
                    'route' => 'admin.b2b.conversations.index',
                    'sort'  => 6,
                    'icon'  => 'icon-mail',
                ],
                [
                    'key'   => 'b2b.contract-templates',
                    'name'  => 'Contract Templates',
                    'route' => 'admin.b2b.contract-templates.index',
                    'sort'  => 7,
                    'icon'  => 'icon-settings',
                ],
                [
                    'key'   => 'b2b.member-contracts',
                    'name'  => 'Member Contracts',
                    'route' => 'admin.b2b.member-contracts.index',
                    'sort'  => 8,
                    'icon'  => 'icon-sales',
                ],
                [
                    'key'   => 'b2b.company-supply',
                    'name'  => 'Company & Supply Information',
                    'route' => 'admin.b2b.company_supply.edit',
                    'sort'  => 9,
                    'icon'  => 'icon-settings',
                ],
                [
                    'key'   => 'b2b.partnership-information',
                    'name'  => 'Wholesale Partnership Information',
                    'route' => 'admin.b2b.partnership_information.edit',
                    'sort'  => 10,
                    'icon'  => 'icon-settings',
                ],
                [
                    'key'   => 'catalog.product-documents',
                    'name'  => 'Product Documents',
                    'route' => 'admin.catalog.product_documents.index',
                    'sort'  => 3,
                    'icon'  => 'icon-attribute',
                ],
            ]),
            'acl' => array_merge(config('acl', []), [
                [
                    'key'   => 'b2b.buyer-verifications',
                    'name'  => 'Buyer Verifications',
                    'route' => 'admin.b2b.buyer_verifications.index',
                    'sort'  => 5,
                ],
                [
                    'key'   => 'b2b.messages',
                    'name'  => 'Message Inbox',
                    'route' => 'admin.b2b.conversations.index',
                    'sort'  => 6,
                ],
                [
                    'key'   => 'b2b.contract-templates',
                    'name'  => 'Contract Templates',
                    'route' => 'admin.b2b.contract-templates.index',
                    'sort'  => 7,
                ],
                [
                    'key'   => 'b2b.member-contracts',
                    'name'  => 'Member Contracts',
                    'route' => 'admin.b2b.member-contracts.index',
                    'sort'  => 8,
                ],
                [
                    'key'   => 'b2b.company-supply',
                    'name'  => 'Company & Supply Information',
                    'route' => 'admin.b2b.company_supply.edit',
                    'sort'  => 9,
                ],
                [
                    'key'   => 'b2b.partnership-information',
                    'name'  => 'Wholesale Partnership Information',
                    'route' => 'admin.b2b.partnership_information.edit',
                    'sort'  => 10,
                ],
                [
                    'key'   => 'catalog.product-documents',
                    'name'  => 'Product Documents',
                    'route' => 'admin.catalog.product_documents.index',
                    'sort'  => 3,
                ],
            ]),
        ]);

        /**
         * Guard private catalog, product detail, download, and storefront catalog API routes.
         */
        $this->app->booted(function () {
            /** @var Router $router */
            $router = $this->app->make(Router::class);
            $router->pushMiddlewareToGroup('shop', ProtectPrivateCatalog::class);

            $routes = Route::getRoutes();
            $routes->refreshNameLookups();

            foreach (ProtectPrivateCatalog::PROTECTED_ROUTES as $routeName) {
                $route = $routes->getByName($routeName);

                if (! $route) {
                    continue;
                }

                $route->withoutMiddleware([
                    'cache.response',
                    CacheResponse::class,
                ]);

                $route->middleware([
                    ProtectPrivateCatalog::class,
                    NoCacheMiddleware::class,
                ]);
            }

            /**
             * Ensure the wholesale portal root gateway does not serve stale FPC cache to guests or logged-out users.
             */
            $routes->getByName('shop.home.index')?->withoutMiddleware([
                'cache.response',
                CacheResponse::class,
            ])->middleware(NoCacheMiddleware::class);

            /**
             * Model B (wholesale indicative pricing) items can only be checked out with an approved quotation.
             * The cart page is intentionally excluded so the RFQ workflow keeps working.
             */
            foreach (self::QUOTATION_GUARDED_ROUTES as $routeName) {
                $routes->getByName($routeName)?->middleware(EnsureQuotationForCheckout::class);
            }

            /**
             * Wholesale commercial transactions and RFQs require verified buyer status.
             */
            foreach (self::VERIFIED_BUYER_GUARDED_ROUTES as $routeName) {
                $routes->getByName($routeName)?->middleware(EnsureVerifiedBuyer::class);
            }
        });

        /**
         * Authoritative backstop for every order-creation path (onepage, Stripe, PayPal, ...):
         * re-verify quotation authorization against the order payload right before the order is saved.
         * Staff-created admin orders are not customer checkouts and are not restricted here.
         */
        Event::listen('checkout.order.save.before', function ($data) {
            if (auth()->guard('admin')->check()) {
                return;
            }

            $lines = collect($data['items'] ?? [])->map(fn ($item) => [
                'product_id'        => $item['product_id'] ?? null,
                'child_product_ids' => collect($item['children'] ?? [])->pluck('product_id')->all(),
                'name'              => $item['name'] ?? null,
                'qty'               => (float) ($item['qty_ordered'] ?? 0),
                'base_price'        => (float) ($item['base_price'] ?? 0),
                'custom_price'      => false,
                'additional'        => $item['additional'] ?? [],
            ])->all();

            $violations = WholesalePricingManager::quotationViolations($lines, $data['customer_id'] ?? null);

            if (! empty($violations)) {
                throw new Exception('An approved quotation is required to order wholesale products. '.implode(' ', $violations));
            }
        });
    }
}

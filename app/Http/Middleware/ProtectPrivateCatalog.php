<?php

namespace App\Http\Middleware;

use App\Support\WholesalePricingManager;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Webkul\Product\Repositories\ProductRepository;

class ProtectPrivateCatalog
{
    /**
     * Storefront routes that belong to the private member catalog and must not
     * be accessible to unauthenticated guests or stored in the public response cache.
     */
    public const PROTECTED_ROUTES = [
        'shop.product_or_category.index',
        'shop.search.index',
        'shop.search.upload',
        'shop.compare.index',
        'shop.product.file.download',
        'shop.customers.account.product_documents.download',
        'shop.customers.account.product_documents.preview',
        'shop.customers.account.conversations.index',
        'shop.customers.account.conversations.create',
        'shop.customers.account.conversations.store',
        'shop.customers.account.conversations.view',
        'shop.customers.account.conversations.reply',
        'shop.customers.account.contracts.index',
        'shop.customers.account.contracts.templates',
        'shop.customers.account.contracts.templates.download',
        'shop.customers.account.contracts.view',
        'shop.customers.account.contracts.action',
        'shop.customers.account.contracts.download',
        'shop.customers.account.company_supply.index',
        'shop.customers.account.partnership_information.index',
        'shop.downloadable.download_sample',
        'shop.sitemap.index',
        'shop.api.products.index',
        'shop.api.products.related.index',
        'shop.api.products.up-sell.index',
        'shop.api.products.reviews.index',
        'shop.api.categories.index',
        'shop.api.categories.tree',
        'shop.api.categories.attributes',
        'shop.api.categories.attribute_options',
        'shop.api.categories.max_price',
        'shop.api.compare.index',
        'shop.api.compare.store',
        'shop.api.checkout.cart.index',
        'shop.api.checkout.cart.store',
        'shop.api.checkout.cart.cross-sell.index',
    ];

    /**
     * Enforce PurNatureK private catalog & product detail access control.
     *
     * - Public pages (e.g. `/`, `/page/*`, `/contact-us`, `/customer/login`) pass through untouched.
     * - Guests cannot access product details, categories, search, compare, product downloads,
     *   or storefront catalog/cart JSON APIs.
     * - Authenticated member responses carry strict private no-cache headers so private pages
     *   are never shared through response caching.
     * - Storefront product API payloads are sanitized so "Contact for Price" products never
     *   leak stored min/max or base prices.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = (string) $request->route()?->getName();

        if (! in_array($routeName, self::PROTECTED_ROUTES, true)) {
            return $next($request);
        }

        $isApiRoute = str_starts_with($routeName, 'shop.api.');

        if (! auth()->guard('customer')->check()) {
            if ($isApiRoute || $request->expectsJson()) {
                return $this->applyNoCacheHeaders(new JsonResponse([
                    'message' => 'Unauthenticated. Private member catalog access required.',
                ], 401));
            }

            if ($request->isMethod('GET') && $request->hasSession()) {
                $request->session()->put('shop.url.intended', $request->fullUrl());
            }

            return $this->applyNoCacheHeaders(
                redirect()->route('shop.customer.session.index')
            );
        }

        if ($routeName === 'shop.api.categories.max_price') {
            return $this->applyNoCacheHeaders(new JsonResponse([
                'data' => [
                    'max_price' => null,
                ],
            ]));
        }

        $response = $next($request);

        if (
            $response instanceof JsonResponse
            && in_array($routeName, [
                'shop.api.products.index',
                'shop.api.products.related.index',
                'shop.api.products.up-sell.index',
            ], true)
        ) {
            $this->sanitizeProductApiResponse($response);
        }

        return $this->applyNoCacheHeaders($response);
    }

    /**
     * Replace retail `min_price` / `price_html` in storefront JSON API payloads with
     * the member-safe wholesale price display so "Contact for Price" never leaks numbers.
     */
    protected function sanitizeProductApiResponse(JsonResponse $response): void
    {
        $payload = $response->getData(true);

        if (! is_array($payload) || empty($payload['data']) || ! is_array($payload['data'])) {
            return;
        }

        $productRepository = app(ProductRepository::class);

        foreach ($payload['data'] as &$item) {
            if (! is_array($item) || empty($item['id'])) {
                continue;
            }

            $product = $productRepository->find($item['id']);

            if ($product) {
                $pricing = WholesalePricingManager::formatForMember($product);
                $item['min_price'] = $pricing['price_display'];
                $item['price_html'] = e($pricing['price_display']);
                $item['wholesale_pricing'] = [
                    'mode'                => $pricing['mode'],
                    'price_display'       => $pricing['price_display'],
                    'moq_display'         => $pricing['moq_display'],
                    'unit'                => $pricing['unit'],
                    'supply_availability' => $pricing['supply_availability'],
                ];
            } else {
                $item['min_price'] = 'Contact for Price';
                $item['price_html'] = 'Contact for Price';
            }
        }
        unset($item);

        $response->setData($payload);
    }

    /**
     * Prevent public or shared caching of private catalog responses.
     */
    protected function applyNoCacheHeaders(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        return $response;
    }
}

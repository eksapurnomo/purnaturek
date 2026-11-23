<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Webkul\Shop\Http\Controllers\ProductsCategoriesProxyController;

/**
 * Redirects legacy root product slugs (/{slug}) to the canonical /catalog/{slug} route
 * once authenticated, while leaving categories and URL rewrites intact.
 */
class WholesaleCatalogProxyController extends ProductsCategoriesProxyController
{
    public function index(Request $request)
    {
        $slugOrURLKey = urldecode(trim($request->getPathInfo(), '/'));

        if ($slugOrURLKey !== '') {
            $category = $this->categoryRepository->findBySlug($slugOrURLKey);
            $hasActiveCategory = $category && $category->status && $category->isAvailableInChannel();

            if (! $hasActiveCategory) {
                $searchEngine = core()->getConfigData('catalog.products.search.engine') === 'elastic'
                    ? core()->getConfigData('catalog.products.search.storefront_mode')
                    : 'database';

                $product = $this->productRepository
                    ->setSearchEngine($searchEngine ?? 'database')
                    ->findBySlug($slugOrURLKey);

                if (
                    $product
                    && $product->url_key
                    && $product->visible_individually
                    && $product->status
                    && $product->isAvailableInChannel()
                ) {
                    return redirect()->route('shop.catalog.show', $product->url_key);
                }
            }
        }

        return parent::index($request);
    }
}


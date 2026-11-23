<?php

namespace App\Http\Controllers;

use App\Support\BuyerVerificationManager;
use App\Support\ProductDocumentationManager;
use App\Support\WholesalePricingManager;
use App\View\Composers\MemberCatalogComposer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Shop\Http\Controllers\Controller;

class WholesaleCatalogController extends Controller
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected MemberCatalogComposer $catalogComposer,
    ) {}

    /**
     * Render the authenticated member wholesale product catalog listing (/catalog).
     */
    public function index(): View
    {
        return view('shop::catalog.index');
    }

    /**
     * Render an individual authenticated wholesale product detail page (/catalog/{slug}).
     */
    public function show(Request $request, string $slug): View|RedirectResponse
    {
        $slug = urldecode(trim($slug));

        $searchEngine = core()->getConfigData('catalog.products.search.engine') === 'elastic'
            ? core()->getConfigData('catalog.products.search.storefront_mode')
            : 'database';

        $product = $this->productRepository
            ->setSearchEngine($searchEngine ?? 'database')
            ->findBySlug($slug);

        if (
            ! $product
            || ! $product->url_key
            || ! $product->visible_individually
            || ! $product->status
            || ! $product->isAvailableInChannel()
        ) {
            abort(404);
        }

        if ($product->url_key !== $slug) {
            return redirect()->route('shop.catalog.show', $product->url_key);
        }

        WholesalePricingManager::ensureAttributesProvisioned();

        $customer = auth()->guard('customer')->user();
        $pricing = WholesalePricingManager::formatForMember($product);
        $specs = ProductDocumentationManager::getProductSpecifications($product);
        $groupedDocuments = ProductDocumentationManager::getGroupedProductDocuments((int) $product->id);
        $relatedProducts = $this->catalogComposer->relatedCardsForProduct($product, 4);

        return view('shop::products.view', [
            'product'            => $product,
            'pricing'            => $pricing,
            'specs'              => $specs,
            'groupedDocuments'   => $groupedDocuments,
            'relatedProducts'    => $relatedProducts,
            'customer'           => $customer,
            'isVerifiedBuyer'    => BuyerVerificationManager::isVerifiedBuyer($customer),
            'verificationStatus' => $customer ? BuyerVerificationManager::getVerificationStatus($customer) : null,
        ]);
    }
}


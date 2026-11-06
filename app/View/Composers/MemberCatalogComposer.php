<?php

namespace App\View\Composers;

use App\Support\WholesalePricingManager;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;

/**
 * Supplies the PurNatureK Members Portal landing page (shop::customers.account.index)
 * with the wholesale product catalog.
 *
 * Products come from Bagisto's own ProductRepository (which the B2B Suite extends with
 * company-catalog visibility) using the same channel/status/visibility constraints as the
 * storefront product API.
 */
class MemberCatalogComposer
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected CategoryRepository $categoryRepository,
    ) {}

    public function compose(View $view): void
    {
        WholesalePricingManager::ensureAttributesProvisioned();

        $channel = core()->getCurrentChannel();

        $search = request()->query('query');
        $search = is_string($search) ? Str::limit(trim($search), 100, '') : '';

        $categoryOptions = $this->categoryOptions($channel->root_category_id);

        $selectedCategory = (int) request()->query('category_id');
        $selectedCategory = collect($categoryOptions)->contains('id', $selectedCategory) ? $selectedCategory : 0;

        $products = $this->fetchProducts($search, $selectedCategory, $channel->id);

        $customer = auth()->guard('customer')->user();

        $view->with([
            'catalogProducts'   => $products,
            'catalogCards'      => $products->getCollection()->map(fn ($product) => $this->card($product))->all(),
            'catalogSearch'     => $search,
            'catalogCategory'   => $selectedCategory,
            'catalogCategories' => $categoryOptions,
            'catalogFiltered'   => $search !== '' || $selectedCategory > 0,
            'memberGroupName'   => $customer?->group?->name,
            'memberIsCompany'   => $customer?->type === 'company',
        ]);
    }

    /**
     * Visible categories under the channel root, flattened with their depth.
     */
    protected function categoryOptions(?int $rootId): array
    {
        $options = [];

        $walk = function ($categories, int $depth) use (&$walk, &$options) {
            foreach ($categories as $category) {
                $options[] = [
                    'id'    => $category->id,
                    'name'  => $category->name,
                    'depth' => $depth,
                ];

                $walk($category->children, $depth + 1);
            }
        };

        $walk($this->categoryRepository->getVisibleCategoryTree($rootId), 0);

        return $options;
    }

    protected function fetchProducts(string $search, int $categoryId, int $channelId)
    {
        $params = [
            'query'                => $search,
            'channel_id'           => $channelId,
            'status'               => 1,
            'visible_individually' => 1,
        ];

        if ($categoryId) {
            $params['category_id'] = implode(',', $this->categoryRepository->getVisibleCategoryIds($categoryId));
        }

        $products = $this->productRepository->getAll($params);

        /**
         * Name search found nothing: fall back to an exact SKU match, as the B2B quick order does.
         */
        if ($search !== '' && $products->isEmpty()) {
            $skuParams = array_merge($params, ['query' => '', 'sku' => $search]);

            $products = $this->productRepository->getAll($skuParams);
        }

        return $products->withQueryString()->fragment('catalog');
    }

    /**
     * Presentation data for one product card, including member-safe wholesale pricing, MOQ, and supply status.
     */
    protected function card($product): array
    {
        $image = product_image()->getProductBaseImage($product);
        $pricing = WholesalePricingManager::formatForMember($product);

        return [
            'name'    => $product->name,
            'sku'     => $product->sku,
            'summary' => Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $product->short_description))), 140),
            'url'     => route('shop.product_or_category.index', $product->url_key),
            'image'   => $image['medium_image_url'] ?? ($image['small_image_url'] ?? null),
            'alt'     => $image['alt'] ?? $product->name,
            'pricing' => $pricing,
        ];
    }
}

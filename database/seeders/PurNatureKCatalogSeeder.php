<?php

namespace Database\Seeders;

use App\Support\WholesalePricingManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Webkul\Category\Models\Category;
use Webkul\Core\Models\Channel;
use Webkul\Inventory\Models\InventorySource;
use Webkul\Product\Helpers\Indexers\Flat as FlatIndexer;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductImage;
use Webkul\Product\Repositories\ProductRepository;

class PurNatureKCatalogSeeder extends Seeder
{
    /**
     * Source directory for approved product images.
     */
    public const SOURCE_ASSET_DIR = 'database/seeders/data/catalog';

    /**
     * Catalog definitions strictly adhering to approved corporate landing page copy.
     * No invented prices, MOQ, packaging, stock, potency, alkaloid percentages,
     * certifications, or origin claims.
     */
    public const PRODUCTS = [
        [
            'sku'                 => 'PUR-KRATOM-POWDER',
            'name'                => 'Kratom Powder',
            'url_key'             => 'kratom-powder',
            'image_filename'      => 'product-kratom-powder.jpg',
            'image_alt'           => 'Fine green Mitragyna speciosa kratom powder in a wide-mouth glass jar with scoop and bulk packaging pouches',
            'short_description'   => '<p>Bulk botanical powder derived from <em>Mitragyna speciosa</em>, supplied strictly for qualified business-to-business wholesale accounts.</p>',
            'description'         => '<p>Prepared for commercial buyers, product formulators, and regional distributors seeking consistent wholesale supply schedules and responsive US commercial support.</p><p>Product specifications, commercial terms, and supporting documentation are shared confidentially inside our private Wholesale Member Portal following buyer inquiry and verification.</p>',
            'meta_title'          => 'Kratom Powder — Wholesale & Bulk Supply | PurNatureK',
            'meta_keywords'       => 'PurNatureK, Kratom Powder, Mitragyna speciosa, wholesale kratom, bulk botanical supply, commercial sourcing',
            'meta_description'    => 'Bulk botanical powder derived from Mitragyna speciosa, supplied strictly for qualified business-to-business wholesale accounts.',
            'price_display_mode'  => WholesalePricingManager::MODE_BULK_CONTACT,
            'supply_availability' => 'Contact for Availability',
        ],
        [
            'sku'                 => 'PUR-KRATOM-EXTRACT',
            'name'                => 'Kratom Extract',
            'url_key'             => 'kratom-extract',
            'image_filename'      => 'product-kratom-extract.jpg',
            'image_alt'           => 'Concentrated Mitragyna speciosa extract powder in a glass dish beside sealed amber sample vials',
            'short_description'   => '<p>Concentrated botanical extract supply with specifications and supporting documentation reserved for verified wholesale buyers.</p>',
            'description'         => '<p>Available exclusively for commercial wholesale evaluation. Product-specific documentation is furnished confidentially inside our Members Portal subject to buyer due diligence.</p><p>Product specifications, commercial terms, and supporting documentation are shared confidentially inside our private Wholesale Member Portal following buyer inquiry and verification.</p>',
            'meta_title'          => 'Kratom Extract — Wholesale & Bulk Supply | PurNatureK',
            'meta_keywords'       => 'PurNatureK, Kratom Extract, concentrated botanical extract, wholesale kratom extract, bulk extract supply',
            'meta_description'    => 'Concentrated botanical extract supply with specifications and supporting documentation reserved for verified wholesale buyers.',
            'price_display_mode'  => WholesalePricingManager::MODE_BULK_CONTACT,
            'supply_availability' => 'Contact for Availability',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WholesalePricingManager::ensureAttributesProvisioned();

        $productRepo = app(ProductRepository::class);
        $flatIndexer = app(FlatIndexer::class);

        $defaultChannel = core()->getDefaultChannel() ?? Channel::first();
        if (! $defaultChannel) {
            throw new \RuntimeException('Default channel not found.');
        }

        $rootCategory = Category::find($defaultChannel->root_category_id) ?? Category::first();
        if (! $rootCategory) {
            throw new \RuntimeException('Root category not found.');
        }

        $inventorySource = InventorySource::first();

        // Remove legacy placeholder test product if present
        $legacyProduct = Product::where('sku', 'TEST-PRODUCT-PUR-01')->first();
        if ($legacyProduct) {
            // Delete associated images, files, and product
            foreach ($legacyProduct->images as $img) {
                Storage::disk('public')->delete($img->path);
            }
            $productRepo->delete($legacyProduct->id);
        }

        foreach (self::PRODUCTS as $def) {
            $sourceImagePath = base_path(self::SOURCE_ASSET_DIR . '/' . $def['image_filename']);
            if (! File::exists($sourceImagePath)) {
                throw new \RuntimeException("Approved original image not found at {$sourceImagePath}");
            }

            // Find or create base product
            $product = Product::where('sku', $def['sku'])
                ->orWhereHas('attribute_values', function ($q) use ($def) {
                    $q->whereHas('attribute', fn ($attr) => $attr->where('code', 'url_key'))
                        ->where('text_value', $def['url_key']);
                })
                ->first();

            if (! $product) {
                $product = $productRepo->create([
                    'type'                => 'simple',
                    'attribute_family_id' => 1,
                    'sku'                 => $def['sku'],
                ]);
            }

            // Prepare native Bagisto update payload
            $updatePayload = [
                'sku'                  => $def['sku'],
                'name'                 => $def['name'],
                'url_key'              => $def['url_key'],
                'short_description'    => $def['short_description'],
                'description'          => $def['description'],
                'meta_title'           => $def['meta_title'],
                'meta_keywords'        => $def['meta_keywords'],
                'meta_description'     => $def['meta_description'],
                'price'                => '0.0000',
                'weight'               => '0.0000',
                'status'               => 1,
                'visible_individually' => 1,
                'guest_checkout'       => 0,
                'manage_stock'         => 0,
                'new'                  => 1,
                'featured'             => 1,
                'price_display_mode'   => '',
                'supply_availability'  => '',
                'channel'              => $defaultChannel->code,
                'locale'               => 'en',
                'channels'             => [$defaultChannel->id],
                'categories'           => [$rootCategory->id],
                'inventories'          => $inventorySource ? [$inventorySource->id => 0] : [],
            ];

            $product = $productRepo->update($updatePayload, $product->id);

            // Store product image on public disk under product/{id}/{filename}
            $targetDir = 'product/' . $product->id;
            $targetRelPath = $targetDir . '/' . $def['image_filename'];
            Storage::disk('public')->put($targetRelPath, File::get($sourceImagePath));

            // Sync ProductImage after product update so Bagisto media synchronizer does not purge it
            ProductImage::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'type'       => 'images',
                ],
                [
                    'path'       => $targetRelPath,
                    'position'   => 1,
                ]
            );

            // Trigger events and flat reindexing
            Event::dispatch('catalog.product.update.after', $product);
            $flatIndexer->refresh($product);
        }

        // Full indexer execution for price and inventory indices
        Artisan::call('indexer:index');
    }
}

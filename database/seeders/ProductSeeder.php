<?php

namespace Database\Seeders;

use App\Domains\Category\Models\Category;
use App\Domains\Product\Models\Product;
use App\Domains\Product\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Get all categories
        $categories = Category::all()->keyBy('name');

        // ==================== HONEY PRODUCTS (Simple & Variable) ====================

        // Simple Product - No variants
        Product::create([
            'category_id' => $categories['Honey']->id,
            'name' => 'Mangrove Gold Honey (ম্যানগ্রোভ গোল্ড হানি)',
            'slug' => 'mangrove-gold-honey',
            'short_description' => 'Pure wild honey collected from Sundarban mangrove forest',
            'description' => 'This honey is collected by traditional honey hunters from the Sundarbans. It has a distinct flavor and medicinal properties.',
            'base_price' => 1200,
            'sku' => 'HON-SUN-001',
            'is_active' => true,
            'is_featured' => true,
            'is_trending' => true,
            'thumbnail' => 'products/mangrove-gold-honey.jpg',
            'meta_title' => 'Buy Mangrove Gold Honey (ম্যানগ্রোভ গোল্ড হানি) Online',
            'meta_description' => 'Pure Mangrove Gold Honey (ম্যানগ্রোভ গোল্ড হানি) with medicinal properties. 100% natural and unprocessed.',
        ])->variants()->create([
            'title' => '1kg',
            'sku' => 'HON-SUN-1KG',
            'price' => 1200,
            'discount_type' => 'fixed',
            'discount_value' => 100,
            'stock' => 50,
            'weight_grams' => 1000,
            'is_active' => true,
        ]);

        // Variable Product - Honey with multiple sizes
        $honeyVariable = Product::create([
            'category_id' => $categories['Honey']->id,
            'name' => 'Organic Raw Honey',
            'slug' => 'organic-raw-honey',
            'short_description' => 'Certified organic raw honey, unprocessed and unfiltered',
            'description' => 'Our organic raw honey is sourced from certified organic farms. It retains all natural enzymes and nutrients.',
            'base_price' => 800,
            'sku' => 'HON-ORG-001',
            'is_active' => true,
            'is_featured' => true,
            'is_trending' => true,
            'thumbnail' => 'products/honey.jpg',
        ]);

        // Variants for Organic Honey
        $honeyVariable->variants()->createMany([
            [
                'title' => '250g',
                'sku' => 'HON-ORG-250G',
                'price' => 250,
                'stock' => 100,
                'weight_grams' => 250,
                'is_active' => true,
            ],
            [
                'title' => '500g',
                'sku' => 'HON-ORG-500G',
                'price' => 450,
                'stock' => 80,
                'weight_grams' => 500,
                'is_active' => true,
            ],
            [
                'title' => '1kg',
                'sku' => 'HON-ORG-1KG',
                'price' => 800,
                'stock' => 60,
                'weight_grams' => 1000,
                'is_active' => true,
            ],
        ]);

        // Simple Honey Product
        Product::create([
            'category_id' => $categories['Honey']->id,
            'name' => 'Floral Gold Honey',
            'slug' => 'floral-gold-honey',
            'base_price' => 950,
            'sku' => 'HON-FOR-001',
            'is_active' => true,
            'thumbnail' => 'products/floral-gold-honey.jpg',
        ])->variants()->create([
            'title' => '1kg',
            'sku' => 'HON-FOR-1KG',
            'price' => 950,
            'stock' => 40,
            'weight_grams' => 1000,
            'is_active' => true,
        ]);

        // ==================== DATES PRODUCTS ====================

        // Simple Date Product
        Product::create([
            'category_id' => $categories['Dates']->id,
            'name' => 'Medjool Dates',
            'slug' => 'medjool-dates',
            'short_description' => 'Premium Medjool dates from Jordan',
            'base_price' => 1800,
            'sku' => 'DATE-MED-001',
            'is_active' => true,
            'is_featured' => true,
            'is_trending' => true,
            'thumbnail' => 'products/medjool-dates.jpg',
        ])->variants()->create([
            'title' => '1kg',
            'sku' => 'DATE-MED-1KG',
            'price' => 1800,
            'stock' => 30,
            'weight_grams' => 1000,
            'is_active' => true,
        ]);

        // Variable Date Product with multiple sizes
        $ajwaDates = Product::create([
            'category_id' => $categories['Dates']->id,
            'name' => 'Ajwa Dates',
            'slug' => 'ajwa-dates',
            'short_description' => 'Premium Ajwa dates from Madinah',
            'base_price' => 2500,
            'sku' => 'DATE-AJW-001',
            'is_active' => true,
            'is_featured' => true,
            'is_trending' => true,
            'thumbnail' => 'products/ajwa-dates.jpg',
        ]);

        $ajwaVariant = $ajwaDates->variants()->create([
            'title' => '1kg',
            'sku' => 'DATE-AJW-1KG',
            'price' => 2500,
            'stock' => 25,
            'weight_grams' => 1000,
            'is_active' => true,
        ]);

        // Add tier pricing
        $ajwaVariant->tierPrices()->create([
            'min_quantity' => 3,
            'discount_type' => 'fixed',
            'discount_value' => 150,
        ]);

        // Another simple date product
        Product::create([
            'category_id' => $categories['Dates']->id,
            'name' => 'Sukkary Dates',
            'slug' => 'sukkary-dates',
            'base_price' => 1600,
            'sku' => 'DATE-SUK-001',
            'is_active' => true,
            'thumbnail' => 'products/sukkary-dates.jpg',
        ])->variants()->create([
            'title' => '1kg',
            'sku' => 'DATE-SUK-1KG',
            'price' => 1600,
            'stock' => 35,
            'weight_grams' => 1000,
            'is_active' => true,
        ]);

        // ==================== OILS PRODUCTS ====================

        // Variable Oil Product - multiple sizes
        $coconutOil = Product::create([
            'category_id' => $categories['Oils']->id,
            'name' => 'Edible Virgin Coconut Oil',
            'slug' => 'edible-virgin-coconut-oil',
            'short_description' => 'Cold-pressed virgin coconut oil',
            'base_price' => 600,
            'sku' => 'OIL-COC-001',
            'is_active' => true,
            'thumbnail' => 'products/edible-virgin-coconut-oil.jpg',
        ]);

        $coconutOil->variants()->createMany([
            [
                'title' => '500ml',
                'sku' => 'OIL-COC-500ML',
                'price' => 350,
                'stock' => 45,
                'weight_grams' => 500,
                'is_active' => true,
            ],
            [
                'title' => '1L',
                'sku' => 'OIL-COC-1L',
                'price' => 600,
                'stock' => 30,
                'weight_grams' => 1000,
                'is_active' => true,
            ],
        ]);

        // Simple Oil Product
        Product::create([
            'category_id' => $categories['Oils']->id,
            'name' => 'Extra Virgin Olive Oil',
            'slug' => 'extra-virgin-olive-oil',
            'base_price' => 1200,
            'sku' => 'OIL-OLV-001',
            'is_active' => true,
            'is_featured' => true,
            'is_trending' => true,
            'thumbnail' => 'products/extra-virgin-olive-oil.jpg',
        ])->variants()->create([
            'title' => '1L',
            'sku' => 'OIL-OLV-1L',
            'price' => 1200,
            'stock' => 40,
            'weight_grams' => 1000,
            'is_active' => true,
        ]);

        // ==================== NUTS PRODUCTS ====================

        // Variable Nuts Product - Example with Apple (as requested)
        $appleProduct = Product::create([
            'category_id' => $categories['Nuts']->id,
            'name' => 'Fresh Apples',
            'slug' => 'fresh-apples',
            'short_description' => 'Premium quality fresh apples',
            'description' => 'Crisp and juicy apples sourced from the best orchards. Available in different sizes.',
            'base_price' => 400,
            'sku' => 'FRT-APP-001',
            'is_active' => true,
            'is_featured' => true,
            'is_trending' => true,
            'thumbnail' => 'products/fresh-apples.jpg',
        ]);

        // Apple variants with different prices per kg
        $appleProduct->variants()->createMany([
            [
                'title' => '1kg',
                'sku' => 'APP-SML-1KG',
                'price' => 400,  // 400 BDT per 1kg 
                'stock' => 100,
                'weight_grams' => 1000,
                'is_active' => true,
            ],
            [
                'title' => '2kg',
                'sku' => 'APP-MED-1KG',
                'price' => 750,  // 750 BDT per 2kg
                'stock' => 80,
                'weight_grams' => 1000,
                'is_active' => true,
            ],
            [
                'title' => '3kg',
                'sku' => 'APP-LRG-1KG',
                'price' => 1050,  // 1050 BDT per 3kg
                'stock' => 60,
                'weight_grams' => 1000,
                'is_active' => true,
            ],
        ]);

        // Mixed Nuts with variants
        $mixedNuts = Product::create([
            'category_id' => $categories['Nuts']->id,
            'name' => 'Mixed Premium Nuts',
            'slug' => 'mixed-premium-nuts',
            'base_price' => 850,
            'sku' => 'NUT-MIX-001',
            'is_active' => true,
            'is_featured' => true,
            'is_trending' => true,
            'thumbnail' => 'products/mixed-premium-nuts.jpg',
        ]);

        $mixedNuts->variants()->createMany([
            [
                'title' => '250g',
                'sku' => 'NUT-MIX-250G',
                'price' => 450,
                'stock' => 60,
                'weight_grams' => 250,
                'is_active' => true,
            ],
            [
                'title' => '500g',
                'sku' => 'NUT-MIX-500G',
                'price' => 850,
                'stock' => 40,
                'weight_grams' => 500,
                'is_active' => true,
            ],
            [
                'title' => '1kg',
                'sku' => 'NUT-MIX-1KG',
                'price' => 1600,
                'stock' => 20,
                'weight_grams' => 1000,
                'is_active' => true,
            ],
        ]);

        // Simple Almonds
        Product::create([
            'category_id' => $categories['Nuts']->id,
            'name' => 'California Almonds',
            'slug' => 'california-almonds',
            'base_price' => 950,
            'sku' => 'NUT-ALM-001',
            'is_active' => true,
            'thumbnail' => 'products/california-almonds.jpg',
        ])->variants()->create([
            'title' => '500g',
            'sku' => 'ALM-500G',
            'price' => 950,
            'stock' => 70,
            'weight_grams' => 500,
            'is_active' => true,
        ]);

        // Simple Cashews
        // Product::create([
        //     'category_id' => $categories['Nuts']->id,
        //     'name' => 'W240 Cashew Nuts',
        //     'slug' => 'w240-cashew-nuts',
        //     'base_price' => 1100,
        //     'sku' => 'NUT-CAS-001',
        //     'is_active' => true,
        //     'thumbnail' => 'products/w240-cashew-nuts.jpg',
        // ])->variants()->create([
        //     'title' => '500g',
        //     'sku' => 'CAS-500G',
        //     'price' => 1100,
        //     'stock' => 55,
        //     'weight_grams' => 500,
        //     'is_active' => true,
        // ]);

        // Walnuts
        // Product::create([
        //     'category_id' => $categories['Nuts']->id,
        //     'name' => 'English Walnuts',
        //     'slug' => 'english-walnuts',
        //     'base_price' => 1200,
        //     'sku' => 'NUT-WAL-001',
        //     'is_active' => true,
        //     'thumbnail' => 'products/english-walnuts.jpg',
        // ])->variants()->create([
        //     'title' => '500g',
        //     'sku' => 'WAL-500G',
        //     'price' => 1200,
        //     'stock' => 40,
        //     'weight_grams' => 500,
        //     'is_active' => true,
        // ]);

        // ==================== SEEDS PRODUCTS ====================

        // Chia Seeds with variants
        $chiaSeeds = Product::create([
            'category_id' => $categories['Seeds']->id,
            'name' => 'Organic Chia Seeds',
            'slug' => 'organic-chia-seeds',
            'base_price' => 450,
            'sku' => 'SED-CHA-001',
            'is_active' => true,
            'thumbnail' => 'products/organic-chia-seeds.jpg',
        ]);

        $chiaSeeds->variants()->createMany([
            [
                'title' => '250g',
                'sku' => 'CHA-250G',
                'price' => 250,
                'stock' => 90,
                'weight_grams' => 250,
                'is_active' => true,
            ],
            [
                'title' => '500g',
                'sku' => 'CHA-500G',
                'price' => 450,
                'stock' => 70,
                'weight_grams' => 500,
                'is_active' => true,
            ],
        ]);

        // Flax Seeds
        // Product::create([
        //     'category_id' => $categories['Seeds']->id,
        //     'name' => 'Brown Flax Seeds',
        //     'slug' => 'brown-flax-seeds',
        //     'base_price' => 280,
        //     'sku' => 'SED-FLA-001',
        //     'is_active' => true,
        //     'thumbnail' => 'products/brown-flax-seeds.jpg',
        // ])->variants()->create([
        //     'title' => '500g',
        //     'sku' => 'FLA-500G',
        //     'price' => 280,
        //     'stock' => 85,
        //     'weight_grams' => 500,
        //     'is_active' => true,
        // ]);

        // ==================== GHEE PRODUCTS ====================

        // Variable Ghee Product
        $gheeProduct = Product::create([
            'category_id' => $categories['Ghee']->id,
            'name' => 'Royal Essence Pure Desi Ghee',
            'slug' => 'royal-essence-ghee',
            'short_description' => 'Traditional desi ghee made from deshi cow milk',
            'base_price' => 1800,
            'sku' => 'GHE-DES-001',
            'is_active' => true,
            'is_featured' => true,
            'is_trending' => true,
            'thumbnail' => 'products/royal-essence-ghee.jpg',
        ]);

        $gheeProduct->variants()->createMany([
            [
                'title' => '500ml',
                'sku' => 'GHE-500ML',
                'price' => 1000,
                'stock' => 30,
                'weight_grams' => 500,
                'is_active' => true,
            ],
            [
                'title' => '900ml',
                'sku' => 'GHE-900ML',
                'price' => 1800,
                'stock' => 20,
                'weight_grams' => 900,
                'is_active' => true,
            ],
            [
                'title' => '1.8L',
                'sku' => 'GHE-1.8L',
                'price' => 3400,
                'stock' => 10,
                'weight_grams' => 1800,
                'is_active' => true,
            ],
        ]);

        // ==================== DRY FRUITS ====================

        // Apricots
        Product::create([
            'category_id' => $categories['Dry Fruits']->id,
            'name' => 'Dried Apricots',
            'slug' => 'dried-apricots',
            'base_price' => 650,
            'sku' => 'DRY-APR-001',
            'is_active' => true,
            'thumbnail' => 'products/dried-apricots.jpg',
        ])->variants()->create([
            'title' => '500g',
            'sku' => 'APR-500G',
            'price' => 650,
            'stock' => 45,
            'weight_grams' => 500,
            'is_active' => true,
        ]);

        // Raisins
        // Product::create([
        //     'category_id' => $categories['Dry Fruits']->id,
        //     'name' => 'Golden Raisins',
        //     'slug' => 'golden-raisins',
        //     'base_price' => 400,
        //     'sku' => 'DRY-RAI-001',
        //     'is_active' => true,
        //     'thumbnail' => 'products/golden-raisins.jpg',
        // ])->variants()->create([
        //     'title' => '500g',
        //     'sku' => 'RAI-500G',
        //     'price' => 400,
        //     'stock' => 60,
        //     'weight_grams' => 500,
        //     'is_active' => true,
        // ]);

        // ==================== SPICES ====================

        // Cardamom
        Product::create([
            'category_id' => $categories['Spices']->id,
            'name' => 'Green Cardamom',
            'slug' => 'green-cardamom',
            'base_price' => 1200,
            'sku' => 'SPI-CAR-001',
            'is_active' => true,
            'thumbnail' => 'products/green-cardamom.jpg',
        ])->variants()->create([
            'title' => '100g',
            'sku' => 'CAR-100G',
            'price' => 1200,
            'stock' => 25,
            'weight_grams' => 100,
            'is_active' => true,
        ]);

        // Cinnamon
        Product::create([
            'category_id' => $categories['Spices']->id,
            'name' => 'Ceylon Cinnamon',
            'slug' => 'ceylon-cinnamon',
            'base_price' => 350,
            'sku' => 'SPI-CIN-001',
            'is_active' => true,
            'thumbnail' => 'products/ceylon-cinnamon.jpg',
        ])->variants()->create([
            'title' => '250g',
            'sku' => 'CIN-250G',
            'price' => 350,
            'stock' => 40,
            'weight_grams' => 250,
            'is_active' => true,
        ]);
    }
}

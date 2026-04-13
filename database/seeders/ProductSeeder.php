<?php

namespace Database\Seeders;

use App\Domains\Category\Models\Category;
use App\Domains\Product\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch all categories to dynamically assign their IDs
        $categories = Category::all()->keyBy('name');

        // Helper to quickly grab the category ID by exact name
        $getCategoryId = function ($name) use ($categories) {
            return isset($categories[$name]) ? $categories[$name]->id : Category::first()->id;
        };

        // ==================== SPICES & POWDERS ====================

        $pinkSalt = Product::create([
            'category_id' => $getCategoryId('Spices'),
            'name' => 'Himalayan Pink Salt (হিমালয়ান পিঙ্ক সল্ট)',
            'slug' => Str::slug('Himalayan Pink Salt'),
            'base_price' => 190,
            'sku' => 'SLT-PNK-001',
            'thumbnail' => 'products/pink-salt.jpg',
            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,
        ]);

        $variants = $pinkSalt->variants()->createMany([
            [
                'title' => '140gm',
                'sku' => 'SLT-PNK-140G',
                'price' => 190,
                'stock' => 1000,
                'weight_grams' => 140,
                'is_active' => true
            ],
            [
                'title' => '1KG',
                'sku' => 'SLT-PNK-1KG',
                'price' => 870,
                'stock' => 1000,
                'weight_grams' => 1000,
                'is_active' => true
            ],
        ]);

        $variants->first()->tierPrices()->create([
            'min_quantity' => 2,
            'discount_type' => 'fixed',
            'discount_value' => 60,
        ]);

        Product::create([
            'category_id' => $getCategoryId('Spices'),
            'name' => 'Beetroot Powder (বিটরুট পাউডার) 200gm',
            'slug' => Str::slug('Beetroot Powder'),
            'base_price' => 990,
            'sku' => 'POW-BET-001',
            'thumbnail' => 'products/beetroot-powder.jpg',
            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,
        ])->variants()->create([
            'title' => '200gm',
            'sku' => 'BET-200G',
            'price' => 990,
            'stock' => 1000,
            'weight_grams' => 200,
            'is_active' => true,
        ]);


        // ==================== HONEY ====================

        $mangroveGoldHoney = Product::create([
            'category_id' => $getCategoryId('Honey'),
            'name' => 'Mangrove Gold Honey (ম্যানগ্রোভ গোল্ড হানি) 500gm',
            'slug' => Str::slug('Mangrove Gold Honey'),
            'base_price' => 990,
            'sku' => 'HON-MAN-001',
            'thumbnail' => 'products/mangrove-gold-honey.jpg',
            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,
        ]);

        $mangroveGoldHoneyVariant = $mangroveGoldHoney->variants()->create([
            'title' => '500gm',
            'sku' => 'HON-MAN-500G',
            'price' => 990,
            'stock' => 1000,
            'weight_grams' => 500,
            'is_active' => true,
        ]);

        $mangroveGoldHoneyVariant->tierPrices()->create([
            'min_quantity' => 2,
            'discount_type' => 'fixed',
            'discount_value' => 60,
        ]);

        Product::create([
            'category_id' => $getCategoryId('Honey'),
            'name' => 'Floral Gold Honey (ফ্লোরাল গোল্ড হানি) 255gm',
            'slug' => Str::slug('Floral Gold Honey'),
            'base_price' => 499,
            'sku' => 'HON-FLO-001',
            'thumbnail' => 'products/floral-gold-honey.jpg',
            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,
        ])->variants()->create([
            'title' => '255gm',
            'sku' => 'HON-FLO-255G',
            'price' => 499,
            'stock' => 1000,
            'weight_grams' => 255,
            'is_active' => true,
        ]);


        // ==================== GHEE ====================

        $premiumGhee = Product::create([
            'category_id' => $getCategoryId('Ghee'),
            'name' => 'Premium Ghee (প্রিমিয়াম ঘি) 350gm',
            'slug' => Str::slug('Premium Ghee'),
            'base_price' => 870,
            'sku' => 'GHE-PRE-001',
            'thumbnail' => 'products/premium-ghee.jpg',
            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,
        ]);

        $premiumGheeVariant = $premiumGhee->variants()->create([
            'title' => '350gm',
            'sku' => 'GHE-PRE-350G',
            'price' => 870,
            'stock' => 1000,
            'weight_grams' => 350,
            'is_active' => true,
        ]);

        $premiumGheeVariant->tierPrices()->create([
            'min_quantity' => 2,
            'discount_type' => 'fixed',
            'discount_value' => 60,
        ]);


        // ==================== DATES ====================

        $kalmi = Product::create([
            'category_id' => $getCategoryId('Dates'),
            'name' => 'Kalmi Super Premium (কালমি সুপার প্রিমিয়াম)',
            'slug' => Str::slug('Kalmi Super Premium'),
            'base_price' => 1600,
            'sku' => 'DAT-KAL-001',
            'thumbnail' => 'products/kalmi-dates.jpg',
            'is_active' => true,
        ]);
        $kalmiVariant = $kalmi->variants()->createMany([
            ['title' => '1KG', 'sku' => 'KAL-1KG', 'price' => 1600, 'stock' => 1000, 'weight_grams' => 1000, 'is_active' => true],
            ['title' => '5KG', 'sku' => 'KAL-5KG', 'price' => 6500, 'stock' => 1000, 'weight_grams' => 5000, 'is_active' => true],
        ]);

        $kalmiVariant->first()->tierPrices()->create([
            'min_quantity' => 5,
            'discount_type' => 'fixed',
            'discount_value' => 300,
        ]);

        $mariyam = Product::create([
            'category_id' => $getCategoryId('Dates'),
            'name' => 'Mariyam Super Premium Dates (মরিয়ম সুপার প্রিমিয়াম খেজুর)',
            'slug' => Str::slug('Mariyam Super Premium Dates'),
            'base_price' => 1920,
            'sku' => 'DAT-MAR-001',
            'thumbnail' => 'products/mariyam-dates.jpg',
            'is_active' => true,
        ]);
        $mariyamVariant = $mariyam->variants()->createMany([
            ['title' => '1KG', 'sku' => 'MAR-1KG', 'price' => 1920, 'stock' => 1000, 'weight_grams' => 1000, 'is_active' => true],
            ['title' => '5KG', 'sku' => 'MAR-5KG', 'price' => 8900, 'stock' => 1000, 'weight_grams' => 5000, 'is_active' => true],
        ]);

        $mariyamVariant->first()->tierPrices()->create([
            'min_quantity' => 5,
            'discount_type' => 'fixed',
            'discount_value' => 140,
        ]);

        $medjool = Product::create([
            'category_id' => $getCategoryId('Dates'),
            'name' => 'Egyptian Medjool (মেডজুল খেজুর)',
            'slug' => Str::slug('Egyptian Medjool'),
            'base_price' => 2200,
            'sku' => 'DAT-MED-001',
            'thumbnail' => 'products/medjool-dates.jpg',
            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,
        ]);
        $medjool->variants()->createMany([
            ['title' => '1KG Large', 'sku' => 'MED-1KG-LRG', 'price' => 2200, 'stock' => 1000, 'weight_grams' => 1000, 'is_active' => true],
            ['title' => '5KG Large', 'sku' => 'MED-5KG-LRG', 'price' => 10500, 'stock' => 1000, 'weight_grams' => 5000, 'is_active' => true],
            ['title' => '1KG Jambo', 'sku' => 'MED-1KG-JAM', 'price' => 2800, 'stock' => 1000, 'weight_grams' => 1000, 'is_active' => true],
            ['title' => '5KG Jambo', 'sku' => 'MED-5KG-JAM', 'price' => 12800, 'stock' => 1000, 'weight_grams' => 5000, 'is_active' => true],
        ]);

        $ajwa = Product::create([
            'category_id' => $getCategoryId('Dates'),
            'name' => 'Ajwa Date (আজওয়া খেজুর)',
            'slug' => Str::slug('Ajwa Date'),
            'base_price' => 2100,
            'sku' => 'DAT-AJW-001',
            'thumbnail' => 'products/ajwa-dates.jpg',
            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,
        ]);
        $ajwa->variants()->createMany([
            ['title' => '1KG Large', 'sku' => 'AJW-1KG-LRG', 'price' => 2100, 'stock' => 1000, 'weight_grams' => 1000, 'is_active' => true],
            ['title' => '5KG Large', 'sku' => 'AJW-5KG-LRG', 'price' => 9900, 'stock' => 1000, 'weight_grams' => 5000, 'is_active' => true],
            ['title' => '1KG Premium', 'sku' => 'AJW-1KG-PREM', 'price' => 2500, 'stock' => 1000, 'weight_grams' => 1000, 'is_active' => true],
            ['title' => '5KG Jambo', 'sku' => 'AJW-5KG-JAM', 'price' => 11500, 'stock' => 1000, 'weight_grams' => 5000, 'is_active' => true],
        ]);

        $sukkari = Product::create([
            'category_id' => $getCategoryId('Dates'),
            'name' => 'Sukkari (সুক্কারি খেজুর)',
            'slug' => Str::slug('Sukkari'),
            'base_price' => 1500,
            'sku' => 'DAT-SUK-001',
            'thumbnail' => 'products/sukkari-dates.jpg',
            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,
        ]);
        $sukkari->variants()->createMany([
            ['title' => '1KG', 'sku' => 'SUK-1KG', 'price' => 1500, 'stock' => 1000, 'weight_grams' => 1000, 'is_active' => true],
            ['title' => '3KG', 'sku' => 'SUK-3KG', 'price' => 3900, 'stock' => 1000, 'weight_grams' => 3000, 'is_active' => true],
        ]);


        // ==================== MIXES & NUTS ====================

        Product::create([
            'category_id' => $getCategoryId('Nuts'),
            'name' => 'Brain Booster Mix (ব্রেন বুস্টার মিক্স) 205gm',
            'slug' => Str::slug('Brain Booster Mix'),
            'base_price' => 699,
            'sku' => 'MIX-BRA-001',
            'thumbnail' => 'products/brain-booster-mix.jpg',
            'is_active' => true,
        ])->variants()->create([
            'title' => '205gm',
            'sku' => 'BRA-205G',
            'price' => 699,
            'stock' => 1000,
            'weight_grams' => 205,
            'is_active' => true,

        ]);

        Product::create([
            'category_id' => $getCategoryId('Nuts'),
            'name' => 'Vital Mix (ভাইটাল মিক্স) 205gm',
            'slug' => Str::slug('Vital Mix'),
            'base_price' => 495,
            'sku' => 'MIX-VIT-001',
            'thumbnail' => 'products/vital-mix.jpg',
            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,
        ])->variants()->create([
            'title' => '205gm',
            'sku' => 'VIT-205G',
            'price' => 495,
            'stock' => 1000,
            'weight_grams' => 205,
            'is_active' => true,
        ]);


        // ==================== SEEDS ====================

        Product::create([
            'category_id' => $getCategoryId('Seeds'),
            'name' => 'Chia Seeds (চিয়া সীডস) 500gm',
            'slug' => Str::slug('Chia Seeds'),
            'base_price' => 690,
            'sku' => 'SED-CHI-001',
            'thumbnail' => 'products/chia-seeds.jpg',
            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,
        ])->variants()->create([
            'title' => '500gm',
            'sku' => 'CHI-500G',
            'price' => 690,
            'stock' => 1000,
            'weight_grams' => 500,
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $getCategoryId('Seeds'),
            'name' => 'Premium Tokma (প্রিমিয়াম তোকমা) 240gm',
            'slug' => Str::slug('Premium Tokma'),
            'base_price' => 240,
            'sku' => 'SED-TOK-001',
            'thumbnail' => 'products/tokma-seeds.jpg',
            'is_active' => true,
        ])->variants()->create([
            'title' => '240gm',
            'sku' => 'TOK-240G',
            'price' => 240,
            'stock' => 1000,
            'weight_grams' => 240,
            'is_active' => true,
        ]);


        // ==================== OILS ====================

        Product::create([
            'category_id' => $getCategoryId('Oils'),
            'name' => 'Black Seed Oil (কালোজিরার তেল) 200ml',
            'slug' => Str::slug('Black Seed Oil'),
            'base_price' => 490,
            'sku' => 'OIL-BLK-001',
            'thumbnail' => 'products/black-seed-oil.jpg',
            'is_active' => true,
        ])->variants()->create([
            'title' => '200ml',
            'sku' => 'BLK-200ML',
            'price' => 490,
            'stock' => 1000,
            'weight_grams' => 200,
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $getCategoryId('Oils'),
            'name' => 'Edible Virgin Coconut Oil (ভোজ্য ভার্জিন নারকেল তেল) 360ml',
            'slug' => Str::slug('Edible Virgin Coconut Oil'),
            'base_price' => 870,
            'sku' => 'OIL-COC-001',
            'thumbnail' => 'products/coconut-oil.jpg',
            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,
        ])->variants()->create([
            'title' => '360ml',
            'sku' => 'COC-360ML',
            'price' => 870,
            'stock' => 1000,
            'weight_grams' => 360,
            'is_active' => true,
        ]);

        $mustardOil = Product::create([
            'category_id' => $getCategoryId('Oils'),
            'name' => 'Mustard Oil (খাঁটি সরিষার তেল)',
            'slug' => Str::slug('Mustard Oil'),
            'base_price' => 1750,
            'sku' => 'OIL-MUS-001',
            'thumbnail' => 'products/mustard-oil.jpg',
            'is_active' => true,
        ]);
        $mustardOil->variants()->createMany([
            ['title' => '5L', 'sku' => 'MUS-5L', 'price' => 1750, 'stock' => 1000, 'weight_grams' => 5000, 'is_active' => true],
            ['title' => '8L', 'sku' => 'MUS-8L', 'price' => 2800, 'discount_type' => 'fixed', 'discount_value' => 150, 'stock' => 1000, 'weight_grams' => 8000, 'is_active' => true],
        ]);


        // ==================== SWEETENERS ====================

        Product::create([
            'category_id' => $getCategoryId('Sweeteners'),
            'name' => 'Goler Gurr (গোলের গুড়) 500gm',
            'slug' => Str::slug('Goler Gurr'),
            'base_price' => 290,
            'sku' => 'SWT-GUR-001',
            'thumbnail' => 'products/goler-gurr.jpg',
            'is_active' => true,
        ])->variants()->create([
            'title' => '500gm',
            'sku' => 'GUR-500G',
            'price' => 290,
            'stock' => 1000,
            'weight_grams' => 500,
            'is_active' => true,
        ]);
    }
}

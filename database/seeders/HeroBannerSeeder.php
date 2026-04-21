<?php

namespace Database\Seeders;

use App\Domains\Store\Models\HeroBanner;
use App\Domains\Product\Models\Product;
use App\Domains\Category\Models\Category;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class HeroBannerSeeder extends Seeder
{
    public function run(): void
    {
        // Get some products and categories for linking
        $featuredProduct = Product::where('is_featured', true)->first();
        $honeyCategory = Category::where('name', 'Honey')->first();
        $datesCategory = Category::where('name', 'Dates')->first();

        // Clear existing banners
        HeroBanner::truncate();

        $banners = [
            // Banner 1: Mangrove Gold Honey (Product focus)
            [
                'badge' => '100% Pure & Natural',
                'title' => 'Pure Organic <br> Mangrove Gold <br> Honey',
                'subtitle' => 'Farm to Jar Freshness',
                'description' => 'Raw, unfiltered mangrove honey harvested from the heart of the Sundarbans.',
                'button_text' => 'Shop Now',
                'button_url' => '/products/mangrove-honey',
                'image' => 'banners/mangrove-honey.png',
                'sort_order' => 1,
                'is_active' => true,
                'starts_at' => null,
                'ends_at' => null,
                'category_id' => $honeyCategory?->id,
            ],

            // Banner 2: Premium Dates (Category focus)
            [
                'badge' => 'Premium Quality',
                'title' => 'Authentic Ajwa <br> and Fresh <br> Sukkari Dates',
                'subtitle' => 'Sourced from Madinah',
                'description' => 'Experience the natural sweetness and health benefits of our handpicked dates.',
                'button_text' => 'Browse Dates',
                'button_url' => '/category/dates',
                'image' => 'banners/dates-collection.png',
                'sort_order' => 2,
                'is_active' => true,
                'starts_at' => null,
                'ends_at' => null,
                'product_id' => null,
                'category_id' => $datesCategory?->id,
            ],

            // Banner 3: Superfood Combo (Vital Mix & Chia)
            [
                'badge' => 'Health Booster',
                'title' => 'Nutritious Vital <br> Mix and <br> Organic Chia Seeds',
                'subtitle' => 'The Ultimate Superfood Duo',
                'description' => 'Boost your energy and immunity with our premium seed and nut powerhouse combo.',
                'button_text' => 'Get the Combo',
                'button_url' => '/products/vital-chia-combo',
                'image' => 'banners/superfood-banner.png',
                'sort_order' => 3,
                'is_active' => true,
                'starts_at' => null,
                'ends_at' => null,
                'category_id' => null,
            ],

            // Banner 4: Wellness Essentials (Himalayan & Beetroot)
            [
                'badge' => '100% Organic',
                'title' => 'Pure Himalayan <br> and Natural <br> Beetroot Powder',
                'subtitle' => 'Daily Detox Essentials',
                'description' => 'Premium grade Himalayan pink salt and nutrient-rich beetroot powder for your kitchen.',
                'button_text' => 'Explore More',
                'button_url' => '/products',
                'image' => 'banners/wellness-banner.png',
                'sort_order' => 4,
                'is_active' => true,
                'starts_at' => null,
                'ends_at' => null,
                'product_id' => null,
            ],

            // Banner 5: Cold-Pressed Oils (New Arrival)
            [
                'badge' => 'New Arrival',
                'title' => 'Premium Cold <br> Pressed Edible <br> Virgin Oil',
                'subtitle' => 'Chemical-Free & Healthy',
                'description' => 'Extracted naturally to retain all nutrients. Perfect for healthy cooking and skin care.',
                'button_text' => 'Order Now',
                'button_url' => '/products/virgin-oil',
                'image' => 'banners/oil-banner.png',
                'sort_order' => 5,
                'is_active' => true,
                'starts_at' => Carbon::now(),
                'ends_at' => Carbon::now()->addDays(60),
                'category_id' => null,
            ],
        ];

        foreach ($banners as $banner) {
            HeroBanner::create($banner);
        }
    }
}

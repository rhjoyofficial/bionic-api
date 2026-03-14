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
            // Banner 1: Linked to a product
            [
                'badge' => '100% Pure & Natural',
                'title' => 'Pure Organic <br> Mangrove Gold <br> Honey',
                'subtitle' => 'Farm to Jar Freshness',
                'description' => 'Premium organic dates, raw honey, cold-pressed oils & natural superfoods.',
                'button_text' => 'Shop All',
                'button_url' => '/products',
                'image' => 'banners/honey-banner.jpg',
                'sort_order' => 1,
                'is_active' => true,
                'starts_at' => null,
                'ends_at' => null,
                'product_id' => $featuredProduct?->id,
                'category_id' => null,
            ],

            // Banner 2: Linked to a category
            [
                'badge' => '100% Pure & Natural',
                'title' => 'Premium Dates Collection',
                'subtitle' => 'From Madinah to You',
                'description' => 'Discover our exclusive collection of premium dates. Ajwa, Medjool, Sukkary and more.',
                'button_text' => 'Browse Dates',
                'button_url' => '/products',
                'image' => 'banners/dates-banner.jpg',
                'sort_order' => 2,
                'is_active' => true,
                'starts_at' => null,
                'ends_at' => null,
                'product_id' => null,
                'category_id' => $datesCategory?->id,
            ],

            // Banner 3: Direct URL (no linking)
            [
                'badge' => '100% Pure & Natural',
                'title' => 'Free Shipping',
                'subtitle' => 'On orders above ৳2000',
                'description' => 'Enjoy free delivery on all prepaid orders above BDT 2000. Limited time offer.',
                'button_text' => 'Learn More',
                'button_url' => '/shipping-policy',
                'image' => 'banners/shipping-banner.jpg',
                'sort_order' => 3,
                'is_active' => true,
                'starts_at' => null,
                'ends_at' => Carbon::now()->addDays(30),
                'product_id' => null,
                'category_id' => null,
            ],

            // Banner 4: Seasonal - Dates offer (linked to category)
            [
                'badge' => '100% Pure & Natural',
                'title' => 'Ramadan Special',
                'subtitle' => 'Premium Dates',
                'description' => 'Get 15% off on all dates. Stock up for the holy month.',
                'button_text' => 'Shop Dates',
                'image' => 'banners/ramadan-banner.jpg',
                'sort_order' => 4,
                'is_active' => true,
                'starts_at' => Carbon::now()->subDays(5),
                'ends_at' => Carbon::now()->addDays(20),
                'product_id' => null,
                'category_id' => $datesCategory?->id,
            ],

            // Banner 5: Scheduled future banner
            [
                'badge' => '100% Pure & Natural',
                'title' => 'Eid Collection',
                'subtitle' => 'Gift Packs Available',
                'description' => 'Beautifully packaged gift boxes for your loved ones. Pre-order now!',
                'button_text' => 'Pre-order',
                'image' => 'banners/eid-banner.jpg',
                'sort_order' => 5,
                'is_active' => true,
                'starts_at' => Carbon::now()->addDays(15),
                'ends_at' => Carbon::now()->addDays(45),
                'product_id' => null,
                'category_id' => null,
            ],

            // Banner 6: New product launch (linked to product)
            [
                'badge' => '100% Pure & Natural',
                'title' => 'New Arrival',
                'subtitle' => 'Organic Cold-Pressed Oils',
                'description' => 'Introducing our new range of cold-pressed oils. 100% organic and chemical-free.',
                'button_text' => 'Explore',
                'image' => 'banners/oils-banner.jpg',
                'sort_order' => 6,
                'is_active' => true,
                'starts_at' => Carbon::now()->subDays(2),
                'ends_at' => Carbon::now()->addDays(60),
                'product_id' => null,
                'category_id' => Category::where('name', 'Oils')->first()?->id,
            ],

            // Banner 7: Inactive banner
            [
                'badge' => '100% Pure & Natural',
                'title' => 'Summer Sale',
                'subtitle' => 'Up to 20% Off',
                'description' => 'Beat the heat with our summer specials. Offer ends soon!',
                'button_text' => 'Shop Sale',
                'image' => 'banners/summer-banner.jpg',
                'sort_order' => 7,
                'is_active' => false,
                'starts_at' => Carbon::now()->subDays(30),
                'ends_at' => Carbon::now()->subDays(10),
                'product_id' => null,
                'category_id' => null,
            ],

            // Banner 8: Expired banner
            [
                'badge' => '100% Pure & Natural',
                'title' => 'Flash Sale',
                'subtitle' => '24 Hours Only',
                'description' => 'Special discounts on selected items. Hurry, limited stock!',
                'button_text' => 'View Deals',
                'image' => 'banners/flash-sale-banner.jpg',
                'sort_order' => 8,
                'is_active' => true,
                'starts_at' => Carbon::now()->subDays(5),
                'ends_at' => Carbon::now()->subDays(1),
                'product_id' => null,
                'category_id' => null,
            ],
        ];

        foreach ($banners as $banner) {
            HeroBanner::create($banner);
        }
    }
}

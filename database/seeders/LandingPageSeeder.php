<?php

namespace Database\Seeders;

use App\Domains\Landing\Models\LandingPage;
use App\Domains\Product\Models\Product;
use Illuminate\Database\Seeder;

class LandingPageSeeder extends Seeder
{
    public function run(): void
    {
        $landings = [
            [
                'slug'             => 'himalayan-pink-salt',
                'type'             => 'product',
                'product_slug'     => 'himalayan-pink-salt',
                'title'            => 'Himalayan Pink Salt - 100% Pure & Mineral Rich',
                'blade_template'   => 'himalayan-pink-salt',
                'content'          => 'Experience the natural goodness of our Himalayan Pink Salt, harvested from ancient sea beds deep within the Himalayan mountains. Perfect for culinary use and wellness.',
                'meta_title'       => 'Buy Pure Himalayan Pink Salt Online | Bionic',
                'meta_description' => 'Organic Himalayan Pink Salt sourced from pristine mountains. High in minerals, low in sodium.',
                'pixel_event_name' => 'ViewContent',
                'config'           => [
                    'free_delivery_amount' => 1000,
                    'discount_percent'     => 10,
                    'hero_style'           => 'dark',
                    'show_reviews'         => true
                ],
            ],
            [
                'slug'             => 'beetroot-powder-offer',
                'type'             => 'product',
                'product_slug'     => 'beetroot-powder',
                'title'            => 'Superfood Beetroot Powder - Natural Energy Booster',
                'blade_template'   => 'product-default',
                'content'          => 'Our Beetroot Powder is crafted from premium quality beets. Perfect for smoothies, baking, and natural food coloring.',
                'meta_title'       => 'Premium Beetroot Powder | Bionic Superfoods',
                'meta_description' => 'Boost your energy levels naturally with our pure Beetroot Powder. No additives, no preservatives.',
                'pixel_event_name' => 'ViewContent',
                'config'           => [
                    'free_delivery_qty' => 2,
                    'discount_amount'   => 50,
                    'hero_style'        => 'light',
                ],
            ],
            [
                'slug'             => 'mangrove-gold-honey',
                'type'             => 'product',
                'product_slug'     => 'mangrove-gold-honey',
                'title'            => 'Mangrove Gold Honey - Rare & Medicinal',
                'blade_template'   => 'product-modern',
                'content'          => 'Sourced from the heart of the Sundarbans, this Mangrove Gold Honey is unique in flavor and rich in health benefits.',
                'meta_title'       => 'Authentic Mangrove Honey | Sundarbans Gold',
                'meta_description' => 'Rare mangrove honey with medicinal properties. 100% raw and unprocessed.',
                'pixel_event_name' => 'ViewContent',
                'config'           => [
                    'free_delivery_amount' => 1500,
                    'hero_style'           => 'golden',
                ],
            ],
            [
                'slug'             => 'vital-mix-health',
                'type'             => 'product',
                'product_slug'     => 'vital-mix',
                'title'            => 'Vital Mix - Premium Nuts & Seeds Powerhouse',
                'blade_template'   => 'product-default',
                'content'          => 'A carefully curated blend of essential nuts and seeds, designed to provide a boost of nutrition and energy for your busy lifestyle.',
                'meta_title'       => 'Vital Mix Nuts & Seeds | Daily Health Booster',
                'meta_description' => 'A perfect blend of roasted nuts and seeds for your daily energy needs.',
                'pixel_event_name' => 'ViewContent',
                'config'           => [
                    'discount_percent' => 15,
                    'show_cta'         => true,
                ],
            ],
            [
                'slug'             => 'medjool-dates-premium',
                'type'             => 'product',
                'product_slug'     => 'egyptian-medjool',
                'title'            => 'Egyptian Medjool Dates - The King of Dates',
                'blade_template'   => 'product-modern',
                'content'          => 'Large, succulent, and sweet. Our Egyptian Medjool dates are the perfect natural treat for any occasion.',
                'meta_title'       => 'Premium Medjool Dates | King of Dates from Egypt',
                'meta_description' => 'Handpicked Medjool dates. Rich in fiber and natural sugars.',
                'pixel_event_name' => 'ViewContent',
                'config'           => [
                    'free_delivery_amount' => 2000,
                    'hero_style'           => 'luxury',
                ],
            ],
        ];

        foreach ($landings as $data) {
            $product = Product::where('slug', $data['product_slug'])->first();

            if ($product) {
                unset($data['product_slug']);
                $data['product_id'] = $product->id;
                $data['hero_image'] = $product->thumbnail;
                $data['is_active']  = true;

                LandingPage::updateOrCreate(
                    ['slug' => $data['slug']],
                    $data
                );
            }
        }
    }
}

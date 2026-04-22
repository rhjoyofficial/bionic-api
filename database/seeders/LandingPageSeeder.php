<?php

namespace Database\Seeders;

use App\Domains\Landing\Models\LandingPage;
use App\Domains\Landing\Models\LandingPageItem;
use App\Domains\Product\Models\Combo;
use App\Domains\Product\Models\Product;
use App\Domains\Product\Models\ProductVariant;
use Illuminate\Database\Seeder;

class LandingPageSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Helper ───────────────────────────────────────────────────────
        $variant = fn(string $productName, string $variantTitle): ?ProductVariant =>
            ProductVariant::where('title', $variantTitle)
                ->whereHas('product', fn($q) => $q->where('name', 'LIKE', "%{$productName}%"))
                ->first();

        $combo = fn(string $slug): ?Combo => Combo::where('slug', $slug)->first();

        // ─── 1. PRODUCT TYPE — Himalayan Pink Salt (custom template) ─────
        $pinkSalt = Product::where('slug', 'himalayan-pink-salt')->first();
        if ($pinkSalt) {
            LandingPage::updateOrCreate(
                ['slug' => 'himalayan-pink-salt'],
                [
                    'type'             => LandingPage::TYPE_PRODUCT,
                    'product_id'       => $pinkSalt->id,
                    'title'            => 'হিমালয়ান পিঙ্ক সল্ট — ১০০% বিশুদ্ধ খনিজ লবণ',
                    'blade_template'   => 'himalayan-pink-salt',
                    'hero_image'       => $pinkSalt->thumbnail,
                    'content'          => 'হিমালয়ের প্রাচীন পাহাড় থেকে সংগৃহীত ৮৪+ প্রাকৃতিক খনিজ সমৃদ্ধ বিশুদ্ধ লবণ। রান্না, ডিটক্স ও ত্বকের যত্নে অতুলনীয়।',
                    'meta_title'       => 'হিমালয়ান পিঙ্ক সল্ট কিনুন | Bionic',
                    'meta_description' => '১০০% প্রাকৃতিক হিমালয়ান পিঙ্ক সল্ট। ৮৪+ খনিজ সমৃদ্ধ, কেমিক্যালমুক্ত। সারাদেশে ডেলিভারি।',
                    'pixel_event_name' => 'ViewContent',
                    'is_active'        => true,
                    'config'           => [
                        'free_delivery_amount' => 1000,
                        'discount_percent'     => 10,
                        'hero_style'           => 'pink',
                        'show_reviews'         => true,
                    ],
                ]
            );
        }

        // ─── 2. PRODUCT TYPE — Beetroot Powder (default template) ────────
        $beetroot = Product::where('slug', 'beetroot-powder')->first();
        if ($beetroot) {
            LandingPage::updateOrCreate(
                ['slug' => 'beetroot-powder-offer'],
                [
                    'type'             => LandingPage::TYPE_PRODUCT,
                    'product_id'       => $beetroot->id,
                    'title'            => 'Superfood Beetroot Powder — Natural Energy Booster',
                    'blade_template'   => 'product-default',
                    'hero_image'       => $beetroot->thumbnail,
                    'content'          => 'Our Beetroot Powder is crafted from premium quality beets. Perfect for smoothies, baking, and natural food coloring.',
                    'meta_title'       => 'Premium Beetroot Powder | Bionic Superfoods',
                    'meta_description' => 'Boost your energy naturally with our pure Beetroot Powder. No additives, no preservatives.',
                    'pixel_event_name' => 'ViewContent',
                    'is_active'        => true,
                    'config'           => [
                        'discount_amount' => 50,
                        'hero_style'      => 'light',
                    ],
                ]
            );
        }

        // ─── 3. PRODUCT TYPE — Mangrove Gold Honey (default template) ────
        $honey = Product::where('slug', 'mangrove-gold-honey')->first();
        if ($honey) {
            LandingPage::updateOrCreate(
                ['slug' => 'mangrove-gold-honey'],
                [
                    'type'             => LandingPage::TYPE_PRODUCT,
                    'product_id'       => $honey->id,
                    'title'            => 'Mangrove Gold Honey — Rare & Medicinal',
                    'blade_template'   => 'product-default',
                    'hero_image'       => $honey->thumbnail,
                    'content'          => 'Sourced from the heart of the Sundarbans, this Mangrove Gold Honey is unique in flavor and rich in health benefits.',
                    'meta_title'       => 'Authentic Mangrove Honey | Sundarbans Gold',
                    'meta_description' => 'Rare mangrove honey with medicinal properties. 100% raw and unprocessed.',
                    'pixel_event_name' => 'ViewContent',
                    'is_active'        => true,
                    'config'           => [
                        'free_delivery_amount' => 1500,
                        'hero_style'           => 'golden',
                    ],
                ]
            );
        }

        // ─── 4. COMBO TYPE — Ramadan Premium Date Box ────────────────────
        $ramadanBox = $combo('ramadan-premium-date-box');
        if ($ramadanBox) {
            LandingPage::updateOrCreate(
                ['slug' => 'ramadan-premium-date-box'],
                [
                    'type'             => LandingPage::TYPE_COMBO,
                    'combo_id'         => $ramadanBox->id,
                    'title'            => 'Ramadan Premium Date Box — Luxury Gift Set',
                    'blade_template'   => 'combo-default',
                    'hero_image'       => $ramadanBox->image,
                    'content'          => 'A luxurious collection of Ajwa, Medjool, and Mariyam dates — the perfect gift for any occasion.',
                    'meta_title'       => 'Ramadan Premium Date Gift Box | Bionic',
                    'meta_description' => 'Hand-curated luxury date box featuring Ajwa, Medjool, and Mariyam dates. Perfect Ramadan gift.',
                    'pixel_event_name' => 'ViewContent',
                    'is_active'        => true,
                    'config'           => [
                        'free_delivery_amount' => 2000,
                        'hero_style'           => 'luxury',
                    ],
                ]
            );
        }

        // ─── 5. SALES TYPE — Healthy Pantry Bundle (sales-picker template) ─
        $saltVariant140    = $variant('Himalayan Pink Salt', '140gm');
        $saltVariant1kg    = $variant('Himalayan Pink Salt', '1KG');
        $gheeVariant       = $variant('Premium Ghee', '350gm');
        $honeyVariant      = $variant('Mangrove Gold Honey', '500gm');
        $chiaVariant       = $variant('Chia Seeds', '500gm');

        $salesPage = LandingPage::updateOrCreate(
            ['slug' => 'healthy-pantry-bundle'],
            [
                'type'             => LandingPage::TYPE_SALES,
                'title'            => 'Healthy Pantry Bundle — আপনার পছন্দের কম্বো বানান',
                'blade_template'   => 'sales-picker',
                'hero_image'       => null,
                'content'          => 'আমাদের সেরা স্বাস্থ্যকর পণ্যগুলো থেকে আপনার পছন্দেরটি বেছে নিন এবং একসাথে অর্ডার করুন।',
                'meta_title'       => 'Healthy Pantry Bundle | Bionic',
                'meta_description' => 'Mix and match our best healthy products. Pink salt, ghee, honey, chia seeds and more.',
                'pixel_event_name' => 'ViewContent',
                'is_active'        => true,
                'config'           => [
                    'free_delivery_amount' => 2000,
                    'discount_percent'     => 5,
                ],
            ]
        );

        // Clear and recreate items for sales page
        $salesPage->items()->delete();
        $sort = 1;
        foreach (array_filter([
            ['variant' => $saltVariant140,  'preselected' => true],
            ['variant' => $saltVariant1kg,  'preselected' => false],
            ['variant' => $gheeVariant,     'preselected' => true],
            ['variant' => $honeyVariant,    'preselected' => false],
            ['variant' => $chiaVariant,     'preselected' => false],
        ], fn($r) => $r['variant'] !== null) as $row) {
            LandingPageItem::create([
                'landing_page_id'    => $salesPage->id,
                'product_variant_id' => $row['variant']->id,
                'combo_id'           => null,
                'is_preselected'     => $row['preselected'],
                'sort_order'         => $sort++,
            ]);
        }

        // ─── 6. LISTING TYPE — Full Catalog Browse ───────────────────────
        $chia   = $variant('Chia Seeds', '500gm');
        $tokma  = $variant('Premium Tokma', '240gm');
        $ghee   = $variant('Premium Ghee', '350gm');
        $bso    = $variant('Black Seed Oil', '200ml');
        $fHoney = $variant('Floral Gold Honey', '255gm');

        $listingPage = LandingPage::updateOrCreate(
            ['slug' => 'shop-natural'],
            [
                'type'             => LandingPage::TYPE_LISTING,
                'title'            => 'Shop Natural — প্রাকৃতিক পণ্যের সংগ্রহ',
                'blade_template'   => 'listing-default',
                'hero_image'       => null,
                'content'          => 'আমাদের সেরা প্রাকৃতিক ও জৈব পণ্যের সংগ্রহ থেকে বেছে নিন। সহজেই কার্টে যোগ করুন ও এক ক্লিকে অর্ডার করুন।',
                'meta_title'       => 'Shop Natural Products | Bionic',
                'meta_description' => 'Browse our curated selection of natural and organic products.',
                'pixel_event_name' => 'ViewContent',
                'is_active'        => true,
                'config'           => [],
            ]
        );

        $listingPage->items()->delete();
        $sort = 1;
        foreach (array_filter([
            $saltVariant140,
            $saltVariant1kg,
            $chia,
            $tokma,
            $ghee,
            $bso,
            $fHoney,
        ]) as $v) {
            LandingPageItem::create([
                'landing_page_id'    => $listingPage->id,
                'product_variant_id' => $v->id,
                'combo_id'           => null,
                'is_preselected'     => false,
                'sort_order'         => $sort++,
            ]);
        }
    }
}

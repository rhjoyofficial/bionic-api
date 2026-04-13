<?php

namespace Database\Seeders;

use App\Domains\Product\Models\Combo;
use App\Domains\Product\Models\ComboItem;
use App\Domains\Product\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComboSeeder extends Seeder
{
  public function run(): void
  {
    // Helper to find variants by Product Name and Weight/Title
    $findVariant = function ($productName, $variantTitle) {
      return ProductVariant::where('title', $variantTitle)
        ->whereHas('product', function ($q) use ($productName) {
          $q->where('name', 'LIKE', "%{$productName}%");
        })->first();
    };

    // --- 1. RAMADAN PREMIUM DATE BOX (Luxury Gift) ---
    $combo1 = Combo::create([
      'title' => 'Ramadan Premium Date Box (রমজান প্রিমিয়াম খেজুর বক্স)',
      'slug' => Str::slug('Ramadan Premium Date Box'),
      'description' => 'A luxury collection of Ajwa, Medjool, and Mariyam dates.',
      'image' => 'combos/premium-dates.jpg',
      'pricing_mode' => 'manual',
      'manual_price' => 5900.00,
      'is_active' => true,
      'is_featured' => true,
    ]);
    $this->addItems($combo1, [
      [$findVariant('Ajwa', '1KG Large'), 1],
      [$findVariant('Egyptian Medjool', '1KG Large'), 1],
      [$findVariant('Mariyam', '1KG'), 1],
    ]);

    // --- 2. ULTIMATE ENERGY BOOSTER (Health Focus) ---
    $combo2 = Combo::create([
      'title' => 'Ultimate Energy Booster (আলটিমেট এনার্জি বুস্টার)',
      'slug' => Str::slug('Ultimate Energy Booster'),
      'description' => 'Combine Brain Booster Mix and Mangrove Honey for peak performance.',
      'image' => 'combos/energy-booster.jpg',
      'pricing_mode' => 'auto',
      'discount_type' => 'percentage',
      'discount_value' => 10,
      'is_active' => true,
      'is_featured' => true,
    ]);
    $this->addItems($combo2, [
      [$findVariant('Brain Booster Mix', '205gm'), 1],
      [$findVariant('Mangrove Gold Honey', '500gm'), 1],
      [$findVariant('Black Seed Oil', '200ml'), 1],
    ]);

    // --- 3. PURE KITCHEN ESSENTIALS (Daily Needs) ---
    $combo3 = Combo::create([
      'title' => 'Pure Kitchen Essentials (বিশুদ্ধ কিচেন এসেনশিয়ালস)',
      'slug' => Str::slug('Pure Kitchen Essentials'),
      'description' => 'Mustard Oil, Ghee, and Pink Salt for a healthy kitchen.',
      'image' => 'combos/kitchen-essentials.jpg',
      'pricing_mode' => 'auto',
      'discount_type' => 'fixed',
      'discount_value' => 150, // 150 TK flat discount
      'is_active' => true,
    ]);
    $this->addItems($combo3, [
      [$findVariant('Mustard Oil', '5L'), 1],
      [$findVariant('Premium Ghee', '350gm'), 1],
      [$findVariant('Himalayan Pink Salt', '1KG'), 1],
    ]);

    // --- 4. WEIGHT MANAGEMENT KIT (Detox Focus) ---
    $combo4 = Combo::create([
      'title' => 'Weight Management Kit (ওজন নিয়ন্ত্রণ কিট)',
      'slug' => Str::slug('Weight Management Kit'),
      'description' => 'Natural Chia Seeds and Tokma for healthy digestion.',
      'image' => 'combos/weight-management.jpg',
      'pricing_mode' => 'manual',
      'manual_price' => 1050.00,
      'is_active' => true,
    ]);
    $this->addItems($combo4, [
      [$findVariant('Chia Seeds', '500gm'), 1],
      [$findVariant('Premium Tokma', '240gm'), 1],
      [$findVariant('Himalayan Pink Salt', '140gm'), 1],
    ]);

    // --- 5. SWEET & TRADITIONAL BUNDLE ---
    $combo5 = Combo::create([
      'title' => 'Sweet & Traditional Bundle (মিষ্টি ও ঐতিহ্যবাহী বান্ডেল)',
      'slug' => Str::slug('Sweet Traditional Bundle'),
      'description' => 'Natural Goler Gurr and Floral Honey.',
      'image' => 'combos/sweet-bundle.jpg',
      'pricing_mode' => 'auto',
      'discount_type' => 'percentage',
      'discount_value' => 5,
      'is_active' => true,
    ]);
    $this->addItems($combo5, [
      [$findVariant('Goler Gurr', '500gm'), 2],
      [$findVariant('Floral Gold Honey', '255gm'), 1],
    ]);

    // --- 6. SUPERFOOD POWDER PACK ---
    $combo6 = Combo::create([
      'title' => 'Immunity Superfood Pack (ইমিউনিটি সুপারফুড প্যাক)',
      'slug' => Str::slug('Immunity Superfood Pack'),
      'description' => 'Beetroot Powder and Black Seed Oil for strong immunity.',
      'image' => 'combos/immunity-pack.jpg',
      'pricing_mode' => 'manual',
      'manual_price' => 1350.00,
      'is_active' => true,
    ]);
    $this->addItems($combo6, [
      [$findVariant('Beetroot Powder', '200gm'), 1],
      [$findVariant('Black Seed Oil', '200ml'), 1],
    ]);

    // --- 7. LUXURY AJWA & MEDJOOL COMBO ---
    $combo7 = Combo::create([
      'title' => 'Royal Date Duo (রয়্যাল খেজুর ডুও)',
      'slug' => Str::slug('Royal Date Duo'),
      'description' => 'The two finest dates: Ajwa Premium and Medjool Jumbo.',
      'image' => 'combos/royal-dates.jpg',
      'pricing_mode' => 'auto',
      'discount_type' => 'percentage',
      'discount_value' => 12,
      'is_active' => true,
    ]);
    $this->addItems($combo7, [
      [$findVariant('Ajwa Date', '1KG Premium'), 1],
      [$findVariant('Egyptian Medjool', '1KG Jambo'), 1],
    ]);

    // --- 8. BRAIN & VITALITY BOOST ---
    $combo8 = Combo::create([
      'title' => 'Brain & Vitality Boost (ব্রেন ও ভাইটালিটি বুস্ট)',
      'slug' => Str::slug('Brain Vitality Boost'),
      'description' => 'A double mix of Brain Booster and Vital Mix.',
      'image' => 'combos/vitality-boost.jpg',
      'pricing_mode' => 'manual',
      'manual_price' => 1100.00,
      'is_active' => true,
    ]);
    $this->addItems($combo8, [
      [$findVariant('Brain Booster Mix', '205gm'), 1],
      [$findVariant('Vital Mix', '205gm'), 1],
    ]);

    // --- 9. HEALTHY HEART OIL PACK ---
    $combo9 = Combo::create([
      'title' => 'Healthy Heart Oil Pack (হার্ট হেলদি অয়েল প্যাক)',
      'slug' => Str::slug('Healthy Heart Oil Pack'),
      'description' => 'Virgin Coconut Oil and Black Seed Oil.',
      'image' => 'combos/heart-oil.jpg',
      'pricing_mode' => 'auto',
      'discount_type' => 'percentage',
      'discount_value' => 8,
      'is_active' => true,
    ]);
    $this->addItems($combo9, [
      [$findVariant('Edible Virgin Coconut Oil', '360ml'), 1],
      [$findVariant('Black Seed Oil', '200ml'), 1],
    ]);

    // --- 10. MEGA SAVER FAMILY PACK ---
    $combo10 = Combo::create([
      'title' => 'Mega Saver Family Pack (মেগা সেভার ফ্যামিলি প্যাক)',
      'slug' => Str::slug('Mega Saver Family Pack'),
      'description' => 'Stock up on large sizes of Ghee, Oil, and Sukkari dates.',
      'image' => 'combos/family-pack.jpg',
      'pricing_mode' => 'manual',
      'manual_price' => 5200.00,
      'is_active' => true,
    ]);
    $this->addItems($combo10, [
      [$findVariant('Mustard Oil', '8L'), 1],
      [$findVariant('Sukkari', '3KG'), 1],
      [$findVariant('Premium Ghee', '350gm'), 1],
    ]);
  }

  /**
   * Helper to attach items to a combo safely
   */
  private function addItems($combo, $items)
  {
    foreach ($items as $item) {
      $variant = $item[0];
      $qty = $item[1];

      if ($variant) {
        ComboItem::create([
          'combo_id' => $combo->id,
          'product_variant_id' => $variant->id,
          'quantity' => $qty,
        ]);
      }
    }
  }
}

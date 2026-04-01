<?php

namespace Database\Seeders;

use App\Domains\Product\Models\ProductVariant as ModelsProductVariant;
use App\Domains\Product\Models\Combo;
use App\Domains\Product\Models\ComboItem;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComboSeeder extends Seeder
{
  public function run(): void
  {
    // 1. Fetch some existing variants to build bundles
    // Adjust these IDs or queries based on your actual data
    $variants = ModelsProductVariant::take(5)->get();

    if ($variants->count() < 2) {
      $this->command->warn("Not enough product variants found. Please seed products first.");
      return;
    }

    // --- COMBO 1: The "Daily Essentials" (Auto-Pricing) ---
    // This combo will automatically sum the prices of the variants inside it.
    $combo1 = Combo::create([
      'title' => 'Daily Essentials Pack',
      'slug' => Str::slug('Daily Essentials Pack'),
      'description' => 'A curated collection of our best-selling daily items.',
      'pricing_mode' => 'auto',
      'discount_type' => 'percentage',
      'discount_value' => 10, // 10% off the total sum
      'is_active' => true,
      'is_featured' => true,
    ]);

    ComboItem::create([
      'combo_id' => $combo1->id,
      'product_variant_id' => $variants[0]->id,
      'quantity' => 2,
    ]);

    ComboItem::create([
      'combo_id' => $combo1->id,
      'product_variant_id' => $variants[1]->id,
      'quantity' => 1,
    ]);


    // --- COMBO 2: The "Mega Saver Bundle" (Manual Pricing) ---
    // This combo has a strictly defined price regardless of individual item costs.
    $combo2 = Combo::create([
      'title' => 'Mega Saver Electronics Bundle',
      'slug' => Str::slug('Mega Saver Electronics Bundle'),
      'description' => 'Get everything you need in one go at a massive discount.',
      'pricing_mode' => 'manual',
      'manual_price' => 1500.00, // Fixed price for the whole set
      'is_active' => true,
      'is_featured' => false,
    ]);

    // Adding 3 different items to this bundle
    foreach ($variants->take(3) as $variant) {
      ComboItem::create([
        'combo_id' => $combo2->id,
        'product_variant_id' => $variant->id,
        'quantity' => 1,
      ]);
    }
  }
}

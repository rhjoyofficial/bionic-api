<?php

namespace Database\Seeders;

use App\Domains\Marketing\Models\LandingPage;
use App\Domains\Product\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LandingPageSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::query()->take(3)->get();

        if ($products->isEmpty()) {
            $this->command?->warn('No products found. Seed products before LandingPageSeeder.');
            return;
        }

        foreach ($products as $product) {
            $slug = Str::slug($product->name) . '-offer';

            LandingPage::updateOrCreate(
                ['slug' => $slug],
                [
                    'product_id' => $product->id,
                    'title' => $product->name . ' Special Offer',
                    'hero_image' => $product->thumbnail,
                    'content' => "Limited-time deal for {$product->name}. Order now and enjoy fresh stock delivery.",
                    'meta_title' => "{$product->name} Offer | Bionic",
                    'meta_description' => "Buy {$product->name} from Bionic with the latest promotional offer.",
                    'pixel_event_name' => 'ViewContent',
                    'is_active' => true,
                ]
            );
        }
    }
}

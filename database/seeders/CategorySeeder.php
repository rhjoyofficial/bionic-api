<?php

namespace Database\Seeders;

use App\Domains\Category\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Honey', 'description' => 'Pure and natural honey from various sources', 'image' => 'categories/honey.gif'],
            ['name' => 'Dates', 'description' => 'Premium quality dates from around the world', 'image' => 'categories/dates.gif'],
            ['name' => 'Oils', 'description' => 'Cold-pressed and organic cooking oils', 'image' => 'categories/oils.gif'],
            ['name' => 'Seeds', 'description' => 'Nutritious seeds for healthy living', 'image' => 'categories/seeds.gif'],
            ['name' => 'Nuts', 'description' => 'Fresh and roasted nuts', 'image' => 'categories/nuts.gif'],
            ['name' => 'Ghee', 'description' => 'Pure desi ghee and clarified butter', 'image' => 'categories/ghee.gif'],
            ['name' => 'Dry Fruits', 'description' => 'Premium quality dry fruits', 'image' => 'categories/dry_fruits.gif'],
            ['name' => 'Spices', 'description' => 'Organic and pure spices', 'image' => 'categories/spices.gif'],
        ];

        foreach ($categories as $index => $catData) {
            Category::create([
                'name' => $catData['name'],
                'slug' => Str::slug($catData['name']),
                'description' => $catData['description'],
                'image' => $catData['image'],
                'is_active' => true,
                'sort_order' => $index + 1,
            ]);
        }
    }
}

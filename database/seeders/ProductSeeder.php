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
            'category_id' => $getCategoryId('Salts'),
            'name' => 'Himalayan Pink Salt (হিমালয়ান পিঙ্ক সল্ট)',
            'slug' => Str::slug('Himalayan Pink Salt'),
            'base_price' => 190,
            'sku' => 'SLT-PNK-001',
            'thumbnail' => 'products/pink-salt.jpg',

            'short_description' => 'Premium quality Himalayan Pink Salt sourced from the pristine mountains, perfect for enhancing flavor and adding essential minerals to your dishes.',
            'description' => '<span class="text-black font-sans font-semibold">Himalayan Pink Salt (হিমালয়ান পিঙ্ক সল্ট)</span> — Experience the natural goodness of our Himalayan Pink Salt, harvested from ancient sea beds deep within the Himalayan mountains. This salt is renowned for its unique pink hue, which comes from the trace minerals it contains, including iron, magnesium, and potassium. Our pink salt is perfect for seasoning your meals, adding a touch of elegance to your dishes, and even for use in salt lamps and bath salts. Elevate your culinary creations with the rich flavor and health benefits of our Himalayan Pink Salt.',

            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,

            'nutritional_info' => [
                'sodium' => '38%',
                'calcium' => '1%',
                'iron' => '2%',
                'magnesium' => '1%',
                'potassium' => '1%',
            ],
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

            'short_description' => 'Pure and natural beetroot powder made from high-quality beets, perfect for adding vibrant color and earthy flavor to your recipes.',
            'description' => '<span class="text-black font-sans font-semibold">Beetroot Powder (বিটরুট পাউডার)</span> — Our Beetroot Powder is crafted from premium quality beets that are carefully dried and ground into a fine powder. This vibrant red powder is packed with essential nutrients, including vitamins, minerals, and antioxidants. It’s perfect for adding a natural sweetness and earthy flavor to smoothies, juices, soups, and baked goods. Whether you’re looking to boost your nutrition or simply want to add a pop of color to your dishes, our Beetroot Powder is a versatile and delicious choice.',

            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,

            'nutritional_info' => [
                'calories' => '40',
                'carbohydrates' => '9g',
                'protein' => '1g',
                'fiber' => '2g',
                'vitamin_c' => '8%',
                'iron' => '4%',
            ],
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

            'short_description' => 'Premium quality mangrove gold honey sourced from the pristine mangrove forests, perfect for adding natural sweetness and health benefits to your dishes.',
            'description' => '<span class="text-black font-sans font-semibold">Mangrove Gold Honey (ম্যানগ্রোভ গোল্ড হানি)</span> — Our Mangrove Gold Honey is carefully harvested from the pristine mangrove forests, known for their rich biodiversity and unique ecosystem. This honey is characterized by its distinct golden color and robust flavor, making it a premium choice for those who appreciate the finest natural sweeteners. Whether you’re looking to enhance your morning tea or add a touch of sweetness to your desserts, our Mangrove Gold Honey delivers an exceptional taste and numerous health benefits.',

            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,

            'nutritional_info' => [
                'calories' => '60',
                'carbohydrates' => '17g',
                'protein' => '0g',
                'fiber' => '0g',
                'vitamins' => 'Trace amounts of B vitamins',
                'minerals' => 'Trace amounts of calcium, iron, magnesium, potassium',
            ],
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

            'short_description' => 'Delicate and aromatic floral gold honey sourced from the lush gardens, perfect for adding a subtle sweetness and elegant flavor to your beverages and desserts.',
            'description' => '<span class="text-black font-sans font-semibold">Floral Gold Honey (ফ্লোরাল গোল্ড হানি)</span> — Our Floral Gold Honey is meticulously crafted from the nectar of blooming flowers, resulting in a light and aromatic honey with a delicate golden hue. This honey is known for its subtle sweetness and elegant flavor, making it an excellent choice for those who appreciate the finer details in their sweeteners. Whether you’re looking to enhance your tea or add a touch of natural sweetness to your baked goods, our Floral Gold Honey brings a refined taste to every dish.',

            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,

            'nutritional_info' => [
                'calories' => '60',
                'carbohydrates' => '17g',
                'protein' => '0g',
                'fiber' => '0g',
                'vitamins' => 'Trace amounts of B vitamins',
                'minerals' => 'Trace amounts of calcium, iron, magnesium, potassium',
            ],
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

            'short_description' => 'Rich and flavorful premium ghee made from high-quality butter, perfect for cooking, baking, and adding a delicious depth of flavor to your dishes.',
            'description' => '<span class="text-black font-sans font-semibold">Premium Ghee (প্রিমিয়াম ঘি)</span> — Our Premium Ghee is crafted from the finest quality butter, carefully simmered to create a rich and flavorful ghee that is perfect for all your culinary needs. This golden elixir is known for its high smoke point, making it ideal for frying, sautéing, and roasting. Whether you’re looking to enhance the flavor of your curries or add a delicious depth to your baked goods, our Premium Ghee delivers exceptional taste and versatility in every dish.',

            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,

            'nutritional_info' => [
                'calories' => '120',
                'fat' => '14g',
                'saturated_fat' => '9g',
                'cholesterol' => '35mg',
                'vitamin_a' => '15%',
                'vitamin_e' => '2%',
            ],
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

            'short_description' => 'Superior quality dates with a rich, sweet flavor and tender texture, perfect for snacking or adding to your favorite recipes.',
            'description' => '<span class="text-black font-sans font-semibold">Kalmi Super Premium (কালমি সুপার প্রিমিয়াম)</span> — Our Kalmi Super Premium Dates are handpicked for their exceptional quality and rich, sweet flavor. These dates are known for their tender texture and natural sweetness, making them an excellent choice for those who appreciate the finer details in their snacks. Whether you’re looking to enjoy them on their own or incorporate them into your favorite recipes, our Kalmi Super Premium Dates deliver a delightful taste in every bite.',

            'is_active' => true,

            'nutritional_info' => [
                'calories' => '60',
                'carbohydrates' => '17g',
                'protein' => '0g',
                'fiber' => '0g',
                'vitamins' => 'Trace amounts of B vitamins',
                'minerals' => 'Trace amounts of calcium, iron, magnesium, potassium',
            ],
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

            'short_description' => 'Superior quality dates with a rich, sweet flavor and tender texture, perfect for snacking or adding to your favorite recipes.',
            'description' => '<span class="text-black font-sans font-semibold">Mariyam Super Premium Dates (মরিয়ম সুপার প্রিমিয়াম খেজুর)</span> — Our Mariyam Super Premium Dates are handpicked for their exceptional quality and rich, sweet flavor. These dates are known for their tender texture and natural sweetness, making them an excellent choice for those who appreciate the finer details in their snacks. Whether you’re looking to enjoy them on their own or incorporate them into your favorite recipes, our Mariyam Super Premium Dates deliver a delightful taste in every bite.',

            'is_active' => true,
            'nutritional_info' => [
                'calories' => '60',
                'carbohydrates' => '17g',
                'protein' => '0g',
                'fiber' => '0g',
                'vitamins' => 'Trace amounts of B vitamins',
                'minerals' => 'Trace amounts of calcium, iron, magnesium, potassium',
            ],
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

            'short_description' => 'Premium quality Egyptian Medjool dates with a rich, sweet flavor and tender texture, perfect for snacking or adding to your favorite recipes.',
            'description' => '<span class="text-black font-sans font-semibold">Egyptian Medjool (মেডজুল খেজুর)</span> — Our Egyptian Medjool Dates are handpicked for their exceptional quality and rich, sweet flavor. These dates are known for their tender texture and natural sweetness, making them an excellent choice for those who appreciate the finer details in their snacks. Whether you’re looking to enjoy them on their own or incorporate them into your favorite recipes, our Egyptian Medjool Dates deliver a delightful taste in every bite.',

            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,

            'nutritional_info' => [
                'calories' => '60',
                'carbohydrates' => '17g',
                'protein' => '0g',
                'fiber' => '0g',
                'vitamins' => 'Trace amounts of B vitamins',
                'minerals' => 'Trace amounts of calcium, iron, magnesium, potassium',
            ],
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

            'short_description' => 'Premium quality Ajwa dates with a rich, sweet flavor and tender texture, perfect for snacking or adding to your favorite recipes.',
            'description' => '<span class="text-black font-sans font-semibold">Ajwa Date (আজওয়া খেজুর)</span> — Our Ajwa Dates are handpicked for their exceptional quality and rich, sweet flavor. These dates are known for their tender texture and natural sweetness, making them an excellent choice for those who appreciate the finer details in their snacks. Whether you’re looking to enjoy them on their own or incorporate them into your favorite recipes, our Ajwa Dates deliver a delightful taste in every bite.',

            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,

            'nutritional_info' => [
                'calories' => '60',
                'carbohydrates' => '17g',
                'protein' => '0g',
                'fiber' => '0g',
                'vitamins' => 'Trace amounts of B vitamins',
                'minerals' => 'Trace amounts of calcium, iron, magnesium, potassium',
            ],
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

            'short_description' => 'Premium quality Sukkari dates with a rich, sweet flavor and tender texture, perfect for snacking or adding to your favorite recipes.',
            'description' => '<span class="text-black font-sans font-semibold">Sukkari (সুক্কারি খেজুর)</span> — Our Sukkari Dates are handpicked for their exceptional quality and rich, sweet flavor. These dates are known for their tender texture and natural sweetness, making them an excellent choice for those who appreciate the finer details in their snacks. Whether you’re looking to enjoy them on their own or incorporate them into your favorite recipes, our Sukkari Dates deliver a delightful taste in every bite.',

            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,

            'nutritional_info' => [
                'calories' => '60',
                'carbohydrates' => '17g',
                'protein' => '0g',
                'fiber' => '0g',
                'vitamins' => 'Trace amounts of B vitamins',
                'minerals' => 'Trace amounts of calcium, iron, magnesium, potassium',
            ],
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

            'short_description' => 'A nutritious mix of brain-boosting nuts and seeds, perfect for a healthy snack or ingredient in your favorite recipes.',
            'description' => '<span class="text-black font-sans font-semibold">Brain Booster Mix (ব্রেন বুস্টার মিক্স)</span> — Our Brain Booster Mix is a carefully curated blend of premium nuts and seeds, designed to support cognitive function and overall brain health. Whether you’re looking to enhance your daily nutrition or add a delicious twist to your favorite recipes, our Brain Booster Mix delivers a delightful taste and a boost of essential nutrients in every bite.',

            'is_active' => true,

            'nutritional_info' => [
                'calories' => '150',
                'fat' => '10g',
                'protein' => '5g',
                'fiber' => '3g',
                'vitamins' => 'Trace amounts of B vitamins, vitamin E',
                'minerals' => 'Trace amounts of magnesium, zinc, iron',
            ],
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

            'short_description' => 'A wholesome mix of vital nuts and seeds, perfect for a nutritious snack or ingredient in your favorite recipes.',
            'description' => '<span class="text-black font-sans font-semibold">Vital Mix (ভাইটাল মিক্স)</span> — Our Vital Mix is a carefully curated blend of essential nuts and seeds, designed to provide a boost of nutrition and energy. Whether you’re looking to enhance your daily diet or add a delicious twist to your favorite recipes, our Vital Mix delivers a delightful taste and a boost of essential nutrients in every bite.',

            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,

            'nutritional_info' => [
                'calories' => '120',
                'fat' => '8g',
                'protein' => '4g',
                'fiber' => '2g',
                'vitamins' => 'Trace amounts of B vitamins, vitamin E',
                'minerals' => 'Trace amounts of magnesium, zinc, iron',
            ],
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

            'short_description' => 'Rich in omega-3 fatty acids and fiber, chia seeds are a powerhouse of nutrition for a healthy lifestyle.',
            'description' => '<span class="text-black font-sans font-semibold">Chia Seeds (চিয়া সীডস)</span> — Our Chia Seeds are a rich source of omega-3 fatty acids, fiber, and protein. These tiny seeds are packed with nutrients and offer a versatile way to boost your daily nutrition. Whether you’re looking to add them to your smoothies, yogurt, or baked goods, our Chia Seeds deliver a delightful taste and a boost of essential nutrients in every bite.',

            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,

            'nutritional_info' => [
                'calories' => '150',
                'fat' => '9g',
                'protein' => '5g',
                'fiber' => '10g',
                'vitamins' => 'Trace amounts of B vitamins, vitamin E',
                'minerals' => 'Trace amounts of calcium, magnesium, iron, zinc',
            ],
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

            'short_description' => 'Premium quality tokma seeds with a rich, nutty flavor and crunchy texture, perfect for snacking or adding to your favorite recipes.',
            'description' => '<span class="text-black font-sans font-semibold">Premium Tokma (প্রিমিয়াম তোকমা)</span> — Our Premium Tokma Seeds are handpicked for their exceptional quality and rich, nutty flavor. These seeds are known for their crunchy texture and natural taste, making them an excellent choice for those who appreciate the finer details in their snacks. Whether you’re looking to enjoy them on their own or incorporate them into your favorite recipes, our Premium Tokma Seeds deliver a delightful taste in every bite.',

            'is_active' => true,

            'nutritional_info' => [
                'calories' => '120',
                'fat' => '8g',
                'protein' => '4g',
                'fiber' => '2g',
                'vitamins' => 'Trace amounts of B vitamins, vitamin E',
                'minerals' => 'Trace amounts of magnesium, zinc, iron',
            ],
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

            'short_description' => 'Premium quality black seed oil with a rich, earthy flavor and numerous health benefits, perfect for cooking or as a natural remedy.',
            'description' => '<span class="text-black font-sans font-semibold">Black Seed Oil (কালোজিরার তেল)</span> — Our Black Seed Oil is carefully extracted from the finest quality black seeds, known for their rich, earthy flavor and numerous health benefits. This oil is perfect for cooking, adding a unique taste to your dishes, or as a natural remedy for various ailments. Whether you’re looking to enhance your culinary creations or support your overall wellness, our Black Seed Oil delivers exceptional quality and benefits in every drop.',

            'is_active' => true,

            'nutritional_info' => [
                'calories' => '120',
                'fat' => '14g',
                'saturated_fat' => '2g',
                'cholesterol' => '0mg',
                'vitamin_e' => '2%',
                'minerals' => 'Trace amounts of calcium, iron, magnesium, potassium',
            ],
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

            'short_description' => 'Premium quality edible virgin coconut oil with a rich, tropical flavor and numerous health benefits, perfect for cooking or as a natural remedy.',
            'description' => '<span class="text-black font-sans font-semibold">Edible Virgin Coconut Oil (ভোজ্য ভার্জিন নারকেল তেল)</span> — Our Edible Virgin Coconut Oil is carefully extracted from the finest quality coconuts, known for their rich, tropical flavor and numerous health benefits. This oil is perfect for cooking, adding a unique taste to your dishes, or as a natural remedy for various ailments. Whether you’re looking to enhance your culinary creations or support your overall wellness, our Edible Virgin Coconut Oil delivers exceptional quality and benefits in every drop.',

            'is_active' => true,
            'is_trending' => true,
            'is_featured' => true,

            'nutritional_info' => [
                'calories' => '120',
                'fat' => '14g',
                'saturated_fat' => '12g',
                'cholesterol' => '0mg',
                'vitamin_e' => '2%',
                'minerals' => 'Trace amounts of calcium, iron, magnesium, potassium',
            ],
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

            'short_description' => 'Premium quality mustard oil with a rich, pungent flavor and numerous health benefits, perfect for cooking or as a natural remedy.',
            'description' => '<span class="text-black font-sans font-semibold">Mustard Oil (খাঁটি সরিষার তেল)</span> — Our Mustard Oil is carefully extracted from the finest quality mustard seeds, known for their rich, pungent flavor and numerous health benefits. This oil is perfect for cooking, adding a unique taste to your dishes, or as a natural remedy for various ailments. Whether you’re looking to enhance your culinary creations or support your overall wellness, our Mustard Oil delivers exceptional quality and benefits in every drop.',

            'is_active' => true,

            'nutritional_info' => [
                'calories' => '120',
                'fat' => '14g',
                'saturated_fat' => '2g',
                'cholesterol' => '0mg',
                'vitamin_e' => '2%',
                'minerals' => 'Trace amounts of calcium, iron, magnesium, potassium',
            ],
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

            'short_description' => 'Premium quality goler gurr with a rich, caramel-like flavor and numerous health benefits, perfect for sweetening your dishes naturally.',
            'description' => '<span class="text-black font-sans font-semibold">Goler Gurr (গোলের গুড়)</span> — Our Goler Gurr is carefully crafted from the finest quality sugarcane, known for its rich, caramel-like flavor and numerous health benefits. This natural sweetener is perfect for adding a unique taste to your dishes while providing essential nutrients. Whether you’re looking to enhance your culinary creations or support your overall wellness, our Goler Gurr delivers exceptional quality and benefits in every bite.',

            'is_active' => true,

            'nutritional_info' => [
                'calories' => '290',
                'carbohydrates' => '75g',
                'protein' => '0g',
                'fiber' => '0g',
                'vitamins' => 'Trace amounts of B vitamins',
                'minerals' => 'Trace amounts of calcium, iron, magnesium, potassium',
            ],
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

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            HeroBannerSeeder::class,
            ComboSeeder::class,
            ShippingZoneSeeder::class,
            CouponSeeder::class,
            LandingPageSeeder::class,
            WebhookSeeder::class,
        ]);
    }
}

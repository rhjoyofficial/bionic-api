<?php

namespace App\Providers;

use App\Domains\Category\Models\Category;
use App\Domains\Category\Observers\CategoryObserver;
use App\Domains\Landing\Models\LandingPage;
use App\Domains\Landing\Observers\LandingPageObserver;
use App\Domains\Product\Models\Combo;
use App\Domains\Product\Models\Product;
use App\Domains\Product\Models\ProductVariant;
use App\Domains\Product\Observers\ComboObserver;
use App\Domains\Product\Observers\ProductObserver;
use App\Domains\Product\Observers\ProductVariantObserver;
use App\Domains\Shipping\Models\ShippingZone;
use App\Domains\Shipping\Observers\ShippingZoneObserver;
use App\Domains\Store\Models\HeroBanner;
use App\Domains\Store\Observers\HeroBannerObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Product::observe(ProductObserver::class);
        ProductVariant::observe(ProductVariantObserver::class);
        Category::observe(CategoryObserver::class);
        Combo::observe(ComboObserver::class);
        LandingPage::observe(LandingPageObserver::class);
        HeroBanner::observe(HeroBannerObserver::class);
        ShippingZone::observe(ShippingZoneObserver::class);
    }
}

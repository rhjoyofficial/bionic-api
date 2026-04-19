<?php

namespace App\Domains\Store\Controllers;

use App\Domains\Category\Models\Category;
use App\Domains\Product\Models\Combo;
use App\Domains\Product\Models\Product;
use App\Domains\Product\Resources\ProductResource;
use App\Domains\Store\Models\HeroBanner;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $heroBanners = Cache::remember('home:hero_banners', now()->addHours(24), fn () =>
            HeroBanner::active()->ordered()->get()
        );

        $categories = Cache::remember('categories:active', now()->addHours(24), fn () =>
            Category::active()->ordered()->get()
        );

        $trendingProductsRaw = Cache::remember('home:trending_products', now()->addHours(6), fn () =>
            Product::query()->active()->trending()->with(['variants.tierPrices'])->limit(12)->get()
        );

        $categoryProductsRaw = Cache::remember('home:category_products', now()->addHours(6), fn () =>
            Product::active()->with(['variants.tierPrices', 'category'])->latest()->limit(20)->get()
        );

        $combos = Cache::remember('home:combos', now()->addHours(6), fn () =>
            Combo::where('is_active', true)->with(['items.variant.product'])->latest()->limit(12)->get()
        );

        $trendingProducts = ProductResource::collection($trendingProductsRaw);
        $categoryProducts = ProductResource::collection($categoryProductsRaw);

        return view('store.pages.home', compact(
            'heroBanners',
            'categories',
            'trendingProducts',
            'categoryProducts',
            'combos'
        ));
    }
}

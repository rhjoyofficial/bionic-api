<?php

namespace App\Domains\Store\Controllers;

use App\Domains\Category\Models\Category;
use App\Domains\Product\Models\Product;
use App\Domains\Store\Models\HeroBanner;
use App\Http\Controllers\Controller;
use App\Domains\Product\Models\Combo;
use App\Domains\Product\Resources\ProductResource;

class HomeController extends Controller
{
    public function index()
    {
        $heroBanners = HeroBanner::active()->ordered()->get();
        $categories = Category::active()->ordered()->get();

        // 1. Trending Products
        $trendingProductsRaw = Product::query()->active()->trending()->with(['variants.tierPrices'])->limit(12)->get();

        $trendingProducts = ProductResource::collection($trendingProductsRaw);

        // 2. Category Products 
        $categoryProductsRaw = Product::active()->with(['variants.tierPrices', 'category'])->latest()->get();

        $categoryProducts = ProductResource::collection($categoryProductsRaw);

        // 3. Combo Products 
        $combos = Combo::where('is_active', true)->with(['items.variant.product'])->latest()->limit(12)->get();

        return view('store.pages.home', compact(
            'heroBanners',
            'categories',
            'trendingProducts',
            'categoryProducts',
            'combos'
        ));
    }
}

<?php

namespace App\Domains\Store\Controllers;

use App\Domains\Category\Models\Category;
use App\Domains\Product\Models\Product;
use App\Domains\Store\Models\HeroBanner;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $heroBanners = HeroBanner::active()->ordered()->get();

        $categories = Category::active()->ordered()->get();

        $categoryIds = $categories->pluck('id');

        $trendingProducts = Product::query()
            ->with(['variants.tierPrices'])
            ->active()
            ->trending()
            ->limit(12)
            ->get();

        $categoryProducts = Product::query()
            ->with(['variants'])
            ->whereIn('category_id', $categoryIds)
            ->active()
            ->latest()
            ->get()
            ->groupBy('category_id')
            ->map(fn($items) => $items->take(8));
        // dd($categoryProducts);

        return view('store.home', compact(
            'heroBanners',
            'categories',
            'trendingProducts',
            'categoryProducts'
        ));
    }
}

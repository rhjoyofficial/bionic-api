<?php

namespace App\Domains\Store\Controllers;

use App\Domains\Category\Models\Category;
use App\Domains\Product\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()
            ->active()
            ->with(['variants.tierPrices', 'category']);

        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $query->where(function ($search) use ($term) {
                $search->where('name', 'like', $term)
                    ->orWhere('short_description', 'like', $term);
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($categoryQuery) use ($request) {
                $categoryQuery->where('slug', $request->query('category'))->active();
            });
        }

        $products = $query->latest()->paginate(12)->withQueryString();
        $selectedCategory = null;

        if ($request->filled('category')) {
            $selectedCategory = Category::query()->active()->where('slug', $request->query('category'))->first();
        }

        return view('store.pages.products', [
            'products' => $products,
            'selectedCategory' => $selectedCategory,
            'searchQuery' => $request->query('q', ''),
        ]);
    }

    public function category(string $slug): View
    {
        $selectedCategory = Category::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        $products = Product::query()
            ->active()
            ->with(['variants.tierPrices', 'category'])
            ->where('category_id', $selectedCategory->id)
            ->latest()
            ->paginate(12);

        return view('store.pages.products', [
            'products' => $products,
            'selectedCategory' => $selectedCategory,
            'searchQuery' => '',
        ]);
    }
}

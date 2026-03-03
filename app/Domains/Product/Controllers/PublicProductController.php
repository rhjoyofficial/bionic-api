<?php

namespace App\Domains\Product\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Product\Models\Product;
use App\Domains\Product\Resources\ProductResource;

class PublicProductController extends Controller
{
    public function index()
    {
        return ProductResource::collection(
            Product::where('is_active', true)
                ->with('variants')
                ->get()
        );
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->with('variants')
            ->firstOrFail();

        return new ProductResource($product);
    }
}

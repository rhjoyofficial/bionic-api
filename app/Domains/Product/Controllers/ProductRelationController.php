<?php

namespace App\Domains\Product\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Product\Services\ProductRelationService;
use Illuminate\Http\Request;

class ProductRelationController extends Controller
{
    public function __construct(
        private ProductRelationService $service
    ) {}

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'related_product_id' => 'required|exists:products,id',
            'type' => 'required|in:upsell,cross_sell'
        ]);

        return $this->service->addRelation(
            $request->product_id,
            $request->related_product_id,
            $request->type
        );
    }

    public function destroy(Request $request)
    {
        $this->service->removeRelation(
            $request->product_id,
            $request->related_product_id
        );

        return response()->json([
            'message' => 'Relation removed'
        ]);
    }
}

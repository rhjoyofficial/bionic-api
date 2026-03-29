<?php

namespace App\Domains\Product\Services;

use App\Domains\Product\Models\ProductRelation;

class ProductRelationService
{
    public function addRelation($productId, $relatedId, $type)
    {
        return ProductRelation::create([
            'product_id' => $productId,
            'related_product_id' => $relatedId,
            'relation_type' => $type
        ]);
    }

    public function removeRelation($productId, $relatedId)
    {
        ProductRelation::where('product_id', $productId)
            ->where('related_product_id', $relatedId)
            ->delete();
    }
}

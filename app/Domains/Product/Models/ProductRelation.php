<?php

namespace App\Domains\Product\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRelation extends Model
{
    protected $fillable = [
        'product_id',
        'related_product_id',
        'relation_type', // cross_sell, upsell, downsell
        'discount_percentage'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function relatedProduct()
    {
        return $this->belongsTo(Product::class, 'related_product_id');
    }
}

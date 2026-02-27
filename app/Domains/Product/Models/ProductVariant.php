<?php

namespace App\Domains\Product\Models\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'title',
        'sku',
        'price',
        'stock',
        'weight_grams',
        'is_active'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function tierPrices()
    {
        return $this->hasMany(ProductTierPrice::class, 'variant_id');
    }
}

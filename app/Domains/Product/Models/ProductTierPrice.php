<?php

namespace App\Domains\Product\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTierPrice extends Model
{
    protected $fillable = [
        'variant_id',
        'min_quantity',
        'discount_type',
        'discount_value'
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}

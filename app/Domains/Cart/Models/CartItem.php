<?php

namespace App\Domains\Cart\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Product\Models\ProductVariant;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'variant_id',
        'quantity'
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}

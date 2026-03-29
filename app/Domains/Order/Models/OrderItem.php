<?php

namespace App\Domains\Order\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'sku_snapshot',
        'product_name_snapshot',
        'variant_title_snapshot',
        'original_unit_price',
        'discount_type_snapshot',
        'discount_value_snapshot',
        'quantity',
        'unit_price',
        'total_price'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function variant()
    {
        return $this->belongsTo(\App\Domains\Product\Models\ProductVariant::class, 'variant_id');
    }
}

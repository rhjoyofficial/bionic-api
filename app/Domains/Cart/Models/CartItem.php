<?php

namespace App\Domains\Cart\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Product\Models\ProductVariant;
use App\Domains\Product\Models\Combo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'combo_id',
        'variant_id',
        'quantity',
        'unit_price_snapshot',
        'product_name_snapshot',
        'variant_title_snapshot'
    ];

    public function getSubtotalAttribute(): float
    {
        return $this->unit_price_snapshot * $this->quantity;
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function combo(): BelongsTo
    {
        return $this->belongsTo(Combo::class, 'combo_id');
    }
}

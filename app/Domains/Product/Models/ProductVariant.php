<?php

namespace App\Domains\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'title',
        'sku',
        'price',
        'stock',          // Physical items in the warehouse
        'reserved_stock', // Items currently in pending orders/carts
        'weight_grams',
        'is_active'
    ];

    /**
     * Virtual attribute: available_stock
     * Usage: $variant->available_stock
     */
    protected function availableStock(): Attribute
    {
        return Attribute::get(fn () => max(0, $this->stock - $this->reserved_stock));
    }

    /**
     * Helper to check if a specific quantity is actually available
     */
    public function hasStock(int $quantity): bool
    {
        return $this->available_stock >= $quantity;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function tierPrices(): HasMany
    {
        return $this->hasMany(ProductTierPrice::class, 'variant_id')
            ->orderByDesc('min_quantity');
    }
}
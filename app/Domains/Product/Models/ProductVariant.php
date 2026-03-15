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
        'discount_type',
        'discount_value',
        'sale_ends_at',
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
        return Attribute::get(fn() => max(0, (int)$this->stock - (int)$this->reserved_stock));
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

    public function getFinalPriceAttribute(): float
    {
        if (
            $this->discount_type &&
            $this->discount_value &&
            (!$this->sale_ends_at || now()->lt($this->sale_ends_at))
        ) {
            if ($this->discount_type === 'percentage') {
                return round($this->price - ($this->price * $this->discount_value / 100), 2);
            }

            return max(0, $this->price - $this->discount_value);
        }

        return $this->price;
    }

    public function getDiscountPercentAttribute(): ?int
    {
        if (!$this->discount_type || !$this->discount_value) return null;

        if ($this->discount_type === 'percentage') {
            return (int)$this->discount_value;
        }

        return (int)round(($this->discount_value / $this->price) * 100);
    }

    public function toFrontend(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'price' => (float)$this->price,
            'final_price' => (float)$this->final_price,
            'discount_percent' => $this->discount_percent,
            'available_stock' => $this->available_stock,
            'tiers' => $this->tierPrices->map(fn($t) => [
                'qty' => $t->min_quantity,
                'type' => $t->discount_type,
                'value' => $t->discount_value
            ])->values()
        ];
    }
}

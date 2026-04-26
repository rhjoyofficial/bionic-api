<?php

namespace App\Domains\Product\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Combo extends Model
{
    protected $fillable = ['title', 'slug', 'description', 'image', 'pricing_mode', 'manual_price', 'discount_type', 'discount_value', 'is_active', 'is_featured', 'is_landing_enabled', 'landing_slug'];

    public function items()
    {
        return $this->hasMany(ComboItem::class);
    }

    public function getAutoPriceAttribute()
    {
        return $this->items->sum(function ($item) {
            return ($item->variant->final_price ?? 0) * $item->quantity;
        });
    }

    public function getFinalPriceAttribute(): float
    {
        $basePrice = ($this->pricing_mode === 'manual')
            ? ($this->manual_price ?? 0)
            : $this->auto_price;

        if (!$this->discount_type || !$this->discount_value) {
            return $basePrice;
        }

        if ($this->discount_type === 'percentage') {
            return round($basePrice - ($basePrice * ($this->discount_value / 100)), 2);
        }

        return max(0, $basePrice - $this->discount_value);
    }

    public function getAvailableStockAttribute(): int
    {
        if ($this->items->isEmpty()) {
            return 0;
        }

        return $this->items->min(function ($item) {
            if ($item->quantity <= 0) {
                return 0;
            }

            return (int) floor($item->variant->available_stock / $item->quantity);
        });
    }

    public function isInStock(): bool
    {
        return $this->available_stock > 0;
    }
    public function isOutOfStock(): bool
    {
        return !$this->isInStock();
    }

    public function isLowStock(): bool
    {
        return $this->available_stock > 0 && $this->available_stock <= 5;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    /**
     * The amount saved vs. buying each item individually at full variant price.
     * Used by combo-card and combo detail page to display a savings badge.
     */
    public function getTotalSavingsAttribute(): float
    {
        return max(0, $this->auto_price - $this->final_price);
    }

    /**
     * Scope to retrieve only active combos.
     * Mirrors Product::scopeActive() for consistent querying patterns.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

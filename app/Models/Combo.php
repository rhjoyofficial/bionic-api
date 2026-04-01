<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Product\Models\ProductVariant;

class Combo extends Model
{
    protected $fillable = ['title', 'slug', 'description', 'image', 'pricing_mode', 'manual_price', 'discount_type', 'discount_value', 'is_active', 'is_featured'];

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

    public function getAvailableStockAttribute()
    {
        if ($this->items->isEmpty()) return 0;

        return $this->items->map(function ($item) {
            return floor($item->variant->available_stock / $item->quantity);
        })->min();
    }
}

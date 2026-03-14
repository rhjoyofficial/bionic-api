<?php

namespace App\Domains\Product\Services;

use App\Domains\Product\Models\ProductVariant;
use Illuminate\Support\Collection;

class PricingService
{
    public function calculate(
        ProductVariant $variant,
        int $quantity,
        ?Collection $tiers = null
    ): array {

        $basePrice = $variant->price;
        $total = $basePrice * $quantity;

        $tiers = $tiers ?? $variant->tierPrices;

        $tier = $tiers
            ->where('min_quantity', '<=', $quantity)
            ->sortByDesc('min_quantity')
            ->first();

        if (! $tier) {
            return [
                'unit_price' => $basePrice,
                'discount' => 0,
                'total' => $total
            ];
        }

        if ($tier->discount_type === 'percentage') {
            $discount = ($basePrice * $tier->discount_value / 100) * $quantity;
        } else {
            $discount = $tier->discount_value * $quantity;
        }

        return [
            'unit_price' => $basePrice,
            'discount' => $discount,
            'total' => $total - $discount
        ];
    }
}

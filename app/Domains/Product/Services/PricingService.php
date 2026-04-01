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
            ?->where('min_quantity', '<=', $quantity)
            ->sortByDesc('min_quantity')
            ->first();

        if (! $tier) {
            return [
                'original_unit_price' => $basePrice,
                'unit_price'          => $basePrice, // No discount, so same as original
                'discount_amount'     => 0,
                'total'               => $total,
                'discount_type'       => 'none',
                'discount_value'      => 0,
            ];
        }

        if ($tier->discount_type === 'percentage') {
            $discountAmount = ($basePrice * $tier->discount_value / 100) * $quantity;
        } else {
            $discountAmount = $tier->discount_value * $quantity;
        }

        $finalTotal = max(0, $total - $discountAmount);

        return [
            'original_unit_price' => $basePrice,
            'unit_price'          => $finalTotal / $quantity, // Discounted unit price
            'discount_amount'     => min($discountAmount, $total),
            'total'               => $finalTotal,
            'discount_type'       => $tier->discount_type, // e.g., 'percentage'
            'discount_value'      => $tier->discount_value, // e.g., 10.00
        ];
    }
}

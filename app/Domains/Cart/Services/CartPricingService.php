<?php

namespace App\Domains\Cart\Services;

use App\Domains\Product\Services\PricingService;

class CartPricingService
{
    public function __construct(
        private PricingService $pricingService
    ) {}

    public function calculate($cart)
    {
        $subtotal = 0;
        $discount = 0;

        foreach ($cart->items as $item) {
            if ($item->combo_id) {
                $subtotal += $item->unit_price_snapshot * $item->quantity;
            } else {
                $pricing = $this->pricingService->calculate(
                    $item->variant,
                    $item->quantity
                );
                $price = $pricing['unit_price'] ?? $item->unit_price_snapshot;
                $subtotal += $price * $item->quantity;
                $discount += $pricing['discount'] ?? 0;
            }
        }

        return [
            'total_qty' => $cart->items->sum('quantity'),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $subtotal - $discount
        ];
    }
}

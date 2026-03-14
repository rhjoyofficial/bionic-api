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

            $basePrice = $item->unit_price_snapshot ?? $item->variant->price;

            $pricing = $this->pricingService->calculate(
                $item->variant,
                $item->quantity
            );

            $subtotal += $basePrice * $item->quantity;
            $discount += $pricing['discount'];
        }

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $subtotal - $discount
        ];
    }
}

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

            $variant = $item->variant;

            $pricing = $this->pricingService->calculate(
                $variant,
                $item->quantity
            );

            $subtotal += $variant->price * $item->quantity;
            $discount += $pricing['discount'];
        }

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $subtotal - $discount
        ];
    }
}

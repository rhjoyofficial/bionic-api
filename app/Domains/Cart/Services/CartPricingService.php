<?php

namespace App\Domains\Cart\Services;

class CartPricingService
{
    public function calculate($cart)
    {
        $subtotal = 0;

        foreach ($cart->items as $item) {
            $subtotal += $item->unit_price_snapshot * $item->quantity;
        }

        return [
            'total_qty' => $cart->items->sum('quantity'),
            'subtotal' => $subtotal,
            'discount' => 0,
            'total' => $subtotal
        ];
    }
}

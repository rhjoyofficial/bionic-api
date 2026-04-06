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
        $cart->load(['items.variant.tierPrices', 'items.combo']);

        foreach ($cart->items as $item) {
            $currentUnitPrice = $item->unit_price_snapshot;

            if ($item->combo_id && $item->combo) {
                $currentUnitPrice = $item->combo->final_price;
            } elseif ($item->variant) {
                $pricing = $this->pricingService->calculate($item->variant, $item->quantity);
                $currentUnitPrice = $pricing['unit_price'];
            }

            $subtotal += $currentUnitPrice * $item->quantity;
        }

        return [
            'total_qty' => $cart->items->sum('quantity'),
            'subtotal'  => $subtotal,
            'discount'  => 0,
            'total'     => $subtotal
        ];
    }
}

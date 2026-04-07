<?php

namespace App\Domains\Cart\Services;

use App\Domains\Order\Services\CheckoutPricingService;

class CartPricingService
{
    public function __construct(
        private CheckoutPricingService $checkoutPricing
    ) {}

    public function calculate($cart)
    {
        $cart->load(['items.variant.tierPrices', 'items.combo']);

        $items = $cart->items->map(function ($item) {
            if ($item->combo_id) {
                return ['combo_id' => $item->combo_id, 'quantity' => $item->quantity];
            }
            return ['variant_id' => $item->variant_id, 'quantity' => $item->quantity];
        })->values()->toArray();

        if (empty($items)) {
            return [
                'total_qty' => 0,
                'subtotal'  => 0,
                'discount'  => 0,
                'total'     => 0,
            ];
        }

        $result = $this->checkoutPricing->calculate(
            items: $items,
            withLock: false,
        );

        $afterDiscount = $result->subtotal - $result->tierDiscountTotal;

        return [
            'total_qty' => $cart->items->sum('quantity'),
            'subtotal'  => round($afterDiscount, 2),
            'discount'  => round($result->tierDiscountTotal, 2),
            'total'     => round($afterDiscount, 2),
        ];
    }
}

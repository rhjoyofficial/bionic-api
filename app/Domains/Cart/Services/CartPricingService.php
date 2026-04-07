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

        $items = $cart->items->map(fn($item) => [
            'variant_id' => $item->variant_id,
            'combo_id'   => $item->combo_id,
            'quantity'   => $item->quantity,
        ])->toArray();

        if (empty($items)) {
            return [
                'total_qty' => 0,
                'subtotal'  => 0,
                'discount'  => 0,
                'total'     => 0,
            ];
        }

        // Use the single pricing engine — no coupon, no zone for cart display
        $result = $this->checkoutPricing->calculate(
            items: $items,
            couponCode: null,
            zoneId: null,
            user: null,
            withLock: false,
        );

        return [
            'total_qty' => $cart->items->sum('quantity'),
            'subtotal'  => $result->subtotal,
            'discount'  => $result->tierDiscountTotal,
            'total'     => $result->subtotal - $result->tierDiscountTotal,
        ];
    }
}

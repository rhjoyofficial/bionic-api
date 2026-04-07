<?php

namespace App\Domains\Order\DTOs;

use App\Domains\Coupon\Models\Coupon;
use Illuminate\Database\Eloquent\Collection;

class CheckoutPricingResult
{
    public function __construct(
        public readonly array $lineItems,
        public readonly float $subtotal,
        public readonly float $tierDiscountTotal,
        public readonly float $couponDiscount,
        public readonly ?Coupon $coupon,
        public readonly float $shippingCost,
        public readonly float $grandTotal,
        public readonly Collection $lockedVariants,
    ) {}

    /**
     * Convert to array for API responses (checkout/preview).
     */
    public function toArray(): array
    {
        return [
            'line_items'       => $this->lineItems,
            'subtotal'         => round($this->subtotal, 2),
            'tier_discount'    => round($this->tierDiscountTotal, 2),
            'coupon_discount'  => round($this->couponDiscount, 2),
            'coupon_code'      => $this->coupon?->code,
            'shipping_cost'    => round($this->shippingCost, 2),
            'grand_total'      => round($this->grandTotal, 2),
        ];
    }
}

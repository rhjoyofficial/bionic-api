<?php

namespace App\Domains\Coupon\Services;

use App\Domains\Coupon\Models\Coupon;
use App\Models\User;
use Exception;

class CouponValidationService
{
    public function validate(
        string $code,
        float $orderAmount,
        ?User $user = null
    ): array {

        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            throw new Exception('Invalid coupon code');
        }

        if (! $coupon->isValidForUser($user)) {
            throw new Exception('Coupon expired, inactive or usage limit reached');
        }

        if ($coupon->min_purchase && $orderAmount < $coupon->min_purchase) {
            throw new Exception('Minimum purchase not met');
        }

        return [
            'coupon' => $coupon,
            'discount' => $this->calculateDiscount($coupon, $orderAmount)
        ];
    }

    private function calculateDiscount(Coupon $coupon, float $amount): float
    {
        if ($coupon->type === 'percentage') {
            return ($amount * $coupon->value) / 100;
        }

        return $coupon->value;
    }
}

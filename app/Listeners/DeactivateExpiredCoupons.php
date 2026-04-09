<?php

namespace App\Listeners;

use App\Events\CouponExpired;
use Illuminate\Support\Facades\Log;

class DeactivateExpiredCoupons
{
    public function handle(CouponExpired $event): void
    {
        $coupon = $event->coupon;

        if ($coupon->is_active) {
            $coupon->update(['is_active' => false]);

            Log::info("Coupon deactivated (expired): [{$coupon->code}] id={$coupon->id}");
        }
    }
}

<?php

namespace App\Listeners;

use App\Events\CouponExpired;
use App\Jobs\SendWebhookJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class DispatchCouponExpiredWebhook implements ShouldQueue
{
    use InteractsWithQueue;

    public int   $tries  = 3;
    public array $backoff = [30, 120, 300];

    public function handle(CouponExpired $event): void
    {
        $coupon = $event->coupon;

        SendWebhookJob::dispatch('coupon.expired', [
            'code'           => $coupon->code,
            'discount_type'  => $coupon->discount_type,
            'discount_value' => (float) $coupon->discount_value,
            'usage_count'    => $coupon->usage_count ?? 0,
            'expired_at'     => $coupon->expires_at?->toDateString(),
            'expired_ts'     => now()->toISOString(),
        ]);
    }

    public function failed(Throwable $e): void
    {
        Log::error('DispatchCouponExpiredWebhook failed: ' . $e->getMessage());
    }
}

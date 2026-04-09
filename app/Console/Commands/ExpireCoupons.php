<?php

namespace App\Console\Commands;

use App\Domains\Coupon\Models\Coupon;
use App\Events\CouponExpired;
use Illuminate\Console\Command;

class ExpireCoupons extends Command
{
    protected $signature   = 'coupons:expire';
    protected $description = 'Deactivate coupons whose end_date has passed';

    public function handle(): int
    {
        $expired = Coupon::where('is_active', true)
            ->whereNotNull('end_date')
            ->where('end_date', '<', now())
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No expired coupons found.');
            return self::SUCCESS;
        }

        foreach ($expired as $coupon) {
            CouponExpired::dispatch($coupon);
        }

        $this->info("Dispatched CouponExpired for {$expired->count()} coupon(s).");

        return self::SUCCESS;
    }
}

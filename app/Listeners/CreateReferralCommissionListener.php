<?php

namespace App\Listeners;

use App\Domains\Order\Models\Commission;
use App\Events\OrderCreated;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class CreateReferralCommissionListener implements ShouldQueue
{
    /**
     * Default commission rate: 5% of grand total.
     * Move to config or a dedicated tiers table when business requires it.
     */
    private const COMMISSION_RATE = 0.05;

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $buyer = $order->user;

        // Guest orders or users without a referrer — nothing to do
        if (!$buyer || !$buyer->referred_by) return;

        $referrer = User::find($buyer->referred_by);
        if (!$referrer) return;

        // Prevent duplicate commissions for the same order
        if (Commission::where('order_id', $order->id)->exists()) return;

        $commissionAmount = round($order->grand_total * self::COMMISSION_RATE, 2);

        if ($commissionAmount <= 0) return;

        Commission::create([
            'order_id'          => $order->id,
            'referrer_id'       => $referrer->id,
            'commission_amount' => $commissionAmount,
            'status'            => 'pending',
        ]);

        Log::info("Referral commission created", [
            'order_id'    => $order->id,
            'referrer_id' => $referrer->id,
            'amount'      => $commissionAmount,
        ]);
    }
}

<?php

namespace App\Console\Commands;

use App\Domains\Cart\Models\Cart;
use App\Domains\Cart\Services\CartService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AbandonExpiredCarts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:abandon-expired-carts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Cart::where('status', 'active')
            ->whereNull('locked_at')
            ->where('expires_at', '<', now())
            ->chunkById(50, function ($carts) {

                foreach ($carts as $cart) {

                    DB::transaction(function () use ($cart) {

                        app(CartService::class)->releaseReservedStock($cart);

                        $cart->update([
                            'status' => 'abandoned'
                        ]);
                    });
                }
            });
    }
}

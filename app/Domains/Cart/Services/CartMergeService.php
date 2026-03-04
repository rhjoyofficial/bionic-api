<?php

namespace App\Domains\Cart\Services;

use App\Domains\Cart\Models\Cart;

class CartMergeService
{
    public function merge(string $sessionToken, int $userId)
    {
        $guestCart = Cart::where('session_token', $sessionToken)->first();

        if (!$guestCart) {
            return;
        }

        $userCart = Cart::firstOrCreate([
            'user_id' => $userId
        ]);

        foreach ($guestCart->items as $item) {

            $existing = $userCart->items()
                ->where('variant_id', $item->variant_id)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $item->quantity);
            } else {

                $userCart->items()->create([
                    'variant_id' => $item->variant_id,
                    'quantity' => $item->quantity
                ]);
            }
        }

        $guestCart->delete();
    }
}

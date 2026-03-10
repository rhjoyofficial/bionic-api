<?php

namespace App\Domains\Cart\Services;

use App\Domains\Cart\Models\Cart;

class CartMergeService
{
    public function merge(string $sessionToken, int $userId): void
    {
        $guestCart = Cart::query()
            ->where('session_token', $sessionToken)
            ->with('items')
            ->first();

        if (! $guestCart) {
            return;
        }

        $userCart = Cart::firstOrCreate([
            'user_id' => $userId,
        ]);

        $existingItems = $userCart->items()
            ->get()
            ->keyBy('variant_id');

        foreach ($guestCart->items as $item) {
            $existing = $existingItems->get($item->variant_id);

            if ($existing) {
                $existing->increment('quantity', $item->quantity);
                continue;
            }

            $created = $userCart->items()->create([
                'variant_id' => $item->variant_id,
                'quantity' => $item->quantity,
            ]);

            $existingItems->put($created->variant_id, $created);
        }

        $guestCart->delete();
    }
}

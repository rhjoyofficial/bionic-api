<?php

namespace App\Domains\Cart\Services;

use App\Domains\Cart\Models\Cart;

class CartService
{
    public function getCart(?int $userId, ?string $sessionToken)
    {
        if ($userId) {
            return Cart::firstOrCreate(['user_id' => $userId]);
        }

        return Cart::firstOrCreate([
            'session_token' => $sessionToken
        ]);
    }

    public function addItem($cart, $variantId, $qty)
    {
        $item = $cart->items()->where('variant_id', $variantId)->first();

        if ($item) {
            $item->increment('quantity', $qty);
            return $item;
        }

        return $cart->items()->create([
            'variant_id' => $variantId,
            'quantity' => $qty
        ]);
    }

    public function updateItem($cart, $variantId, $qty)
    {
        $item = $cart->items()->where('variant_id', $variantId)->firstOrFail();

        $item->update(['quantity' => $qty]);

        return $item;
    }

    public function removeItem($cart, $variantId)
    {
        $cart->items()->where('variant_id', $variantId)->delete();
    }

    public function clearCart($cart)
    {
        $cart->items()->delete();
    }
}

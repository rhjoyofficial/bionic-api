<?php

namespace App\Domains\Cart\Services;

use App\Domains\Cart\Models\Cart;
use App\Domains\Product\Models\ProductVariant;
use Exception;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function getCart(?int $userId, ?string $sessionToken): Cart
    {
        return Cart::firstOrCreate([
            'user_id' => $userId,
            'session_token' => $userId ? null : $sessionToken,
            'status' => 'active'
        ]);
    }

    public function addItem(Cart $cart, int $variantId, int $qty)
    {
        return DB::transaction(function () use ($cart, $variantId, $qty) {

            $variant = ProductVariant::lockForUpdate()->findOrFail($variantId);

            if (! $variant->hasStock($qty)) {
                throw new Exception("Only {$variant->available_stock} left");
            }

            $item = $cart->items()->where('variant_id', $variantId)->first();

            if ($item) {

                $newQty = $item->quantity + $qty;

                if (! $variant->hasStock($qty)) {
                    throw new Exception("Stock limit reached");
                }

                $item->increment('quantity', $qty);
                $variant->increment('reserved_stock', $qty);

                return $item;
            }

            $variant->increment('reserved_stock', $qty);

            return $cart->items()->create([
                'variant_id' => $variantId,
                'quantity' => $qty,
                'unit_price_snapshot' => $variant->final_price,
                'product_name_snapshot' => $variant->product->name,
                'variant_title_snapshot' => $variant->title,
            ]);
        });
    }

    public function updateItem(Cart $cart, int $variantId, int $qty)
    {
        return DB::transaction(function () use ($cart, $variantId, $qty) {

            $variant = ProductVariant::lockForUpdate()->findOrFail($variantId);

            $item = $cart->items()->where('variant_id', $variantId)->firstOrFail();

            $diff = $qty - $item->quantity;

            if ($diff > 0 && ! $variant->hasStock($diff)) {
                throw new Exception("Stock limit reached");
            }

            $item->update(['quantity' => $qty]);

            $variant->increment('reserved_stock', $diff);

            return $item;
        });
    }

    public function removeItem(Cart $cart, int $variantId)
    {
        return DB::transaction(function () use ($cart, $variantId) {

            $item = $cart->items()->where('variant_id', $variantId)->first();

            if (! $item) return;

            $variant = ProductVariant::lockForUpdate()->find($variantId);

            $variant?->decrement('reserved_stock', $item->quantity);

            $item->delete();
        });
    }

    public function clearCart(Cart $cart)
    {
        return DB::transaction(function () use ($cart) {

            foreach ($cart->items as $item) {

                $variant = ProductVariant::lockForUpdate()->find($item->variant_id);

                $variant?->decrement('reserved_stock', $item->quantity);
            }

            $cart->items()->delete();
        });
    }
}

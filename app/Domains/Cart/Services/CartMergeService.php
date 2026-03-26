<?php

namespace App\Domains\Cart\Services;

use App\Domains\Cart\Models\Cart;
use Exception;
use Illuminate\Support\Facades\DB;

class CartMergeService
{
    public function merge(string $sessionToken, int $userId): void
    {
        DB::transaction(function () use ($sessionToken, $userId) {
            $guestCart = Cart::query()->where('session_token', $sessionToken)->with('items')->first();

            if (!$guestCart) return;

            $userCart = Cart::firstOrCreate([
                'user_id' => $userId,
                'status' => 'active'
            ]);

            $existingItems = $userCart->items()->get()->keyBy('variant_id');

            foreach ($guestCart->items as $item) {

                $existing = $existingItems->get($item->variant_id);
                $newQuantity = $existing ? ($existing->quantity + $item->quantity) : $item->quantity;

                if ($item->variant->stock_quantity < $newQuantity) {
                    throw new Exception("Insufficient stock for product: {$item->product_name_snapshot}");
                }

                if ($existing) {
                    $existing->update(['quantity' => $newQuantity]);
                    continue;
                } else {
                    $created = $userCart->items()->create([
                        'variant_id'             => $item->variant_id,
                        'quantity'               => $item->quantity,
                        'unit_price_snapshot'    => $item->unit_price_snapshot,
                        'product_name_snapshot'  => $item->product_name_snapshot,
                        'variant_title_snapshot' => $item->variant_title_snapshot,
                    ]);

                    $existingItems->put($created->variant_id, $created);
                }
            }

            $guestCart->delete();
        });
    }
}

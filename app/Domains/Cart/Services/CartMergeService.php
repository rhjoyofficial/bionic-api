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

            // 1. Eager load both variant AND combo relationships to avoid N+1 queries during stock checks
            $guestCart = Cart::query()
                ->where('session_token', $sessionToken)
                ->with(['items.variant', 'items.combo.items.variant'])
                ->first();

            if (!$guestCart) return;

            $userCart = Cart::firstOrCreate([
                'user_id' => $userId,
                'status'  => 'active'
            ]);

            // 2. Create a dynamic identifier for cart items so Combos and Variants don't collide
            $resolveItemKey = fn($item) => $item->combo_id ? 'combo_' . $item->combo_id : 'variant_' . $item->variant_id;

            // 3. Map existing user items using the dynamic key
            $existingItems = $userCart->items()->get()->keyBy($resolveItemKey);

            foreach ($guestCart->items as $item) {

                $itemKey = $resolveItemKey($item);
                $existing = $existingItems->get($itemKey);

                $newQuantity = $existing ? ($existing->quantity + $item->quantity) : $item->quantity;

                // 4. Split stock validation based on item type
                if ($item->combo_id) {
                    $combo = $item->combo;

                    if (!$combo) {
                        throw new Exception("Invalid combo detected in cart.");
                    }

                    // Check stock for every piece of the bundle
                    foreach ($combo->items as $comboItem) {
                        if (!$comboItem->variant->hasStock($comboItem->quantity * $newQuantity)) {
                            throw new Exception("Insufficient stock for bundle component in: {$item->product_name_snapshot}");
                        }
                    }
                } else {
                    // Standard Variant check
                    if (!$item->variant || !$item->variant->hasStock($newQuantity)) {
                        throw new Exception("Insufficient stock for product: {$item->product_name_snapshot}");
                    }
                }

                // 5. Update or Create
                if ($existing) {
                    $existing->update(['quantity' => $newQuantity]);
                } else {
                    $created = $userCart->items()->create([
                        'variant_id'             => $item->variant_id,
                        'combo_id'               => $item->combo_id,
                        'quantity'               => $item->quantity,
                        'unit_price_snapshot'    => $item->unit_price_snapshot,
                        'product_name_snapshot'  => $item->product_name_snapshot,
                        'variant_title_snapshot' => $item->variant_title_snapshot,
                    ]);

                    $existingItems->put($itemKey, $created);
                }
            }

            // Clean up the old guest cart
            $guestCart->delete();
        });
    }
}

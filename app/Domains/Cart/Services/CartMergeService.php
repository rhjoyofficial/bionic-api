<?php

namespace App\Domains\Cart\Services;

use App\Domains\Cart\Models\Cart;
use App\Domains\Product\Services\PricingService;
use Exception;
use Illuminate\Support\Facades\DB;

class CartMergeService
{
    public function __construct(
        private CartService $cartService,
        private PricingService $pricingService,
    ) {}

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

            $this->cartService->releaseReservedStock($guestCart);
            $this->cartService->releaseReservedStock($userCart);

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

                // 5. Update or Create (refresh price snapshot on merge)
                if ($existing) {
                    $updateData = ['quantity' => $newQuantity];

                    // Refresh price snapshot with new quantity so tier pricing is accurate
                    if ($existing->variant_id && $item->variant) {
                        $pricing = $this->pricingService->calculate($item->variant, $newQuantity);
                        $updateData['unit_price_snapshot'] = $pricing['unit_price'];
                    } elseif ($existing->combo_id && $item->combo) {
                        $updateData['unit_price_snapshot'] = $item->combo->final_price;
                    }

                    $existing->update($updateData);
                } else {
                    $created = $userCart->items()->create([
                        'variant_id'             => $item->variant_id,
                        'combo_id'               => $item->combo_id,
                        'quantity'               => $item->quantity,
                        'unit_price_snapshot'    => $item->unit_price_snapshot,
                        'product_name_snapshot'  => $item->product_name_snapshot,
                        'variant_title_snapshot' => $item->variant_title_snapshot,
                        'combo_name_snapshot' => $item->combo_name_snapshot,
                    ]);

                    $existingItems->put($itemKey, $created);
                }
            }

            $userCart->load('items');
            $this->cartService->reserveStock($userCart);
            $this->cartService->syncCartPrices($userCart);
            $guestCart->delete();
        });
    }
}

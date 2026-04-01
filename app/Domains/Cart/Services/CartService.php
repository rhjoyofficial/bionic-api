<?php

namespace App\Domains\Cart\Services;

use App\Domains\Cart\Models\Cart;
use App\Domains\Product\Models\Combo;
use App\Domains\Product\Models\ProductVariant;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Domains\Product\Services\PricingService;

class CartService
{
    public function __construct(
        private PricingService $pricingService
    ) {}
    public function getCart(?int $userId, ?string $sessionToken): Cart
    {
        if ($userId) {
            return Cart::firstOrCreate(['user_id' => $userId, 'status' => 'active']);
        }

        return Cart::firstOrCreate(['session_token' => $sessionToken, 'user_id' => null, 'status' => 'active']);
    }

    public function addCombo(Cart $cart, int $comboId, int $qty = 1)
    {
        return DB::transaction(function () use ($cart, $comboId, $qty) {
            $combo = Combo::with(['items.variant' => function ($q) {
                $q->lockForUpdate();
            }])->findOrFail($comboId);

            if ($combo->available_stock < $qty) {
                throw new Exception("Insufficient stock for this bundle.");
            }

            $cartItem = $cart->items()->where('combo_id', $comboId)->first();

            if ($cartItem) {
                $cartItem->increment('quantity', $qty);
            } else {
                $cartItem = $cart->items()->create([
                    'combo_id' => $comboId,
                    'quantity' => $qty,
                    'unit_price_snapshot' => $combo->final_price,
                    'combo_name_snapshot' => $combo->title,
                ]);
            }

            foreach ($combo->items as $item) {
                $item->variant->increment('reserved_stock', $item->quantity * $qty);
            }

            return $cartItem;
        });
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

                if (!$variant->hasStock($newQty)) {
                    throw new \Exception("Stock limit reached. Only {$variant->available_stock} left.");
                }

                // $item->increment('quantity', $qty);
                $item->update([
                    'quantity' => $newQty,
                    'unit_price_snapshot' => $variant->final_price,
                    'product_name_snapshot' => $variant->product->name,
                    'variant_title_snapshot' => $variant->title,
                ]);

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

    public function removeItem(Cart $cart, int $itemId)
    {
        return DB::transaction(function () use ($cart, $itemId) {
            $item = $cart->items()->findOrFail($itemId);

            if ($item->combo_id) {
                foreach ($item->combo->items as $ci) {
                    $ci->variant->decrement('reserved_stock', $ci->quantity * $item->quantity);
                }
            } else {
                $item->variant->decrement('reserved_stock', $item->quantity);
            }

            $item->delete();
        });
    }

    public function clearCart(Cart $cart)
    {
        return DB::transaction(function () use ($cart) {
            foreach ($cart->items as $item) {
                if ($item->combo_id) {
                    $combo = Combo::with('items.variant')->find($item->combo_id);
                    if ($combo) {
                        foreach ($combo->items as $ci) {
                            $ci->variant->decrement('reserved_stock', $ci->quantity * $item->quantity);
                        }
                    }
                } else {
                    $variant = ProductVariant::lockForUpdate()->find($item->variant_id);
                    $variant?->decrement('reserved_stock', $item->quantity);
                }
            }
            $cart->items()->delete();
        });
    }

    public function updateItemQuantity(Cart $cart, int $cartItemId, int $newQty)
    {
        return DB::transaction(function () use ($cart, $cartItemId, $newQty) {
            $item = $cart->items()->findOrFail($cartItemId);
            $diff = $newQty - $item->quantity;

            if ($diff === 0) return $item;

            // 1. Handle Combo Items (Usually fixed price, but we refresh the snapshot anyway)
            if ($item->combo_id) {
                $combo = Combo::with('items.variant')->findOrFail($item->combo_id);

                if ($diff > 0 && $combo->available_stock < $diff) {
                    throw new Exception("Insufficient stock for bundle update.");
                }

                foreach ($combo->items as $ci) {
                    $ci->variant->increment('reserved_stock', $ci->quantity * $diff);
                }

                // Refresh snapshot from the combo's current final price
                $item->update([
                    'quantity' => $newQty,
                    'unit_price_snapshot' => $combo->final_price
                ]);

                // 2. Handle Regular Variants (The Tier Pricing Logic)
            } else {
                $variant = \App\Domains\Product\Models\ProductVariant::lockForUpdate()
                    ->findOrFail($item->variant_id);

                if ($diff > 0 && !$variant->hasStock($diff)) {
                    throw new Exception("Insufficient stock.");
                }

                // RECALCULATE PRICING BASED ON NEW QUANTITY
                $pricing = $this->pricingService->calculate($variant, $newQty);

                $variant->increment('reserved_stock', $diff);

                // Update both quantity AND the new unit price snapshot
                $item->update([
                    'quantity' => $newQty,
                    'unit_price_snapshot' => $pricing['unit_price'] // This captures the tier discount!
                ]);
            }

            return $item;
        });
    }

    public function releaseReservedStock(Cart $cart)
    {
        foreach ($cart->items as $item) {
            if ($item->combo_id) {
                $combo = Combo::with('items.variant')->find($item->combo_id);
                if ($combo) {
                    foreach ($combo->items as $ci) {
                        $ci->variant->decrement('reserved_stock', $ci->quantity * $item->quantity);
                    }
                }
            } elseif ($item->variant_id) {
                ProductVariant::where('id', $item->variant_id)
                    ->decrement('reserved_stock', $item->quantity);
            }
        }
    }

    public function syncCartPrices(Cart $cart): bool
    {
        $anyPriceChanged = false;

        DB::transaction(function () use ($cart, &$anyPriceChanged) {
            $cart->load(['items.variant.tierPrices', 'items.combo']);

            foreach ($cart->items as $item) {
                $currentPrice = $item->unit_price_snapshot;
                $newPrice = $currentPrice;

                if ($item->combo_id && $item->combo) {
                    $newPrice = $item->combo->final_price;
                } elseif ($item->variant) {
                    $pricing = $this->pricingService->calculate($item->variant, $item->quantity);
                    $newPrice = $pricing['unit_price'];
                }

                // Check if the price shifted
                if ((float)$currentPrice !== (float)$newPrice) {
                    $item->update(['unit_price_snapshot' => $newPrice]);
                    $anyPriceChanged = true;
                }
            }
        });

        return $anyPriceChanged;
    }
}

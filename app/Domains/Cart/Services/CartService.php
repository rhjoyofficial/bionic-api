<?php

namespace App\Domains\Cart\Services;

use App\Domains\Cart\Models\Cart;
use App\Domains\Cart\Resources\CartItemResource;
use App\Domains\Cart\Services\CartPricingService;
use App\Domains\Product\Models\Combo;
use App\Domains\Product\Models\ProductVariant;
use App\Domains\Product\Services\PricingService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function __construct(
        private PricingService $pricingService,
        private CartPricingService $cartPricingService
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
            $combo = Combo::with('items')->findOrFail($comboId);

            $variants = ProductVariant::whereIn('id', $combo->items->pluck('product_variant_id'))
                ->lockForUpdate()->get()->keyBy('id');

            foreach ($combo->items as $comboItem) {
                $variant = $variants->get($comboItem->product_variant_id);
                $neededForThisAdd = $comboItem->quantity * $qty;
                if (!$variant || !$variant->hasStock($neededForThisAdd)) {
                    throw new Exception("Insufficient stock for component in bundle: {$combo->title}");
                }
            }

            $cartItem = $cart->items()->where('combo_id', $comboId)->first();

            if ($cartItem) {
                $cartItem->update([
                    'quantity' => $cartItem->quantity + $qty,
                    'unit_price_snapshot' => $combo->final_price,
                ]);
            } else {
                $cartItem = $cart->items()->create([
                    'combo_id' => $comboId,
                    'quantity' => $qty,
                    'unit_price_snapshot' => $combo->final_price,
                    'combo_name_snapshot' => $combo->title,
                ]);
            }

            foreach ($combo->items as $comboItem) {
                $variants->get($comboItem->product_variant_id)
                    ->increment('reserved_stock', $comboItem->quantity * $qty);
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

                if (!$variant->hasStock($qty)) {
                    throw new \Exception("Stock limit reached. Only {$variant->available_stock} more available.");
                }

                $pricing = $this->pricingService->calculate($variant, $newQty);

                $item->update([
                    'quantity' => $newQty,
                    'unit_price_snapshot' => $pricing['unit_price'],
                    'product_name_snapshot' => $variant->product->name,
                    'variant_title_snapshot' => $variant->title,
                ]);

                $variant->increment('reserved_stock', $qty);
                return $item;
            }

            $variant->increment('reserved_stock', $qty);

            $pricing = $this->pricingService->calculate($variant, $qty);
            return $cart->items()->create([
                'variant_id' => $variantId,
                'quantity' => $qty,
                'unit_price_snapshot' => $pricing['unit_price'],
                'product_name_snapshot' => $variant->product->name,
                'variant_title_snapshot' => $variant->title,
            ]);
        });
    }

    public function updateItem(Cart $cart, int $variantId, int $qty)
    {
        return DB::transaction(function () use ($cart, $variantId, $qty) {
            $item = $cart->items()
                ->where('variant_id', $variantId)
                ->firstOrFail();

            return $this->updateItemQuantity($cart, $item->id, $qty);
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
            $cart->load('items.combo.items');

            $variantIds = collect();
            foreach ($cart->items as $item) {
                if ($item->combo_id && $item->combo) {
                    $variantIds = $variantIds->merge($item->combo->items->pluck('variant_id'));
                } elseif ($item->variant_id) {
                    $variantIds->push($item->variant_id);
                }
            }

            $variants = ProductVariant::whereIn('id', $variantIds->unique())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($cart->items as $item) {
                if ($item->combo_id && $item->combo) {
                    foreach ($item->combo->items as $ci) {
                        $variants->get($ci->variant_id)
                            ?->decrement('reserved_stock', $ci->quantity * $item->quantity);
                    }
                } elseif ($item->variant_id) {
                    $variants->get($item->variant_id)
                        ?->decrement('reserved_stock', $item->quantity);
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
        DB::transaction(function () use ($cart) {
            $cart->load('items.combo.items');
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
        });
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
                if (abs((float)$currentPrice - (float)$newPrice) > 0.001) {
                    $item->update(['unit_price_snapshot' => $newPrice]);
                    $anyPriceChanged = true;
                }
            }
        });

        return $anyPriceChanged;
    }

    public function reserveStock(Cart $cart)
    {
        foreach ($cart->items as $item) {
            if ($item->combo_id) {
                $combo = Combo::with('items.variant')->find($item->combo_id);
                if ($combo) {
                    foreach ($combo->items as $ci) {
                        $ci->variant->increment('reserved_stock', $ci->quantity * $item->quantity);
                    }
                }
            } elseif ($item->variant_id) {
                ProductVariant::where('id', $item->variant_id)
                    ->increment('reserved_stock', $item->quantity);
            }
        }
    }

    public function formatCartDetails(Cart      $cart)
    {
        $cart->load(['items.variant.product', 'items.variant.tierPrices', 'items.combo']);
        return [
            'items' => CartItemResource::collection($cart->items),
            'totals' => $this->cartPricingService->calculate($cart),
            'cart_id' => $cart->id,
        ];
    }
}

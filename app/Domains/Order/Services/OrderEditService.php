<?php

namespace App\Domains\Order\Services;

use App\Domains\ActivityLog\Models\ActivityLog;
use App\Domains\Coupon\Models\Coupon;
use App\Domains\Order\DTOs\CheckoutPricingResult;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\OrderItem;
use App\Domains\Product\Models\Combo;
use App\Domains\Product\Models\ProductVariant;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderEditService
{
    public function __construct(
        private readonly CheckoutPricingService $pricingService,
    ) {}

    /**
     * Statuses that allow order editing.
     */
    public const EDITABLE_STATUSES = ['pending', 'confirmed'];

    /**
     * Check if an order can be edited.
     */
    public function isEditable(Order $order): bool
    {
        return in_array($order->order_status, self::EDITABLE_STATUSES);
    }

    /**
     * Get current order data prepared for the edit form.
     * Returns items with current variant stock info for validation.
     */
    public function getEditData(Order $order): array
    {
        if (!$this->isEditable($order)) {
            throw new Exception('Order cannot be edited. Status must be pending or confirmed.');
        }

        $order->load(['items', 'zone']);

        $items = $order->items->map(function (OrderItem $item) {
            $variant = $item->variant_id ? ProductVariant::with('product', 'tierPrices')->find($item->variant_id) : null;
            $combo   = $item->combo_id ? Combo::with('items.variant')->find($item->combo_id) : null;

            return [
                'id'                    => $item->id,
                'variant_id'            => $item->variant_id,
                'combo_id'              => $item->combo_id,
                'product_name_snapshot' => $item->product_name_snapshot,
                'variant_title_snapshot'=> $item->variant_title_snapshot,
                'sku_snapshot'          => $item->sku_snapshot,
                'quantity'              => $item->quantity,
                'unit_price'            => (float) $item->unit_price,
                'original_unit_price'   => (float) $item->original_unit_price,
                'total_price'           => (float) $item->total_price,
                // Live stock info for validation
                'available_stock'       => $variant?->available_stock,
                'current_reserved'      => $item->quantity, // This item's current reservation
                'max_quantity'          => $variant
                    ? $variant->available_stock + $item->quantity // Available + already reserved by this item
                    : ($combo ? $this->comboMaxQuantity($combo, $item->quantity) : 999),
                'is_active'             => $variant?->is_active ?? ($combo?->is_active ?? false),
            ];
        });

        return [
            'order_id'       => $order->id,
            'order_number'   => $order->order_number,
            'order_status'   => $order->order_status,
            'zone_id'        => $order->zone_id,
            'coupon_code'    => $order->coupon_code_snapshot,
            'payment_method' => $order->payment_method,
            'items'          => $items->values()->toArray(),
        ];
    }

    /**
     * Preview the result of editing without committing changes.
     * Uses the SAME pricing engine as checkout — no duplicated logic.
     *
     * @param  Order $order
     * @param  array $newItems [{variant_id, combo_id, quantity}]
     * @return array
     */
    public function previewEdit(Order $order, array $newItems): array
    {
        if (!$this->isEditable($order)) {
            throw new Exception('Order cannot be edited. Status must be pending or confirmed.');
        }

        if (empty($newItems)) {
            throw new Exception('Order must have at least one item.');
        }

        // Temporarily release current reserved stock in memory to get accurate availability
        // The pricing engine will validate stock as if this is a fresh order
        $pricing = $this->pricingService->calculate(
            items: $newItems,
            couponCode: $order->coupon_code_snapshot,
            zoneId: $order->zone_id,
            user: $order->user,
            withLock: false, // Preview only — no locks
        );

        return [
            'line_items'      => $pricing->lineItems,
            'subtotal'        => round($pricing->subtotal, 2),
            'tier_discount'   => round($pricing->tierDiscountTotal, 2),
            'coupon_discount' => round($pricing->couponDiscount, 2),
            'coupon_code'     => $pricing->coupon?->code,
            'shipping_cost'   => round($pricing->shippingCost, 2),
            'grand_total'     => round($pricing->grandTotal, 2),
            'changes'         => $this->diffItems($order, $newItems),
        ];
    }

    /**
     * Apply the edit — replace order items and recalculate totals.
     * Handles stock integrity (release old reservations, make new ones).
     *
     * @param  Order  $order
     * @param  array  $newItems [{variant_id, combo_id, quantity}]
     * @param  int    $adminId  The admin performing the edit
     * @return Order
     */
    public function applyEdit(Order $order, array $newItems, int $adminId): Order
    {
        if (!$this->isEditable($order)) {
            throw new Exception('Order cannot be edited. Status must be pending or confirmed.');
        }

        if (empty($newItems)) {
            throw new Exception('Order must have at least one item.');
        }

        return DB::transaction(function () use ($order, $newItems, $adminId) {
            // Lock the order to prevent race conditions
            $order = Order::lockForUpdate()->findOrFail($order->id);

            // Re-check editability after lock
            if (!$this->isEditable($order)) {
                throw new Exception('Order status changed during edit — no longer editable.');
            }

            // Snapshot old state for activity log
            $oldItems = $order->items()->get()->toArray();
            $oldTotals = [
                'subtotal'       => $order->subtotal,
                'discount_total' => $order->discount_total,
                'shipping_cost'  => $order->shipping_cost,
                'grand_total'    => $order->grand_total,
            ];

            // 1. Release ALL current reserved stock
            $this->releaseCurrentReservedStock($order);

            // 2. Delete existing order items
            $order->items()->delete();

            // 3. Run pricing engine with locks (validates stock, calculates prices)
            $pricing = $this->pricingService->calculate(
                items: $newItems,
                couponCode: $order->coupon_code_snapshot,
                zoneId: $order->zone_id,
                user: $order->user,
                withLock: true,
            );

            // 4. Create new order items from pricing engine
            foreach ($pricing->lineItems as $lineItem) {
                $order->items()->create($lineItem);
            }

            // 5. Reserve stock for new items
            foreach ($newItems as $item) {
                if (!empty($item['combo_id'])) {
                    $combo = Combo::with('items')->findOrFail($item['combo_id']);
                    foreach ($combo->items as $comboItem) {
                        $pricing->lockedVariants
                            ->get($comboItem->product_variant_id)
                            ->increment('reserved_stock', $comboItem->quantity * $item['quantity']);
                    }
                } else {
                    $pricing->lockedVariants
                        ->get($item['variant_id'])
                        ->increment('reserved_stock', $item['quantity']);
                }
            }

            // 6. Update order totals
            $order->update([
                'subtotal'        => $pricing->subtotal,
                'discount_total'  => $pricing->tierDiscountTotal + $pricing->couponDiscount,
                'shipping_cost'   => $pricing->shippingCost,
                'grand_total'     => $pricing->grandTotal,
                'coupon_id'       => $pricing->coupon?->id,
                'coupon_code_snapshot' => $pricing->coupon?->code,
                'coupon_discount' => $pricing->couponDiscount,
            ]);

            // 7. Log the edit
            $this->logEdit($order, $adminId, $oldItems, $oldTotals, $pricing);

            return $order->fresh(['items']);
        });
    }

    /**
     * Release reserved stock for all current order items.
     * Mirrors OrderStatusService::releaseStock but works on the current items.
     */
    private function releaseCurrentReservedStock(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->combo_id) {
                $combo = Combo::with('items')->find($item->combo_id);
                if ($combo) {
                    foreach ($combo->items as $comboItem) {
                        $variant = ProductVariant::lockForUpdate()->find($comboItem->product_variant_id);
                        if ($variant) {
                            $releaseQty = min($variant->reserved_stock, $comboItem->quantity * $item->quantity);
                            $variant->decrement('reserved_stock', $releaseQty);
                        }
                    }
                }
            } elseif ($item->variant_id) {
                $variant = ProductVariant::lockForUpdate()->find($item->variant_id);
                if ($variant) {
                    $releaseQty = min($variant->reserved_stock, $item->quantity);
                    $variant->decrement('reserved_stock', $releaseQty);
                }
            }
        }
    }

    /**
     * Compute a diff between old and new items for the preview.
     */
    private function diffItems(Order $order, array $newItems): array
    {
        $changes = [];
        $oldItems = $order->items->keyBy(fn($item) => ($item->variant_id ?? 'c' . $item->combo_id));

        $newMap = collect($newItems)->keyBy(fn($item) => ($item['variant_id'] ?? 'c' . ($item['combo_id'] ?? '')));

        // Items removed
        foreach ($oldItems as $key => $old) {
            if (!$newMap->has($key)) {
                $changes[] = [
                    'type'    => 'removed',
                    'name'    => $old->product_name_snapshot,
                    'variant' => $old->variant_title_snapshot,
                    'old_qty' => $old->quantity,
                    'new_qty' => 0,
                ];
            }
        }

        // Items added or quantity changed
        foreach ($newMap as $key => $new) {
            $old = $oldItems->get($key);
            if (!$old) {
                $variant = isset($new['variant_id']) ? ProductVariant::with('product')->find($new['variant_id']) : null;
                $combo   = isset($new['combo_id']) ? Combo::find($new['combo_id']) : null;
                $changes[] = [
                    'type'    => 'added',
                    'name'    => $variant?->product?->name ?? $combo?->title ?? 'Unknown',
                    'variant' => $variant?->title ?? 'Bundle',
                    'old_qty' => 0,
                    'new_qty' => $new['quantity'],
                ];
            } elseif ($old->quantity !== $new['quantity']) {
                $changes[] = [
                    'type'    => 'quantity_changed',
                    'name'    => $old->product_name_snapshot,
                    'variant' => $old->variant_title_snapshot,
                    'old_qty' => $old->quantity,
                    'new_qty' => $new['quantity'],
                ];
            }
        }

        return $changes;
    }

    /**
     * Log the edit action for audit trail.
     */
    private function logEdit(Order $order, int $adminId, array $oldItems, array $oldTotals, CheckoutPricingResult $pricing): void
    {
        try {
            ActivityLog::create([
                'log_name'     => 'order',
                'description'  => "Order {$order->order_number} items edited by admin",
                'subject_type' => Order::class,
                'subject_id'   => $order->id,
                'causer_type'  => \App\Models\User::class,
                'causer_id'    => $adminId,
                'event'        => 'order_items_edited',
                'properties'   => [
                    'old_totals' => $oldTotals,
                    'new_totals' => [
                        'subtotal'       => round($pricing->subtotal, 2),
                        'discount_total' => round($pricing->tierDiscountTotal + $pricing->couponDiscount, 2),
                        'shipping_cost'  => round($pricing->shippingCost, 2),
                        'grand_total'    => round($pricing->grandTotal, 2),
                    ],
                    'old_item_count' => count($oldItems),
                    'new_item_count' => count($pricing->lineItems),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Order edit activity log failed: ' . $e->getMessage());
        }
    }

    /**
     * Calculate max orderable quantity for a combo (limited by component stock).
     */
    private function comboMaxQuantity(Combo $combo, int $currentReservation): int
    {
        $max = PHP_INT_MAX;
        foreach ($combo->items as $comboItem) {
            $variant = $comboItem->variant ?? ProductVariant::find($comboItem->product_variant_id);
            if ($variant) {
                $available = $variant->available_stock + ($currentReservation * $comboItem->quantity);
                $max = min($max, intdiv($available, max(1, $comboItem->quantity)));
            }
        }
        return $max === PHP_INT_MAX ? 999 : $max;
    }
}

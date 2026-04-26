<?php

namespace App\Domains\Order\Services;

use App\Domains\ActivityLog\Services\AdminLogger;
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
     * Check if the order can be edited by an admin.
     * Delegates to Order::canAdminEdit() — shipment-aware.
     */
    public function isEditable(Order $order): bool
    {
        return $order->canAdminEdit();
    }

    /**
     * Get all current order data prepared for the admin edit form.
     * Includes customer info, address, zone, and items with live stock data.
     */
    public function getEditData(Order $order): array
    {
        if (!$this->isEditable($order)) {
            throw new Exception('Order cannot be edited. It has already been picked up by a courier or is in a terminal state.');
        }

        $order->load(['items', 'zone', 'shippingAddress']);

        $items = $order->items->map(function (OrderItem $item) {
            $variant = $item->variant_id ? ProductVariant::with('product', 'tierPrices')->find($item->variant_id) : null;
            $combo   = $item->combo_id ? Combo::with('items.variant')->find($item->combo_id) : null;

            return [
                'id'                     => $item->id,
                'variant_id'             => $item->variant_id,
                'combo_id'               => $item->combo_id,
                'product_name_snapshot'  => $item->product_name_snapshot,
                'variant_title_snapshot' => $item->variant_title_snapshot,
                'sku_snapshot'           => $item->sku_snapshot,
                'quantity'               => $item->quantity,
                'unit_price'             => (float) $item->unit_price,
                'original_unit_price'    => (float) $item->original_unit_price,
                'total_price'            => (float) $item->total_price,
                // Live stock info for validation in the UI
                'available_stock'        => $variant?->available_stock,
                'current_reserved'       => $item->quantity,
                'max_quantity'           => $variant
                    ? $variant->available_stock + $item->quantity
                    : ($combo ? $this->comboMaxQuantity($combo, $item->quantity) : 999),
                'is_active'              => $variant?->is_active ?? ($combo?->is_active ?? false),
            ];
        });

        return [
            'order_id'     => $order->id,
            'order_number' => $order->order_number,
            'order_status' => $order->order_status,

            // Customer info
            'customer_name'  => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_email' => $order->customer_email,

            // Shipping address
            'address' => $order->shippingAddress ? [
                'address_line' => $order->shippingAddress->address_line,
                'area'         => $order->shippingAddress->area,
                'city'         => $order->shippingAddress->city,
                'postal_code'  => $order->shippingAddress->postal_code,
            ] : null,

            // Pricing context
            'zone_id'        => $order->zone_id,
            'zone_name'      => $order->zone?->name,
            'coupon_code'    => $order->coupon_code_snapshot,
            'payment_method' => $order->payment_method,
            'notes'          => $order->notes,

            // Items
            'items' => $items->values()->toArray(),
        ];
    }

    /**
     * Preview recalculated totals without committing.
     * Accepts an optional new zone_id to preview shipping cost changes.
     */
    public function previewEdit(Order $order, array $newItems, ?int $newZoneId = null): array
    {
        if (!$this->isEditable($order)) {
            throw new Exception('Order cannot be edited.');
        }

        if (empty($newItems)) {
            throw new Exception('Order must have at least one item.');
        }

        $zoneId = $newZoneId ?? $order->zone_id;

        $pricing = $this->pricingService->calculate(
            items: $newItems,
            couponCode: $order->coupon_code_snapshot,
            zoneId: $zoneId,
            user: $order->user,
            withLock: false,
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
     * Apply the full edit — customer info, address, zone, items.
     * All changes are atomic inside a single DB transaction.
     *
     * @param  Order   $order
     * @param  array   $newItems     [{variant_id?, combo_id?, quantity}]
     * @param  int     $adminId
     * @param  array   $customerData {customer_name, customer_phone, customer_email?,
     *                               address_line, area?, city?, postal_code?, notes?}
     * @param  int|null $newZoneId   New shipping zone (null = keep existing)
     */
    public function applyEdit(
        Order $order,
        array $newItems,
        int $adminId,
        array $customerData = [],
        ?int $newZoneId = null,
    ): Order {
        if (!$this->isEditable($order)) {
            throw new Exception('Order cannot be edited. It may have been picked up or is in a terminal state.');
        }

        if (empty($newItems)) {
            throw new Exception('Order must have at least one item.');
        }

        return DB::transaction(function () use ($order, $newItems, $adminId, $customerData, $newZoneId) {
            // Lock the order row to prevent concurrent edits
            $order = Order::lockForUpdate()->findOrFail($order->id);

            if (!$this->isEditable($order)) {
                throw new Exception('Order status changed during edit — no longer editable.');
            }

            // Snapshot old state for the activity log
            $oldCustomer = [
                'name'         => $order->customer_name,
                'phone'        => $order->customer_phone,
                'email'        => $order->customer_email,
                'zone_id'      => $order->zone_id,
                'notes'        => $order->notes,
            ];
            $oldTotals = [
                'subtotal'       => $order->subtotal,
                'discount_total' => $order->discount_total,
                'shipping_cost'  => $order->shipping_cost,
                'grand_total'    => $order->grand_total,
            ];
            $oldItemCount = $order->items()->count();

            // ── 1. Release ALL current reserved stock ──────────────────
            $this->releaseCurrentReservedStock($order);

            // ── 2. Delete existing order items ─────────────────────────
            $order->items()->delete();

            // ── 3. Determine effective zone ────────────────────────────
            $effectiveZoneId = $newZoneId ?? $order->zone_id;

            // ── 4. Run pricing engine with locks (validates stock) ─────
            $pricing = $this->pricingService->calculate(
                items: $newItems,
                couponCode: $order->coupon_code_snapshot,
                zoneId: $effectiveZoneId,
                user: $order->user,
                withLock: true,
            );

            // ── 5. Create new order items ──────────────────────────────
            foreach ($pricing->lineItems as $lineItem) {
                $order->items()->create($lineItem);
            }

            // ── 6. Reserve stock for new items ─────────────────────────
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

            // ── 7. Update order-level fields (totals + zone + customer) ─
            $orderUpdates = [
                'zone_id'              => $effectiveZoneId,
                'subtotal'             => $pricing->subtotal,
                'discount_total'       => $pricing->tierDiscountTotal + $pricing->couponDiscount,
                'shipping_cost'        => $pricing->shippingCost,
                'grand_total'          => $pricing->grandTotal,
                'coupon_id'            => $pricing->coupon?->id,
                'coupon_code_snapshot' => $pricing->coupon?->code,
                'coupon_discount'      => $pricing->couponDiscount,
            ];

            if (!empty($customerData)) {
                if (!empty($customerData['customer_name']))  $orderUpdates['customer_name']  = $customerData['customer_name'];
                if (!empty($customerData['customer_phone'])) $orderUpdates['customer_phone'] = $customerData['customer_phone'];
                if (array_key_exists('customer_email', $customerData)) $orderUpdates['customer_email'] = $customerData['customer_email'];
                if (array_key_exists('notes', $customerData)) $orderUpdates['notes'] = $customerData['notes'];
            }

            $order->update($orderUpdates);

            // ── 8. Update shipping address ─────────────────────────────
            if (!empty($customerData)) {
                $addressData = array_filter([
                    'customer_name'  => $customerData['customer_name']  ?? null,
                    'customer_phone' => $customerData['customer_phone'] ?? null,
                    'address_line'   => $customerData['address_line']   ?? null,
                    'area'           => $customerData['area']           ?? null,
                    'city'           => $customerData['city']           ?? null,
                    'postal_code'    => $customerData['postal_code']    ?? null,
                ], fn($v) => $v !== null);

                if (!empty($addressData)) {
                    if ($order->shippingAddress) {
                        $order->shippingAddress->update($addressData);
                    } else {
                        $order->shippingAddress()->create(array_merge(['type' => 'shipping'], $addressData));
                    }
                }
            }

            // ── 9. Activity log ────────────────────────────────────────
            $this->logEdit($order, $adminId, $oldCustomer, $oldTotals, $oldItemCount, $customerData, $pricing);

            return $order->fresh(['items', 'zone', 'shippingAddress']);
        });
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    /**
     * Release all reserved stock for current order items (with floor protection).
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
                            $qty = min($variant->reserved_stock, $comboItem->quantity * $item->quantity);
                            $variant->decrement('reserved_stock', $qty);
                        }
                    }
                }
            } elseif ($item->variant_id) {
                $variant = ProductVariant::lockForUpdate()->find($item->variant_id);
                if ($variant) {
                    $qty = min($variant->reserved_stock, $item->quantity);
                    $variant->decrement('reserved_stock', $qty);
                }
            }
        }
    }

    /**
     * Compute human-readable diff between old and new items for preview.
     */
    private function diffItems(Order $order, array $newItems): array
    {
        $changes  = [];
        $oldItems = $order->items->keyBy(fn($item) => ($item->variant_id ?? 'c' . $item->combo_id));
        $newMap   = collect($newItems)->keyBy(fn($item) => ($item['variant_id'] ?? 'c' . ($item['combo_id'] ?? '')));

        foreach ($oldItems as $key => $old) {
            if (!$newMap->has($key)) {
                $changes[] = ['type' => 'removed', 'name' => $old->product_name_snapshot,
                    'variant' => $old->variant_title_snapshot, 'old_qty' => $old->quantity, 'new_qty' => 0];
            }
        }

        foreach ($newMap as $key => $new) {
            $old = $oldItems->get($key);
            if (!$old) {
                $variant = isset($new['variant_id']) ? ProductVariant::with('product')->find($new['variant_id']) : null;
                $combo   = isset($new['combo_id']) ? Combo::find($new['combo_id']) : null;
                $changes[] = ['type' => 'added',
                    'name' => $variant?->product?->name ?? $combo?->title ?? 'Unknown',
                    'variant' => $variant?->title ?? 'Bundle', 'old_qty' => 0, 'new_qty' => $new['quantity']];
            } elseif ((int) $old->quantity !== (int) $new['quantity']) {
                $changes[] = ['type' => 'quantity_changed', 'name' => $old->product_name_snapshot,
                    'variant' => $old->variant_title_snapshot, 'old_qty' => $old->quantity, 'new_qty' => $new['quantity']];
            }
        }

        return $changes;
    }

    /**
     * Calculate max orderable quantity for a combo (limited by lowest component stock).
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

    /**
     * Write activity log entry for the edit.
     */
    private function logEdit(
        Order $order,
        int $adminId,
        array $oldCustomer,
        array $oldTotals,
        int $oldItemCount,
        array $newCustomer,
        CheckoutPricingResult $pricing
    ): void {
        AdminLogger::log(
            'order',
            "Order {$order->order_number} edited by admin",
            $order,
            [
                'old_totals'   => $oldTotals,
                'new_totals'   => [
                    'subtotal'       => round($pricing->subtotal, 2),
                    'discount_total' => round($pricing->tierDiscountTotal + $pricing->couponDiscount, 2),
                    'shipping_cost'  => round($pricing->shippingCost, 2),
                    'grand_total'    => round($pricing->grandTotal, 2),
                ],
                'old_customer'     => $oldCustomer,
                'new_customer'     => $newCustomer,
                'old_item_count'   => $oldItemCount,
                'new_item_count'   => count($pricing->lineItems),
            ],
            'order_edited'
        );
    }
}

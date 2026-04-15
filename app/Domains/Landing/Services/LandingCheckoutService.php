<?php

namespace App\Domains\Landing\Services;

use App\Domains\Coupon\Models\Coupon;
use App\Domains\Coupon\Models\CouponUsage;
use App\Domains\Landing\Models\LandingPage;
use App\Domains\Order\DTOs\CheckoutPricingResult;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Services\CheckoutPricingService;
use App\Domains\Product\Models\Combo;
use App\Domains\Shipping\Models\ShippingZone;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * LandingCheckoutService
 *
 * Handles direct purchases from landing pages — no cart required.
 * Uses CheckoutPricingService as the single pricing engine.
 * Supports landing-page-specific rules: free delivery by amount/qty.
 */
class LandingCheckoutService
{
    public function __construct(
        private readonly CheckoutPricingService $pricingService,
    ) {}

    /**
     * Preview pricing for the landing page checkout form.
     * Called by the Alpine.js frontend on every qty/selection change.
     *
     * @param  array       $items      [{variant_id?, combo_id?, quantity}]
     * @param  int         $zoneId
     * @param  LandingPage $landing
     * @param  string|null $couponCode
     * @param  User|null   $user
     * @return array
     */
    public function preview(
        array $items,
        int $zoneId,
        LandingPage $landing,
        ?string $couponCode = null,
        ?User $user = null,
    ): array {
        $pricing = $this->pricingService->calculate(
            items: $items,
            couponCode: $couponCode,
            zoneId: $zoneId,
            user: $user,
            withLock: false,
        );

        // Apply landing-page-specific free delivery rules
        $shippingCost = $pricing->shippingCost;
        $shippingCost = $this->applyLandingShippingRules($landing, $pricing, $items, $shippingCost);
        $freeDeliveryApplied = $shippingCost < $pricing->shippingCost;

        $grandTotal = max(0, $pricing->subtotal - $pricing->tierDiscountTotal - $pricing->couponDiscount) + $shippingCost;

        return [
            'line_items'             => $pricing->lineItems,
            'subtotal'               => round($pricing->subtotal, 2),
            'tier_discount'          => round($pricing->tierDiscountTotal, 2),
            'coupon_discount'        => round($pricing->couponDiscount, 2),
            'coupon_code'            => $pricing->coupon?->code,
            'shipping_cost'          => round($shippingCost, 2),
            'grand_total'            => round($grandTotal, 2),
            'free_delivery_applied'  => $freeDeliveryApplied,
        ];
    }

    /**
     * Place an order directly from a landing page.
     *
     * @param  array       $data    {customer_name, customer_phone, customer_email?, address_line, city?, zone_id, payment_method, items, coupon_code?}
     * @param  LandingPage $landing
     * @param  User|null   $user
     * @return Order
     */
    public function checkout(array $data, LandingPage $landing, ?User $user = null): Order
    {
        // Coupon requires auth (same business rule as main checkout)
        if (!empty($data['coupon_code']) && !$user) {
            throw new Exception('Please log in to apply a coupon code.');
        }

        return DB::transaction(function () use ($data, $landing, $user) {

            // 1. Run pricing engine with locks
            $pricing = $this->pricingService->calculate(
                items: $data['items'],
                couponCode: $data['coupon_code'] ?? null,
                zoneId: $data['zone_id'],
                user: $user,
                withLock: true,
            );

            // 2. Apply landing-page free-delivery rules
            $shippingCost = $this->applyLandingShippingRules(
                $landing, $pricing, $data['items'], $pricing->shippingCost
            );
            $grandTotal = max(0, $pricing->subtotal - $pricing->tierDiscountTotal - $pricing->couponDiscount) + $shippingCost;

            // 3. Create Order
            $order = Order::create([
                'user_id'              => $user?->id,
                'checkout_token'       => (string) Str::uuid(),
                'order_number'         => 'BNC-' . now()->format('Ymd') . '-' . strtoupper(Str::random(8)),
                'customer_name'        => $data['customer_name'],
                'customer_phone'       => $data['customer_phone'],
                'customer_email'       => $data['customer_email'] ?? null,
                'zone_id'              => $data['zone_id'],
                'payment_method'       => $data['payment_method'],
                'payment_status'       => 'unpaid',
                'order_status'         => 'pending',
                'source'               => 'landing',
                'landing_page_id'      => $landing->id,
                'subtotal'             => $pricing->subtotal,
                'discount_total'       => $pricing->tierDiscountTotal + $pricing->couponDiscount,
                'shipping_cost'        => $shippingCost,
                'grand_total'          => $grandTotal,
                'coupon_id'            => $pricing->coupon?->id,
                'coupon_code_snapshot' => $pricing->coupon?->code,
                'coupon_discount'      => $pricing->couponDiscount,
                'placed_at'            => now(),
            ]);

            // 4. Shipping address
            $order->shippingAddress()->create([
                'type'           => 'shipping',
                'customer_name'  => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'address_line'   => $data['address_line'],
                'area'           => $data['area'] ?? null,
                'city'           => $data['city'] ?? null,
            ]);

            // 5. Order items
            foreach ($pricing->lineItems as $lineItem) {
                $order->items()->create($lineItem);
            }

            // 6. Reserve stock
            foreach ($data['items'] as $item) {
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

            // 7. Coupon usage
            if ($pricing->coupon && $user) {
                $this->recordCouponUsage($pricing->coupon, $order, $pricing->couponDiscount, $user);
            }

            // 8. Dispatch event
            $order->load('items');
            event(new \App\Events\OrderCreated($order));

            return $order;
        });
    }

    /**
     * Apply landing-page-specific free delivery rules.
     *
     * Rules (checked in order):
     * 1. free_delivery_amount — if discounted subtotal >= amount → free delivery
     * 2. free_delivery_qty   — if total quantity >= qty → free delivery
     */
    private function applyLandingShippingRules(
        LandingPage $landing,
        CheckoutPricingResult $pricing,
        array $items,
        float $currentShipping,
    ): float {
        // Rule 1: Free delivery by amount
        $freeAmount = $landing->freeDeliveryAmount();
        if ($freeAmount !== null) {
            $discountedSubtotal = $pricing->subtotal - $pricing->tierDiscountTotal;
            if ($discountedSubtotal >= $freeAmount) {
                return 0;
            }
        }

        // Rule 2: Free delivery by total quantity
        $freeQty = $landing->freeDeliveryQty();
        if ($freeQty !== null) {
            $totalQty = array_sum(array_column($items, 'quantity'));
            if ($totalQty >= $freeQty) {
                return 0;
            }
        }

        return $currentShipping;
    }

    private function recordCouponUsage(Coupon $coupon, Order $order, float $discount, User $user): void
    {
        if (CouponUsage::where('order_id', $order->id)->where('coupon_id', $coupon->id)->exists()) {
            return;
        }

        $affected = Coupon::where('id', $coupon->id)
            ->where(fn($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
            ->increment('used_count');

        if (!$affected) {
            throw new Exception('Coupon usage limit reached.');
        }

        CouponUsage::create([
            'coupon_id'       => $coupon->id,
            'user_id'         => $user->id,
            'order_id'        => $order->id,
            'discount_amount' => $discount,
        ]);
    }
}

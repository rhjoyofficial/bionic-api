<?php

namespace App\Domains\Order\Services;

use App\Domains\Cart\Models\Cart;
use App\Domains\Cart\Services\CartService;
use App\Domains\Coupon\Models\Coupon;
use App\Domains\Coupon\Models\CouponUsage;
use App\Domains\Order\Models\Order;
use App\Events\OrderCreated;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private readonly CheckoutPricingService $pricingService,
        private readonly CartService $cartService,
    ) {}

    /**
     * Create an order from validated checkout data.
     *
     * @param  array      $data      Validated checkout payload
     * @param  Cart|null  $cart      Resolved guest or user cart (may be null)
     * @param  User|null  $user      Authenticated user injected from the controller
     *                               (never resolved internally — services must not call Auth::)
     */
    public function create(array $data, ?Cart $cart = null, ?User $user = null): Order
    {
        // ── Business rule: coupons require an authenticated account ──────────
        // Checked BEFORE the DB transaction so no resources are acquired for
        // a request that is destined to fail.
        if (!empty($data['coupon_code']) && !$user) {
            throw new Exception('Please log in to apply a coupon code.');
        }

        $itemCount = count($data['items']);

        try {
            return DB::transaction(function () use ($data, $cart, $user) {

                // 1. Idempotency guard — prevent double orders on retry
                if (!empty($data['checkout_token'])) {
                    $existing = Order::with('items')
                        ->where('checkout_token', $data['checkout_token'])
                        ->latest('id')
                        ->first();

                    if ($existing) {
                        // Same token can arrive in true retries OR stale frontend token reuse.
                        if ($this->isSameCheckoutAttempt($existing, $data)) {
                            return $existing;
                        }

                        // Token collision for a different order attempt — rotate and continue.
                        $data['checkout_token'] = (string) Str::uuid();
                    }
                }

                // 2. Release cart reserved stock (will be re-reserved by order)
                if ($cart) {
                    $this->cartService->clearCart($cart);
                }

                // 3. Run the SINGLE pricing engine (locks variants, validates stock, calculates everything)
                $items = $data['items'];
                unset($data['items']);

                $pricing = $this->pricingService->calculate(
                    items: $items,
                    couponCode: $data['coupon_code'] ?? null,
                    zoneId: $data['zone_id'],
                    user: $user,          // passed in, never resolved internally
                    withLock: true,
                );

                // 4. Create Order record with final calculated totals
                $order = Order::create([
                    ...$data,
                    'user_id'              => $user?->id,  // null for guest orders
                    'checkout_token'       => $data['checkout_token'] ?? null,
                    'order_number'         => 'BNC-' . now()->format('Ymd') . '-' . strtoupper(Str::random(10)),
                    'subtotal'             => $pricing->subtotal,
                    'discount_total'       => $pricing->tierDiscountTotal + $pricing->couponDiscount,
                    'shipping_cost'        => $pricing->shippingCost,
                    'grand_total'          => $pricing->grandTotal,
                    'coupon_id'            => $pricing->coupon?->id,
                    'coupon_code_snapshot' => $pricing->coupon?->code,
                    'coupon_discount'      => $pricing->couponDiscount,
                    'payment_method'       => $data['payment_method'],
                    'payment_status'       => 'unpaid',
                    'order_status'         => 'pending',
                    'placed_at'            => now(),
                ]);

                // 5. Create shipping address
                $order->shippingAddress()->create([
                    'type'           => 'shipping',
                    'customer_name'  => $data['customer_name'],
                    'customer_phone' => $data['customer_phone'],
                    'address_line'   => $data['address_line'],
                    'city'           => $data['city'],
                ]);

                // 6. Create order items from pricing engine's line items
                foreach ($pricing->lineItems as $lineItem) {
                    $order->items()->create($lineItem);
                }

                // 7. Reserve stock for all variants (using locked instances from pricing engine)
                foreach ($items as $item) {
                    if (!empty($item['combo_id'])) {
                        $combo = \App\Domains\Product\Models\Combo::with('items')->findOrFail($item['combo_id']);
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

                // 8. Record coupon usage atomically (inside same transaction as pricing)
                if ($pricing->coupon) {
                    $this->recordCouponUsage($pricing->coupon, $order, $pricing->couponDiscount, $user);
                }

                // 9. Dispatch event
                $order->load('items');
                event(new OrderCreated($order));

                return $order;
            });
        } catch (Exception $e) {
            Log::error('Order Service Error: ' . $e->getMessage(), [
                'customer_phone' => $data['customer_phone'] ?? null,
                'checkout_token' => $data['checkout_token'] ?? null,
                'zone_id'        => $data['zone_id'] ?? null,
                'item_count'     => $itemCount,
            ]);
            throw $e;
        }
    }

    /**
     * Record coupon usage and increment the global used_count atomically.
     *
     * @param  User|null $user  The authenticated user who applied the coupon.
     *                          This is NEVER null here because the coupon-requires-auth
     *                          gate at the top of create() blocks guest coupon use before
     *                          the transaction is even opened.
     */
    private function recordCouponUsage(Coupon $coupon, Order $order, float $discount, ?User $user): void
    {
        // Safety net: should never be reached for guests because the gate in
        // create() throws before the transaction starts, but guard anyway.
        if (!$user) {
            throw new Exception('Coupon usage requires an authenticated user.');
        }

        $alreadyUsed = CouponUsage::where('order_id', $order->id)
            ->where('coupon_id', $coupon->id)
            ->exists();

        if ($alreadyUsed) return;

        // Atomic increment with limit check — prevents race conditions
        // between two concurrent requests using the same coupon.
        $affected = Coupon::where('id', $coupon->id)
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                    ->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->increment('used_count');

        if (!$affected) {
            throw new Exception('Coupon limit has been reached.');
        }

        CouponUsage::create([
            'coupon_id'       => $coupon->id,
            'user_id'         => $user->id,   // always non-null — gate enforces this above
            'order_id'        => $order->id,
            'discount_amount' => $discount,
        ]);
    }

    private function isSameCheckoutAttempt(Order $existing, array $incoming): bool
    {
        $sameMeta =
            ($existing->customer_name ?? null) === ($incoming['customer_name'] ?? null) &&
            ($existing->customer_phone ?? null) === ($incoming['customer_phone'] ?? null) &&
            (string) ($existing->zone_id ?? '') === (string) ($incoming['zone_id'] ?? '') &&
            ($existing->payment_method ?? null) === ($incoming['payment_method'] ?? null) &&
            ($existing->coupon_code_snapshot ?? null) === ($incoming['coupon_code'] ?? null);

        if (!$sameMeta) {
            return false;
        }

        $normalize = fn(array $item) => [
            'variant_id' => isset($item['variant_id']) ? (int) $item['variant_id'] : null,
            'combo_id'   => isset($item['combo_id']) ? (int) $item['combo_id'] : null,
            'quantity'   => (int) ($item['quantity'] ?? 0),
        ];

        $incomingItems = collect($incoming['items'] ?? [])
            ->map($normalize)
            ->sortBy(fn($i) => ($i['variant_id'] ?? 0) . ':' . ($i['combo_id'] ?? 0) . ':' . $i['quantity'])
            ->values()
            ->all();

        $existingItems = $existing->items
            ->map(fn($item) => [
                'variant_id' => $item->variant_id ? (int) $item->variant_id : null,
                'combo_id'   => $item->combo_id ? (int) $item->combo_id : null,
                'quantity'   => (int) $item->quantity,
            ])
            ->sortBy(fn($i) => ($i['variant_id'] ?? 0) . ':' . ($i['combo_id'] ?? 0) . ':' . $i['quantity'])
            ->values()
            ->all();

        return $incomingItems === $existingItems;
    }
}

<?php

namespace App\Domains\Order\Services;

use App\Domains\Cart\Models\Cart;
use App\Domains\Cart\Services\CartService;
use App\Domains\Coupon\Models\Coupon;
use App\Domains\Coupon\Models\CouponUsage;
use App\Domains\Order\Models\Order;
use App\Events\OrderCreated;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private readonly CheckoutPricingService $pricingService,
        private readonly CartService $cartService,
    ) {}

    public function create(array $data, ?Cart $cart = null): Order
    {
        $itemCount = count($data['items']);

        try {
            return DB::transaction(function () use ($data, $cart) {

                // 1. Idempotency guard — prevent double orders on retry
                if (!empty($data['checkout_token'])) {
                    $existing = Order::where('checkout_token', $data['checkout_token'])->first();
                    if ($existing) return $existing;
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
                    user: Auth::user(),
                    withLock: true,
                );

                // 4. Create Order record with final calculated totals
                $order = Order::create([
                    ...$data,
                    'user_id'              => Auth::id(),
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
                    $this->recordCouponUsage($pricing->coupon, $order, $pricing->couponDiscount);
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

    private function recordCouponUsage(Coupon $coupon, Order $order, float $discount): void
    {
        $alreadyUsed = CouponUsage::where('order_id', $order->id)
            ->where('coupon_id', $coupon->id)
            ->exists();

        if ($alreadyUsed) return;

        // Atomic increment with limit check
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
            'user_id'         => Auth::id(),
            'order_id'        => $order->id,
            'discount_amount' => $discount,
        ]);
    }
}

<?php

namespace App\Domains\Order\Services;

use App\Domains\Cart\Services\CartService;
use App\Domains\Coupon\Models\Coupon;
use App\Domains\Coupon\Models\CouponUsage;
use App\Domains\Order\DTOs\CheckoutPricingResult;
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

    public function create(array $data, ?\App\Domains\Cart\Models\Cart $cart = null): Order
    {
        $itemCount = count($data['items']);

        try {
            return DB::transaction(function () use ($data, $cart) {

                // Idempotency guard — prevent double orders on retry
                if (!empty($data['checkout_token'])) {
                    $existing = Order::where('checkout_token', $data['checkout_token'])->first();
                    if ($existing) return $existing;
                }

                if ($cart) {
                    $this->cartService->clearCart($cart);
                }

                $user = Auth::user();
                $items = $data['items'];
                unset($data['items']);

                // Single pricing engine — all math happens here
                $pricing = $this->pricingService->calculate(
                    items: $items,
                    couponCode: $data['coupon_code'] ?? null,
                    zoneId: $data['zone_id'],
                    user: $user,
                    withLock: true,
                );

                $order = Order::create([
                    ...$data,
                    'user_id'        => $user?->id,
                    'checkout_token' => $data['checkout_token'] ?? null,
                    'order_number'   => 'BNC-' . now()->format('Ymd') . '-' . strtoupper(Str::random(10)),
                    'subtotal'       => $pricing->subtotal,
                    'discount_total' => $pricing->tierDiscountTotal + $pricing->couponDiscount,
                    'shipping_cost'  => $pricing->shippingCost,
                    'grand_total'    => $pricing->grandTotal,
                    'coupon_id'             => $pricing->coupon?->id,
                    'coupon_code_snapshot'  => $pricing->coupon?->code,
                    'coupon_discount'       => $pricing->couponDiscount,
                    'payment_method' => $data['payment_method'],
                    'payment_status' => 'unpaid',
                    'order_status'   => 'pending',
                    'placed_at'      => now(),
                ]);

                $order->shippingAddress()->create([
                    'type'           => 'shipping',
                    'customer_name'  => $data['customer_name'],
                    'customer_phone' => $data['customer_phone'],
                    'address_line'   => $data['address_line'],
                    'city'           => $data['city'],
                ]);

                // Create order items and reserve stock from the pricing result
                foreach ($pricing->lineItems as $line) {
                    $order->items()->create([
                        'product_id'              => $line['variant_id'] ? $pricing->lockedVariants->get($line['variant_id'])?->product_id : null,
                        'variant_id'              => $line['variant_id'],
                        'combo_id'                => $line['combo_id'],
                        'sku_snapshot'            => $line['variant_id'] ? $pricing->lockedVariants->get($line['variant_id'])?->sku : null,
                        'product_name_snapshot'   => $line['name'],
                        'variant_title_snapshot'  => $line['variant_title'],
                        'quantity'                => $line['quantity'],
                        'original_unit_price'     => $line['original_unit_price'],
                        'unit_price'              => $line['unit_price'],
                        'total_price'             => $line['line_total'],
                        'discount_type_snapshot'  => $line['discount_type'],
                        'discount_value_snapshot' => $line['discount_value'],
                    ]);

                    // Reserve stock
                    if ($line['combo_id']) {
                        $combo = \App\Domains\Product\Models\Combo::with('items')->find($line['combo_id']);
                        if ($combo) {
                            foreach ($combo->items as $comboItem) {
                                $pricing->lockedVariants->get($comboItem->product_variant_id)
                                    ?->increment('reserved_stock', $comboItem->quantity * $line['quantity']);
                            }
                        }
                    } else {
                        $pricing->lockedVariants->get($line['variant_id'])
                            ?->increment('reserved_stock', $line['quantity']);
                    }
                }

                // Record coupon usage
                if ($pricing->coupon) {
                    $this->recordCouponUsage($pricing, $order);
                }

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

    private function recordCouponUsage(CheckoutPricingResult $pricing, Order $order): void
    {
        $coupon = $pricing->coupon;

        $alreadyUsed = CouponUsage::where('order_id', $order->id)
            ->where('coupon_id', $coupon->id)
            ->exists();

        if ($alreadyUsed) return;

        // Handle null usage_limit: if null, coupon is unlimited so always allow
        $affected = Coupon::where('id', $coupon->id)
            ->where(fn($q) => $q
                ->whereNull('usage_limit')
                ->orWhereColumn('used_count', '<', 'usage_limit')
            )
            ->increment('used_count');

        if (!$affected) {
            throw new Exception('Coupon limit has been reached.');
        }

        CouponUsage::create([
            'coupon_id'       => $coupon->id,
            'user_id'         => Auth::id(),
            'order_id'        => $order->id,
            'discount_amount' => $pricing->couponDiscount,
        ]);
    }
}

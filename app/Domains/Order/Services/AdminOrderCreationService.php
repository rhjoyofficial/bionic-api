<?php

namespace App\Domains\Order\Services;

use App\Domains\ActivityLog\Services\AdminLogger;
use App\Domains\Coupon\Models\Coupon;
use App\Domains\Coupon\Models\CouponUsage;
use App\Domains\Order\Models\Order;
use App\Domains\Product\Models\Combo;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * AdminOrderCreationService
 *
 * Creates orders on behalf of customers from the admin panel.
 * - No cart required
 * - No checkout_token idempotency (admin intentionally creates)
 * - Coupon restriction (auth required) is waived — admin can apply any coupon
 * - Uses the same CheckoutPricingService for all pricing (no duplicated logic)
 */
class AdminOrderCreationService
{
    public function __construct(
        private readonly CheckoutPricingService $pricingService,
    ) {}

    /**
     * Create a new order from admin-provided data.
     *
     * @param  array $data {
     *   customer_name, customer_phone, customer_email?,
     *   address_line, area?, city?, postal_code?,
     *   zone_id, payment_method,
     *   items: [{variant_id?, combo_id?, quantity}],
     *   coupon_code?, notes?
     * }
     * @param  int  $adminId  The admin performing the creation
     * @param  User|null $linkedUser  Optional registered user to link the order to
     */
    public function create(array $data, int $adminId, ?User $linkedUser = null): Order
    {
        return DB::transaction(function () use ($data, $adminId, $linkedUser) {

            // 1. Run pricing engine (acquires locks, validates stock, calculates everything)
            $pricing = $this->pricingService->calculate(
                items: $data['items'],
                couponCode: $data['coupon_code'] ?? null,
                zoneId: $data['zone_id'],
                user: $linkedUser,   // null is fine — coupon restriction waived below
                withLock: true,
            );

            // 2. Create Order record
            $order = Order::create([
                'user_id'              => $linkedUser?->id,
                'checkout_token'       => (string) Str::uuid(),
                'order_number'         => 'BNC-' . now()->format('Ymd') . '-' . strtoupper(Str::random(8)),
                'customer_name'        => $data['customer_name'],
                'customer_phone'       => $data['customer_phone'],
                'customer_email'       => $data['customer_email'] ?? null,
                'zone_id'              => $data['zone_id'],
                'payment_method'       => $data['payment_method'],
                'payment_status'       => $data['payment_status'] ?? 'unpaid',
                'order_status'         => 'pending',
                'notes'                => $data['notes'] ?? null,
                'subtotal'             => $pricing->subtotal,
                'discount_total'       => $pricing->tierDiscountTotal + $pricing->couponDiscount,
                'shipping_cost'        => $pricing->shippingCost,
                'grand_total'          => $pricing->grandTotal,
                'coupon_id'            => $pricing->coupon?->id,
                'coupon_code_snapshot' => $pricing->coupon?->code,
                'coupon_discount'      => $pricing->couponDiscount,
                'placed_at'            => now(),
            ]);

            // 3. Create shipping address
            $order->shippingAddress()->create([
                'type'           => 'shipping',
                'customer_name'  => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'address_line'   => $data['address_line'],
                'area'           => $data['area'] ?? null,
                'city'           => $data['city'] ?? null,
                'postal_code'    => $data['postal_code'] ?? null,
            ]);

            // 4. Create order items from pricing engine's line items
            foreach ($pricing->lineItems as $lineItem) {
                $order->items()->create($lineItem);
            }

            // 5. Reserve stock for all variants (using locked instances from pricing engine)
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

            // 6. Record coupon usage if a coupon was applied
            //    Admin can apply coupons regardless of auth — record usage to linked user if available
            if ($pricing->coupon && $linkedUser) {
                $this->recordCouponUsage($pricing->coupon, $order, $pricing->couponDiscount, $linkedUser);
            }

            // 7. Activity log
            AdminLogger::log(
                'order',
                "Order {$order->order_number} created by admin",
                $order,
                [
                    'grand_total'    => round($pricing->grandTotal, 2),
                    'item_count'     => count($pricing->lineItems),
                    'payment_method' => $data['payment_method'],
                    'coupon_code'    => $pricing->coupon?->code,
                    'linked_user_id' => $linkedUser?->id,
                ],
                'order_created_by_admin'
            );

            return $order->load(['items', 'zone', 'shippingAddress']);
        });
    }

    private function recordCouponUsage(Coupon $coupon, Order $order, float $discount, User $user): void
    {
        $alreadyUsed = CouponUsage::where('order_id', $order->id)
            ->where('coupon_id', $coupon->id)
            ->exists();

        if ($alreadyUsed) return;

        $affected = Coupon::where('id', $coupon->id)
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                  ->orWhereColumn('used_count', '<', 'usage_limit');
            })
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

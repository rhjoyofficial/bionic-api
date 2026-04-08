<?php

namespace App\Domains\Order\Controllers;

use App\Domains\Cart\Services\CartService;
use App\Domains\Order\Requests\CheckoutPreviewRequest;
use App\Domains\Order\Requests\CheckoutRequest;
use App\Domains\Order\Services\CheckoutPricingService;
use App\Domains\Order\Services\OrderService;
use App\Domains\Order\Resources\OrderResource;
use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly OrderService $service,
        private readonly CheckoutPricingService $pricingService,
        private readonly CartService $cartService,
    ) {}

    public function index()
    {
        return view('store.checkout');
    }

    /**
     * Returns authoritative pricing breakdown without creating an order.
     * Frontend calls this on page load, zone change, or coupon change.
     */
    public function preview(CheckoutPreviewRequest $request)
    {
        try {
            $validated = $request->validated();

            // Resolve the authenticated user once at the controller boundary.
            // Use the web guard explicitly so the PHP session is checked correctly
            // regardless of which guard the route middleware resolved.
            $authUser = Auth::guard('web')->user();

            $result = DB::transaction(fn() => $this->pricingService->calculate(
                items: $validated['items'],
                couponCode: $validated['coupon_code'] ?? null,
                zoneId: $validated['zone_id'] ?? null,
                user: $authUser,
                withLock: false,
            ));

            return ApiResponse::success($result->toArray(), 'Pricing calculated');
        } catch (Exception $e) {
            return ApiResponse::error(
                $e->getMessage() ?: 'Could not calculate pricing.',
                null,
                $this->resolveStatus($e),
            );
        }
    }

    public function store(CheckoutRequest $request)
    {
        // Resolve the authenticated user once at the controller boundary.
        // Services must NOT call Auth:: internally — the controller is the auth boundary.
        /** @var User|null $authUser */
        $authUser = Auth::guard('web')->user();

        try {
            $order = $this->service->create(
                $request->validated(),
                $this->resolveCheckoutCart($request, $authUser),
                $authUser,
            );
            
            $request->session()->put('last_order_id', $order->id);
            $redirectUrl = $this->resolveRedirectUrl($order);

            if (!$request->expectsJson()) {
                return redirect()->to($redirectUrl);
            }

            return ApiResponse::success(
                array_merge(
                    (new OrderResource($order))->toArray($request),
                    ['redirect_url' => $redirectUrl]
                ),
                'Order placed successfully',
                201
            );
        } catch (Exception $e) {
            Log::error('Checkout Error: ' . $e->getMessage(), [
                'customer_phone' => $request->input('customer_phone'),
                'zone_id'        => $request->input('zone_id'),
                'item_count'     => count($request->input('items', [])),
            ]);

            if (!$request->expectsJson()) {
                return back()->withErrors([
                    'checkout' => $e->getMessage() ?: 'Order could not be placed. Please try again.',
                ])->withInput();
            }

            return ApiResponse::error(
                $e->getMessage() ?: 'Order could not be placed. Please try again.',
                config('app.debug') ? $e->getMessage() : null,
                $this->resolveStatus($e),
            );
        }
    }

    /**
     * Route to the right post-payment URL based on payment method.
     *
     * COD       → success page immediately.
     * sslcommerz → initiate payment and return gateway URL.
     *              TODO: Replace stub with real SSL Commerz initiation.
     *              Package: karim007/laravel-sslcommerz
     *              Docs: https://github.com/karim007/laravel-sslcommerz
     */
    private function resolveRedirectUrl($order): string
    {
        if ($order->payment_method === 'sslcommerz') {
            // ─────────────────────────────────────────────────────────
            // STUB — Replace with real SSL Commerz initiation:
            //
            // $post_data = [
            //     'total_amount'  => $order->grand_total,
            //     'currency'      => 'BDT',
            //     'tran_id'       => $order->order_number,
            //     'success_url'   => route('sslcommerz.success'),
            //     'fail_url'      => route('sslcommerz.fail'),
            //     'cancel_url'    => route('sslcommerz.cancel'),
            //     'cus_name'      => $order->customer_name,
            //     'cus_phone'     => $order->customer_phone,
            //     'cus_email'     => $order->customer_email ?? 'guest@example.com',
            //     'cus_add1'      => $order->shippingAddress->address_line ?? '',
            //     'cus_city'      => $order->shippingAddress->city ?? '',
            //     'cus_country'   => 'Bangladesh',
            //     'shipping_method' => 'NO',
            //     'product_name'  => 'Order ' . $order->order_number,
            //     'product_category' => 'General',
            //     'product_profile' => 'general',
            // ];
            // $sslc = new SslCommerzNotification();
            // $response = $sslc->makePayment($post_data, 'checkout', 'json');
            // return $response['GatewayPageURL'] ?? route('order.failed');
            // ─────────────────────────────────────────────────────────
            return route('order.failed') . '?reason=payment_gateway_pending&order=' . $order->order_number;
        }

        // COD — go straight to success
        return route('order.success', ['order' => $order->order_number]);
    }

    private function resolveStatus(Exception $e): int
    {
        return match (true) {
            $e instanceof ValidationException      => 422,
            $e instanceof ModelNotFoundException   => 404,
            default                                => 500,
        };
    }

    private function resolveCheckoutCart(CheckoutRequest $request, ?User $authUser): mixed
    {
        try {
            // Authenticated user: find cart by user ID (ignores session token).
            if ($authUser) {
                return $this->cartService->getCart($authUser->id, null);
            }

            // Guest: find cart by session token from cookie / header / payload.
            $token = $request->attributes->get('cart_token')
                ?? $request->header('X-Session-Token')
                ?? $request->cookie('bionic_cart_token')
                ?? $request->input('checkout_token');

            if (!$token) {
                return null;
            }

            return $this->cartService->getCart(null, $token);
        } catch (Throwable) {
            return null;
        }
    }
}

<?php

namespace App\Domains\Cart\Controllers;

use App\Domains\Cart\Services\CartPricingService;
use App\Domains\Cart\Services\CartService;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse; // Import helper
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private CartPricingService $pricing
    ) {}

    public function view(Request $request)
    {
        try {
            $cart = $this->cartService->getCart(
                Auth::id(),
                $request->session_token
            );

            $cart->load('items.variant.product');
            $totals = $this->pricing->calculate($cart);

            return ApiResponse::success([
                'cart' => $cart,
                'totals' => $totals
            ], 'Cart retrieved successfully');
        } catch (Exception $e) {
            return $this->handleError($e, 'Could not retrieve cart');
        }
    }

    public function add(Request $request)
    {
        try {
            $request->validate([
                'variant_id' => 'required|exists:product_variants,id',
                'quantity' => 'required|integer|min:1'
            ]);

            $cart = $this->cartService->getCart(Auth::id(), $request->session_token);
            $item = $this->cartService->addItem($cart, $request->variant_id, $request->quantity);

            return ApiResponse::success($item, 'Item added to cart', 201);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to add item to cart');
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'variant_id' => 'required',
                'quantity' => 'required|integer|min:1'
            ]);

            $cart = $this->cartService->getCart(Auth::id(), $request->session_token);
            $updatedItem = $this->cartService->updateItem($cart, $request->variant_id, $request->quantity);

            return ApiResponse::success($updatedItem, 'Cart updated');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to update cart');
        }
    }

    public function remove(Request $request)
    {
        try {
            $cart = $this->cartService->getCart(Auth::id(), $request->session_token);
            $this->cartService->removeItem($cart, $request->variant_id);

            return ApiResponse::success(null, 'Item removed from cart');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to remove item');
        }
    }

    public function clear(Request $request)
    {
        try {
            $cart = $this->cartService->getCart(Auth::id(), $request->session_token);
            $this->cartService->clearCart($cart);

            return ApiResponse::success(null, 'Cart cleared');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to clear cart');
        }
    }

    private function handleError(Exception $e, string $msg)
    {
        Log::error($msg . ': ' . $e->getMessage());
        return ApiResponse::error(
            $msg,
            config('app.debug') ? $e->getMessage() : null,
            500
        );
    }
}

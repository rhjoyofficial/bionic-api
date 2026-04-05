<?php

namespace App\Domains\Cart\Controllers;

use App\Domains\Cart\Services\CartService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;

class PublicCartController extends Controller
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function view(Request $request)
    {
        try {
            $sessionToken = $request->attributes->get('cart_token');
            // dd($sessionToken);
            $cart = $this->cartService->getCart(Auth::id(), $sessionToken);

            $pricesUpdated = $this->cartService->syncCartPrices($cart);
            $cartData = $this->cartService->formatCartDetails($cart->fresh());
            // dd($cartData);
            if ($pricesUpdated) {
                flash('info', 'Prices in your cart have been updated based on current product pricing.');
            }
            return view('store.cart', $cartData);
        } catch (Exception $e) {
            Log::error('Public Cart View Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'We encountered an issue loading your cart.');
        }
    }
}

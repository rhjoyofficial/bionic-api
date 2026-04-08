<?php

namespace App\Domains\Customer\Controllers;

use App\Domains\Order\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerDashboard extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)->latest()->take(5)->get();

        return view('customer.dashboard', [
            'user' => $user,
            'orders' => $orders,
            'orderCount' => Order::where('user_id', $user->id)->count(),
            'totalSpent' => Order::where('user_id', $user->id)->sum('grand_total'),
        ]);
    }

    public function orders(): View
    {
        $orders = Order::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('customer.orders', compact('orders'));
    }

    public function orderDetails(Order $order): View
    {
        abort_unless($order->user_id === Auth::id(), 403);
        $order->load(['items', 'shippingAddress']);

        return view('customer.order-details', compact('order'));
    }

    public function profile(): View
    {
        return view('customer.profile', ['user' => Auth::user()]);
    }

    public function generateReferralCode(): RedirectResponse
    {
        $user = Auth::user();

        do {
            $code = Str::upper(Str::random(8));
        } while (
            \App\Models\User::query()
            ->where('referral_code', $code)
            ->exists()
        );

        $user->update(['referral_code' => $code]);

        return back()->with('success', 'Referral code generated successfully.');
    }
}

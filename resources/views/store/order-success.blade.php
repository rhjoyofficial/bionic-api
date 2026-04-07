@extends('layouts.app')

@section('title', 'Order Confirmed')

@section('content')
    <section class="bg-[#f0f5f1] min-h-screen flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-xl">

            {{-- Success card --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">

                {{-- Header --}}
                <div class="bg-green-800 px-8 py-10 text-center relative overflow-hidden">
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-4 left-8 w-24 h-24 rounded-full bg-white"></div>
                        <div class="absolute bottom-2 right-6 w-16 h-16 rounded-full bg-white"></div>
                    </div>
                    <div class="relative">
                        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-white font-bengali">Order Confirmed!</h1>
                        <p class="text-green-100 text-sm mt-1">Thank you for your order</p>
                        <p class="text-white/70 text-xs mt-3 font-mono tracking-wider">Order #{{ $order->order_number }}</p>
                    </div>
                </div>

                {{-- Body --}}
                <div class="px-8 py-6 space-y-6">

                    {{-- Customer info --}}
                    @if($order->shippingAddress)
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Delivering to</h4>
                            <div class="bg-gray-50 rounded-xl p-4 space-y-1">
                                <p class="font-semibold text-gray-800 text-sm">{{ $order->shippingAddress->customer_name }}</p>
                                <p class="text-gray-500 text-sm">{{ $order->shippingAddress->customer_phone }}</p>
                                <p class="text-gray-500 text-sm">{{ collect([$order->shippingAddress->address_line, $order->shippingAddress->city])->filter()->join(', ') }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Items --}}
                    @if($order->items->count())
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Items Ordered</h4>
                            <div class="space-y-2">
                                @foreach($order->items as $item)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800 font-bengali">{{ $item->product_name_snapshot }}</p>
                                            <p class="text-xs text-gray-400">{{ $item->variant_title_snapshot }} &times; {{ $item->quantity }}</p>
                                        </div>
                                        <p class="text-sm font-bold text-gray-800 font-bengali">{{ number_format($item->total_price, 2) }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Totals --}}
                    <div class="space-y-2 pt-4 border-t border-gray-100">
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Subtotal</span>
                            <span class="font-medium text-gray-800 font-bengali">{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        @if($order->discount_total > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Discount</span>
                                <span class="font-medium text-green-600 font-bengali">&minus;{{ number_format($order->discount_total, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Shipping</span>
                            <span class="font-medium text-gray-800 font-bengali">{{ $order->shipping_cost == 0 ? 'Free' : number_format($order->shipping_cost, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                            <span class="font-bold text-gray-800">Total</span>
                            <span class="text-xl font-bold text-green-800 font-bengali">{{ number_format($order->grand_total, 2) }}</span>
                        </div>
                    </div>

                    {{-- Payment method --}}
                    @if($order->payment_method === 'cod')
                        <div class="flex items-center gap-3 bg-amber-50 border border-amber-100 rounded-xl p-4">
                            <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <div>
                                <p class="text-sm font-bold text-amber-800">Cash on Delivery</p>
                                <p class="text-xs text-amber-600 mt-0.5">Please have the exact amount ready when your order arrives.</p>
                            </div>
                        </div>
                    @endif

                    {{-- CTA --}}
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('shop') }}"
                            class="flex-1 flex items-center justify-center gap-2 bg-green-800 text-white py-3 rounded-full font-bold text-sm hover:bg-green-900 transition-all shadow-md shadow-green-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            Continue Shopping
                        </a>
                        @auth
                            <a href="/account/orders"
                                class="flex-1 flex items-center justify-center gap-2 border border-green-800 text-green-800 py-3 rounded-full font-bold text-sm hover:bg-green-50 transition-all">
                                My Orders
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            // Clean up sessionStorage from frontend checkout flow
            sessionStorage.removeItem('bionic_last_order');
            sessionStorage.removeItem('bionic_coupon');
        </script>
    @endpush
@endsection

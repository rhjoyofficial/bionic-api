@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
    <section class="max-w-7xl mx-auto px-4 md:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            @include('customer.partials.nav')

            <div class="lg:col-span-3 space-y-6">
                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Order #{{ $order->order_number }}</h1>
                            <p class="text-sm text-gray-500 mt-1">Placed on {{ $order->created_at?->format('d M, Y h:i A') }}
                            </p>
                        </div>
                        <span class="text-sm px-3 py-1 rounded-lg bg-gray-100 text-gray-700 capitalize">
                            {{ str_replace('_', ' ', $order->order_status ?? 'pending') }}
                        </span>
                    </div>
                </div>

                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Items</h2>
                    <div class="space-y-3">
                        @foreach ($order->items as $item)
                            <div
                                class="flex items-center justify-between border-b border-gray-100 pb-3 last:border-0 last:pb-0">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $item->product_name_snapshot }}</p>
                                    <p class="text-xs text-gray-500">Qty: {{ $item->quantity }}</p>
                                </div>
                                <p class="font-semibold text-gray-900 font-bengali">
                                    ৳{{ number_format($item->total_price, 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Order Summary</h2>
                    <div class="space-y-2 text-sm font-bengali">
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span>৳{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Shipping</span>
                            <span>৳{{ number_format($order->shipping_cost, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Discount</span>
                            <span>-৳{{ number_format($order->discount_total, 2) }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between text-gray-900 font-bold pt-2 border-t border-gray-100">
                            <span>Total</span>
                            <span>৳{{ number_format($order->grand_total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
    <section class="max-w-7xl mx-auto px-4 md:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            @include('customer.partials.nav')

            <div class="lg:col-span-3 bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">My Orders</h1>

                @forelse($orders as $order)
                    <div class="border border-gray-100 rounded-xl p-4 mb-3 last:mb-0">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div>
                                <p class="font-semibold text-gray-900">Order #{{ $order->order_number }}</p>
                                <p class="text-xs text-gray-500">{{ $order->created_at?->format('d M, Y h:i A') }}</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="text-sm px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 capitalize">
                                    {{ str_replace('_', ' ', $order->order_status ?? 'pending') }}
                                </span>
                                <span
                                    class="font-semibold text-gray-900 font-bengali">৳{{ number_format($order->grand_total, 2) }}</span>
                                <a href="{{ route('customer.order-details', $order) }}"
                                    class="text-sm font-semibold text-green-700 hover:text-green-800">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No orders found.</p>
                @endforelse

                <div class="mt-6">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection

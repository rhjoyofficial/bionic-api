@extends('layouts.app')

@section('title', 'Customer Dashboard')

@section('content')
    <section class="max-w-7xl mx-auto px-4 md:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            @include('customer.partials.nav')

            <div class="lg:col-span-3 space-y-6">
                @if (session('success'))
                    <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-green-700 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Welcome back,</p>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                            <p class="text-sm text-gray-500 mt-1">Manage your orders and profile from this dashboard.</p>
                        </div>

                        <div class="text-right">
                            <p class="text-xs uppercase text-gray-500 mb-2">Referral Code</p>
                            @if ($user->referral_code)
                                <p
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-100 text-gray-900 font-semibold tracking-wider">
                                    {{ $user->referral_code }}
                                </p>
                            @else
                                <p class="text-sm text-gray-500 mb-2">No referral code generated yet.</p>
                            @endif
                            <form action="{{ route('customer.referral.generate') }}" method="POST" class="mt-2">
                                @csrf
                                <button type="submit"
                                    class="cursor-pointer inline-flex items-center rounded-lg bg-green-600 text-white text-sm font-semibold px-4 py-2 hover:bg-green-700 transition-colors">
                                    Generate Referral Code
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                        <p class="text-sm text-gray-500">Total Orders</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $orderCount }}</p>
                    </div>
                    <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                        <p class="text-sm text-gray-500">Total Spent</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1 font-bengali">৳{{ number_format($totalSpent, 2) }}
                        </p>
                    </div>
                </div>

                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900">Recent Orders</h2>
                        <a href="{{ route('customer.orders') }}"
                            class="text-sm font-semibold text-green-700 hover:text-green-800">
                            View all
                        </a>
                    </div>

                    @forelse($orders as $order)
                        <div
                            class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 py-3 border-b border-gray-100 last:border-0">
                            <div>
                                <p class="font-semibold text-gray-900">#{{ $order->order_number }}</p>
                                <p class="text-xs text-gray-500">{{ $order->created_at?->format('d M, Y h:i A') }}</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <p class="font-semibold text-gray-900 font-bengali">
                                    ৳{{ number_format($order->grand_total, 2) }}</p>
                                <a href="{{ route('customer.order-details', $order) }}"
                                    class="text-sm font-semibold text-green-700 hover:text-green-800">Details</a>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">You do not have any orders yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endsection

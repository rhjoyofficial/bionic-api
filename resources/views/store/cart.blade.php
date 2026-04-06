@extends('layouts.app')

@section('title', 'Your Cart')

@section('content')
    <section class="bg-[#f0f5f1] min-h-screen">
        <div class="max-w-8xl mx-auto px-4 py-6 md:py-10">

            <x-page-header :breadcrumbs="[['label' => 'Home', 'url' => route('shop')], ['label' => 'Cart', 'url' => null]]" />

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

                {{-- ==============================
                     LEFT: Cart Items
                ============================== --}}
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                    <div class="p-6 pb-0 flex justify-between items-end">
                        <h2 class="text-2xl font-bold text-gray-800">Shopping Cart</h2>
                        <span id="pageCartCount" class="text-gray-400 text-sm font-medium">0 items</span>
                    </div>

                    {{-- Items injected here by CartPageRenderer --}}
                    <div id="pageCartItems" class="p-6 space-y-0">
                        {{-- Loading skeleton --}}
                        <div id="pageCartSkeleton" class="space-y-5 animate-pulse">
                            @foreach (range(1, 2) as $i)
                                <div class="flex items-center gap-5 py-5 border-b border-gray-100">
                                    <div class="w-20 h-20 rounded-xl bg-gray-100 shrink-0"></div>
                                    <div class="flex-1 space-y-2">
                                        <div class="h-4 bg-gray-100 rounded w-2/3"></div>
                                        <div class="h-3 bg-gray-100 rounded w-1/3"></div>
                                        <div class="flex justify-between mt-3">
                                            <div class="h-8 w-28 bg-gray-100 rounded-xl"></div>
                                            <div class="h-5 w-20 bg-gray-100 rounded"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="px-6 pb-6">
                        <a href="{{ route('shop') }}"
                            class="inline-flex items-center text-green-700 font-semibold hover:text-green-800 transition-colors text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Continue Shopping
                        </a>
                    </div>
                </div>

                {{-- ==============================
                     RIGHT: Order Summary
                ============================== --}}
                <div class="lg:col-span-1 sticky top-8" id="pageSummaryBox">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-6">Order Summary</h2>

                        <div class="space-y-3">

                            {{-- Subtotal --}}
                            <div class="flex justify-between text-gray-500 text-sm font-medium">
                                <span>Subtotal</span>
                                <span id="pageSubtotal" class="text-gray-900 font-bold font-bengali">৳0</span>
                            </div>

                            {{-- Discount row — hidden until coupon applied --}}
                            <div id="pageDiscountRow" class="hidden flex justify-between text-sm font-medium">
                                <span class="text-gray-500">Coupon Discount</span>
                                <span id="pageDiscountAmount" class="text-green-600 font-bold font-bengali">− ৳0</span>
                            </div>

                            <hr class="border-gray-100 my-2">

                            {{-- Total --}}
                            <div class="flex justify-between items-center">
                                <span class="text-base font-semibold text-gray-800">Total</span>
                                <span id="pageTotal" class="text-2xl font-bold text-gray-900 font-bengali">৳0</span>
                            </div>
                        </div>

                        {{-- Coupon Input --}}
                        <div class="mt-5">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Promo
                                Code</label>
                            <div
                                class="flex items-center bg-gray-50 rounded-xl border border-gray-200 overflow-hidden focus-within:border-green-400 focus-within:ring-2 focus-within:ring-green-100 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600 ml-3 shrink-0"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <input id="couponInput" type="text" placeholder="Enter promo code"
                                    class="bg-transparent flex-1 text-sm px-3 py-2.5 focus:outline-none text-gray-700 placeholder-gray-400 uppercase tracking-wider"
                                    autocomplete="off">
                                <button id="couponApplyBtn"
                                    class="bg-green-800 text-white px-4 py-2.5 text-sm font-semibold hover:bg-primary transition-colors shrink-0 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                                    Apply
                                </button>
                            </div>
                            <p id="couponFeedback" class="hidden text-xs mt-2 font-medium"></p>
                        </div>

                        {{-- Checkout CTA --}}
                        <button id="pageCheckoutBtn"
                            class="mt-5 w-full bg-green-800 text-white py-4 rounded-full font-bold text-base hover:bg-primary transition-all shadow-md shadow-green-100 active:scale-[.98] disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer">
                            Proceed to Checkout
                        </button>

                        {{-- Trust badges --}}
                        <div class="flex justify-center gap-5 mt-4">
                            <div class="flex items-center text-xs text-gray-400 gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Secure Checkout
                            </div>
                            <div class="flex items-center text-xs text-gray-400 gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Quality Guarantee
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

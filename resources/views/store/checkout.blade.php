@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <section class="bg-[#f0f5f1] min-h-screen">
        <div class="max-w-8xl mx-auto px-4 py-6 md:py-10">

            <x-page-header :breadcrumbs="[
                ['label' => 'Home', 'url' => route('shop')],
                ['label' => 'Cart', 'url' => route('cart.view')],
                ['label' => 'Checkout', 'url' => null],
            ]" />

            <form id="checkoutForm" action="{{ route('checkout.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

                    {{--    LEFT — Form   --}}
                    <div class="lg:col-span-2 space-y-5">

                        {{-- 1. Customer Information --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <div class="flex items-center gap-3 mb-5">
                                <div
                                    class="w-7 h-7 rounded-full bg-green-800 text-white flex items-center justify-center text-xs font-bold shrink-0">
                                    1</div>
                                <h3 class="text-lg font-bold text-gray-800">Customer Information</h3>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2 sm:grid sm:grid-cols-2 gap-4 flex flex-col">
                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Full
                                            Name <span class="text-red-400">*</span></label>
                                        <input id="co_name" name="customer_name" type="text"
                                            value="{{ auth()->user()?->name ?? '' }}" placeholder="Your full name"
                                            class="w-full rounded-xl border @error('customer_name') border-red-400 @else border-gray-200 @enderror px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-300 focus:border-green-400 transition-all">
                                        @error('customer_name')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Phone
                                            Number <span class="text-red-400">*</span></label>
                                        <input id="co_phone" name="customer_phone" type="tel"
                                            value="{{ auth()->user()?->phone ?? '' }}" placeholder="01XXXXXXXXX"
                                            class="w-full rounded-xl border @error('customer_phone') border-red-400 @else border-gray-200 @enderror px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-300 focus:border-green-400 transition-all">
                                        @error('customer_phone')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="sm:col-span-2 sm:grid sm:grid-cols-2 gap-4 flex flex-col">
                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Email
                                            <span class="text-gray-300 font-normal normal-case">(optional)</span></label>
                                        <input id="co_email" name="customer_email" type="email"
                                            value="{{ auth()->user()?->email ?? '' }}" placeholder="you@example.com"
                                            class="w-full rounded-xl border @error('customer_email') border-red-400 @else border-gray-200 @enderror px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-300 focus:border-green-400 transition-all">
                                        @error('customer_email')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">City
                                            <span class="text-red-400">*</span></label>
                                        <input id="co_city" type="text" name="city" placeholder="Dhaka"
                                            class="w-full rounded-xl border @error('city') border-red-400 @else border-gray-200 @enderror px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-300 focus:border-green-400 transition-all">
                                        @error('city')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                </div>

                                <div class="sm:col-span-2 sm:grid sm:grid-cols-2 gap-4 flex flex-col">
                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Delivery
                                            Address <span class="text-red-400">*</span></label>
                                        <textarea id="co_address" name="address_line" rows="2" placeholder="House no., road, area..."
                                            class="w-full rounded-xl border @error('customer_address') border-red-400 @else border-gray-200 @enderror px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-300 focus:border-green-400 transition-all resize-none"></textarea>
                                        @error('address_line')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Order
                                            Notes <span
                                                class="text-gray-300 font-normal normal-case">(optional)</span></label>
                                        <textarea id="co_notes" name="notes" rows="2" placeholder="Any special instructions…"
                                            class="w-full rounded-xl border @error('notes') border-red-400 @else border-gray-200 @enderror px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-300 focus:border-green-400 transition-all resize-none"></textarea>
                                        @error('notes')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>


                            </div>
                        </div>

                        {{-- 2. Delivery Zone --}}
                        <div id="zonesModule" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <div class="flex items-center gap-3 mb-5">
                                <div
                                    class="w-7 h-7 rounded-full bg-green-800 text-white flex items-center justify-center text-xs font-bold shrink-0">
                                    2</div>
                                <h3 class="text-lg font-bold text-gray-800">Delivery Zone</h3>
                            </div>

                            {{-- Skeleton loader --}}
                            <div id="zonesLoader" class="space-y-3 animate-pulse">
                                @foreach (range(1, 3) as $i)
                                    <div class="h-16 bg-gray-100 rounded-xl"></div>
                                @endforeach
                            </div>

                            <div id="shippingZones"
                                class="flex flex-col md:flex-row flex-1 items-center justify-between gap-4">
                                {{-- Rendered by CheckoutManager --}}
                            </div>
                        </div>

                        {{-- 3. Payment Method --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <div class="flex items-center gap-3 mb-5">
                                <div
                                    class="w-7 h-7 rounded-full bg-green-800 text-white flex items-center justify-center text-xs font-bold shrink-0">
                                    3</div>
                                <h3 class="text-lg font-bold text-gray-800">Payment Method</h3>
                            </div>

                            <div class="flex flex-col md:flex-row flex-1 items-center gap-4">
                                {{-- COD --}}
                                <label
                                    class="flex items-center gap-4 p-4 rounded-xl border-2 border-gray-100 cursor-pointer hover:border-green-300 transition-all has-checked:border-green-600 has-checked:bg-green-50">
                                    <input type="radio" name="payment_method" value="cod" checked
                                        class="accent-green-700 w-4 h-4 shrink-0">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-800 text-sm">Cash on Delivery</p>
                                            <p class="text-xs text-gray-400 mt-0.5">Pay when your order arrives</p>
                                        </div>
                                    </div>
                                </label>

                                {{-- SSL Commerz --}}
                                <label
                                    class="flex items-center gap-4 p-4 rounded-xl border-2 border-gray-100 cursor-pointer hover:border-green-300 transition-all has-checked:border-green-600 has-checked:bg-green-50">
                                    <input type="radio" name="payment_method" value="sslcommerz" disabled
                                        class="accent-green-700 w-4 h-4 shrink-0">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-800 text-sm">Online Payment <span
                                                    class="text-[10px] font-medium text-amber-600 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded-full ml-1">Coming
                                                    soon</span></p>
                                            <p class="text-xs text-gray-400 mt-0.5">bKash, Nagad, Cards via SSL Commerz</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- 4. Promo Code --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <div class="flex items-center gap-3 mb-5">
                                <div
                                    class="w-7 h-7 rounded-full bg-green-800 text-white flex items-center justify-center text-xs font-bold shrink-0">
                                    4</div>
                                <h3 class="text-lg font-bold text-gray-800">Promo Code</h3>
                            </div>

                            <div
                                class="flex items-center bg-gray-50 rounded-xl border border-gray-200  overflow-hidden focus-within:border-green-400 focus-within:ring-2 focus-within:ring-green-100 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600 ml-3 shrink-0"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <input id="co_coupon" type="text" placeholder="Enter promo code"
                                    class="bg-transparent flex-1 text-sm px-3 py-2.5 focus:outline-none text-gray-700 placeholder-gray-400 uppercase tracking-wider"
                                    autocomplete="off">
                                <button id="co_couponBtn" type="button"
                                    class="bg-green-800 text-white px-4 py-2.5 text-sm font-semibold hover:bg-green-900 transition-colors shrink-0">
                                    Apply
                                </button>
                            </div>
                            <p id="co_couponFeedback" class="hidden text-xs mt-2 font-medium"></p>
                        </div>

                    </div>

                    {{--   RIGHT — Order Summary (sticky)  --}}
                    <div class="lg:col-span-1 sticky top-8 space-y-4">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-bold text-gray-800">Order Summary</h3>
                                <span id="coItemCount" class="text-xs text-gray-400 font-medium"></span>
                            </div>

                            {{-- Items list --}}
                            <div id="coItemsList" class="mb-4">
                                {{-- Rendered by CheckoutManager --}}
                                <div class="space-y-3 animate-pulse">
                                    @foreach (range(1, 2) as $i)
                                        <div class="flex gap-3 py-3 border-b border-gray-100">
                                            <div class="w-12 h-12 rounded-lg bg-gray-100 shrink-0"></div>
                                            <div class="flex-1 space-y-1.5">
                                                <div class="h-3.5 bg-gray-100 rounded w-3/4"></div>
                                                <div class="h-3 bg-gray-100 rounded w-1/2"></div>
                                            </div>
                                            <div class="h-3.5 w-12 bg-gray-100 rounded shrink-0"></div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Totals --}}
                            <div class="space-y-2.5 pt-2 border-t border-gray-100">
                                <div class="flex justify-between text-sm text-gray-500">
                                    <span>Subtotal</span>
                                    <span id="coSubtotal" class="font-semibold text-gray-800 font-bengali">৳0</span>
                                </div>

                                <div id="coDiscountRow" class="hidden flex justify-between text-sm">
                                    <span class="text-gray-500">Coupon Discount</span>
                                    <span id="coDiscount" class="font-semibold text-green-600 font-bengali">−৳0</span>
                                </div>

                                <div class="flex justify-between text-sm text-gray-500">
                                    <span>Shipping</span>
                                    <span id="coShipping" class="font-semibold text-gray-800 font-bengali">Select
                                        zone</span>
                                </div>

                                <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                                    <span class="font-bold text-gray-800">Total</span>
                                    <span id="coTotal" class="text-2xl font-bold text-green-800 font-bengali">—</span>
                                </div>
                            </div>

                            {{-- Place Order --}}
                            <button id="placeOrderBtn" type="button"
                                class="mt-5 w-full bg-green-800 text-white py-4 rounded-full font-bold text-base hover:bg-green-900 transition-all shadow-md shadow-green-100 active:scale-[.98] disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span id="placeOrderLabel">Place Order</span>
                            </button>

                            {{-- Trust signals --}}
                            <div class="flex justify-center gap-5 mt-4">
                                <div class="flex items-center text-xs text-gray-400 gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Secure
                                </div>
                                <div class="flex items-center text-xs text-gray-400 gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Quality Guaranteed
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('cart.view') }}"
                            class="flex items-center justify-center gap-2 text-sm font-semibold text-green-700 hover:text-green-800 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to Cart
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

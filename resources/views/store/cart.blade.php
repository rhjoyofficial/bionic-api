@extends('layouts.app')

@section('title', 'Your Cart')

@section('content')
    <section class="bg-[#f0f5f1] min-h-screen">
        <div class="max-w-8xl mx-auto px-4 py-6 md:py-10">

            <x-page-header :breadcrumbs="[['label' => 'Home', 'url' => route('shop')], ['label' => 'Cart', 'url' => null]]" />

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                {{-- Left: Cart Items --}}
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 pb-0 flex justify-between items-end">
                        <h2 class="text-2xl font-bold text-gray-800">Shopping Cart</h2>
                        <span class="text-gray-400 text-sm font-medium">2 items</span>
                    </div>

                    <div id="pageCartItems" class="p-6 space-y-6">
                        {{-- Item 1 --}}
                        <div class="flex items-center gap-4 pb-6 border-b border-gray-100 last:border-0 last:pb-0">
                            <div class="w-24 h-24 rounded-xl bg-gray-50 overflow-hidden shrink-0">
                                <img src="path-to-your-image.jpg" alt="Medjool Dates" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between">
                                    <h3 class="font-bold text-gray-800 text-lg">Egyptian Medjool Large Dates</h3>
                                    <button class="text-red-500 hover:text-red-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-gray-400 text-sm mb-2">Size: 1 কেজি (1 Kg)</p>
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center justify-start gap-2">
                                        <span
                                            class="text-xl font-bold text-gray-900 font-bengali">{{ format_currency(1920) }}</span>
                                        <span
                                            class="line-through ml-2 font-bengali text-sm text-red-400 font-medium">{{ format_currency(2000) }}</span>
                                    </div>

                                    <div class="flex items-center border border-gray-200 rounded-lg">
                                        <button class="px-3 py-1 text-gray-500 hover:bg-gray-50">-</button>
                                        <input type="text" value="1"
                                            class="w-10 text-center border-x border-gray-200 py-1 text-sm font-semibold focus:outline-none">
                                        <button class="px-3 py-1 text-gray-500 hover:bg-gray-50">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Item 2 --}}
                        <div class="flex items-center gap-4 pb-6 border-b border-gray-100 last:border-0 last:pb-0">
                            <div class="w-24 h-24 rounded-xl bg-gray-50 overflow-hidden shrink-0">
                                <img src="path-to-your-image.jpg" alt="Medjool Dates" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between">
                                    <h3 class="font-bold text-gray-800 text-lg">Egyptian Medjool Large Dates</h3>
                                    <button class="text-red-500 hover:text-red-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-gray-400 text-sm mb-2">Size: 1 কেজি (1 Kg)</p>
                                <div class="flex justify-between items-center">
                                    <span
                                        class="text-xl font-bold text-gray-900 font-bengali">{{ format_currency(1920) }}</span>
                                    <div class="flex items-center border border-gray-200 rounded-lg">
                                        <button class="px-3 py-1 text-gray-500 hover:bg-gray-50">-</button>
                                        <input type="text" value="1"
                                            class="w-10 text-center border-x border-gray-200 py-1 text-sm font-semibold focus:outline-none">
                                        <button class="px-3 py-1 text-gray-500 hover:bg-gray-50">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border-t border-gray-50">
                        <a href="#"
                            class="inline-flex items-center text-green-700 font-semibold hover:text-green-800 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Continue Shopping
                        </a>
                    </div>
                </div>

                {{-- Right: Order Summary --}}
                <div class="lg:col-span-1 sticky top-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Order Summary</h2>

                        <div class="space-y-4">
                            <div class="flex justify-between text-gray-500 font-medium">
                                <span>Subtotal</span>
                                <span class="text-gray-900 font-bengali">{{ format_currency(1920) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-500 font-medium">
                                <span>Discount (-20%)</span>
                                <span class="text-red-500 font-bengali">- {{ format_currency(384) }}</span>
                            </div>

                            <hr class="border-gray-100 my-4">

                            <div class="flex justify-between items-center mb-6">
                                <span class="text-lg font-medium text-gray-800">Total</span>
                                <span
                                    class="text-3xl font-bold text-gray-900 font-bengali">{{ format_currency(2000) }}</span>
                            </div>

                            {{-- Promo Code Input --}}
                            <div class="flex items-center bg-gray-50 rounded-full px-4 py-2 border border-gray-100 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-700 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <input type="text" placeholder="Add promo code"
                                    class="bg-transparent flex-1 text-sm focus:outline-none text-gray-600">
                                <button
                                    class="bg-green-800 text-white px-5 py-1.5 rounded-full text-sm font-semibold hover:bg-green-900 transition-colors">Apply</button>
                            </div>

                            <button
                                class="w-full bg-green-800 text-white py-4 rounded-full font-bold text-lg hover:bg-green-900 transition-all shadow-md shadow-green-100">
                                Proceed to Checkout
                            </button>

                            <div class="flex justify-center gap-4 mt-4">
                                <div class="flex items-center text-xs text-gray-400">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Secure Checkout
                                </div>
                                <div class="flex items-center text-xs text-gray-400">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        </div>
    </section>
@endsection

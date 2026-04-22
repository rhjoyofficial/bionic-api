@extends('layouts.app')

@section('title', $landing->meta_title ?? $landing->title)
@section('meta_description', $landing->meta_description ?? 'বিশেষ অফারে অর্ডার করুন')

@section('content')
<section class="min-h-screen bg-gray-50 font-bengali">

    {{-- Hero --}}
    <div class="bg-gradient-to-br from-red-700 via-red-600 to-rose-500 text-white py-12 md:py-16 text-center px-4">
        @if($landing->hero_image)
            <img src="{{ asset('storage/' . $landing->hero_image) }}"
                 alt="{{ $landing->title }}"
                 class="max-w-xs w-full mx-auto mb-6 rounded-2xl shadow-2xl">
        @endif
        <h1 class="text-3xl md:text-5xl font-black mb-3">{{ $landing->title }}</h1>
        @if($landing->content)
            <p class="text-red-100 text-base max-w-2xl mx-auto">
                {{ Str::limit(strip_tags($landing->content), 200) }}
            </p>
        @endif
    </div>

    {{-- Body --}}
    <div class="max-w-6xl mx-auto px-4 py-12"
         data-lp-checkout
         data-lp-slug="{{ $landing->slug }}">

        <div class="grid lg:grid-cols-2 gap-10 items-start">

            {{-- LEFT: Product Picker --}}
            <div class="space-y-5">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 mb-1">পণ্য নির্বাচন করুন</h2>
                    <p class="text-gray-500 text-sm">আপনার পছন্দের পণ্যটি টিক দিন ও পরিমাণ নির্বাচন করুন</p>
                </div>

                <div class="space-y-3">
                    @foreach($salesItems as $item)
                        @php
                            $isVariant = $item->product_variant_id !== null;
                            $label = $isVariant
                                ? (($item->variant->product->name ?? '') . ($item->variant->title ? ' — ' . $item->variant->title : ''))
                                : ($item->combo->name ?? 'Combo');
                            $price = $isVariant ? ($item->variant->price ?? 0) : ($item->combo->price ?? 0);
                            $image = $isVariant
                                ? ($item->variant->product->thumbnail ?? null)
                                : ($item->combo->image ?? null);
                            $itemKey = $isVariant ? 'v_' . $item->product_variant_id : 'c_' . $item->combo_id;
                            $tierPrices = $isVariant ? ($item->variant->tierPrices ?? collect()) : collect();
                            $tierData = $tierPrices->sortBy('min_quantity')->map(fn($t) => [
                                'min_qty' => $t->min_quantity,
                                'price'   => $t->discount_type === 'percentage'
                                    ? round($price * (1 - $t->discount_value / 100), 2)
                                    : round($price - $t->discount_value, 2),
                            ])->values()->toArray();
                            $preselected = $item->is_preselected ? '1' : '0';
                        @endphp

                        <div data-lp-item-card
                             data-item-key="{{ $itemKey }}"
                             data-variant-id="{{ $item->product_variant_id ?? '' }}"
                             data-combo-id="{{ $item->combo_id ?? '' }}"
                             data-price="{{ $price }}"
                             data-tier-prices="{{ json_encode($tierData) }}"
                             data-preselected="{{ $preselected }}"
                             data-item-label="{{ addslashes($label) }}"
                             data-active-class="border-red-400 ring-2 ring-red-100"
                             class="bg-white rounded-2xl shadow-sm border transition-all cursor-pointer hover:shadow-md
                                    {{ $item->is_preselected ? 'border-red-400 ring-2 ring-red-100' : 'border-gray-100 hover:border-red-100' }}">

                            <div class="flex items-center gap-4 p-4">

                                {{-- Checkbox indicator --}}
                                <div data-lp-item-check
                                     data-active-class="bg-red-600 border-red-600"
                                     class="w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0 transition-all
                                            {{ $item->is_preselected ? 'bg-red-600 border-red-600' : 'border-gray-300 bg-white' }}">
                                    <svg style="{{ $item->is_preselected ? '' : 'display:none' }}"
                                         class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>

                                {{-- Image --}}
                                @if($image)
                                    <img src="{{ asset('storage/' . $image) }}"
                                         alt="{{ $label }}"
                                         class="w-16 h-16 rounded-xl object-cover flex-shrink-0 hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-16 h-16 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-gray-900 leading-tight text-sm">{{ $label }}</h3>
                                    <p class="text-red-600 font-black text-base mt-0.5 font-bengali">৳{{ number_format($price, 0) }}</p>
                                    @if($tierPrices->isNotEmpty())
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @foreach($tierPrices->sortBy('min_quantity') as $tier)
                                                @php
                                                    $computedTierPrice = $tier->discount_type === 'percentage'
                                                        ? round($price * (1 - $tier->discount_value / 100), 0)
                                                        : round($price - $tier->discount_value, 0);
                                                @endphp
                                                <span class="text-[10px] bg-red-50 text-red-600 border border-red-200 rounded-full px-2 py-0.5 font-semibold">
                                                    {{ $tier->min_quantity }}+&nbsp;→&nbsp;৳{{ number_format($computedTierPrice, 0) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                {{-- Per-item Qty stepper (hidden until selected) --}}
                                <div data-lp-qty-control
                                     style="{{ $item->is_preselected ? '' : 'display:none' }}"
                                     class="flex items-center gap-1.5 bg-red-50 rounded-xl p-1.5 flex-shrink-0">
                                    <button data-lp-qty-dec="{{ $itemKey }}" type="button"
                                            class="w-7 h-7 bg-white hover:bg-red-100 hover:text-red-600 rounded-lg font-bold text-gray-600 transition flex items-center justify-center text-sm">
                                        &minus;
                                    </button>
                                    <span data-lp-qty-display="{{ $itemKey }}"
                                          class="w-7 text-center font-black text-gray-800 text-sm">1</span>
                                    <button data-lp-qty-inc="{{ $itemKey }}" type="button"
                                            class="w-7 h-7 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold transition flex items-center justify-center text-sm">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Selected items list --}}
                <div data-lp-selected-container
                     style="{{ $salesItems->where('is_preselected', true)->count() > 0 ? '' : 'display:none' }}"
                     class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-2">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">নির্বাচিত পণ্য</p>
                    <div data-lp-selected-list class="space-y-1"></div>
                </div>

                {{-- Rules (desktop) --}}
                <div class="hidden md:block bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-2.5">
                    <h4 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                        <i class="fas fa-info-circle text-red-400"></i> অর্ডারের নিয়মাবলী
                    </h4>
                    <ul class="space-y-2 text-xs text-gray-600">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-truck text-red-400 mt-0.5 shrink-0"></i>
                            ক্যাশ অন ডেলিভারি — পণ্য হাতে পেয়ে পরিশোধ করুন।
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-shield-alt text-red-400 mt-0.5 shrink-0"></i>
                            ১০০% অরিজিনাল পণ্যের গ্যারান্টি।
                        </li>
                    </ul>
                </div>
            </div>

            {{-- RIGHT: Order Form --}}
            <div class="w-full">
                <div class="bg-white border border-red-100 rounded-3xl p-6 md:p-8 shadow-xl shadow-red-50/50">

                    <div class="text-center mb-7">
                        <h2 class="text-2xl font-bold text-gray-900">অর্ডার কনফার্ম করুন</h2>
                        <p class="text-red-600 text-xs italic mt-1.5">সঠিক তথ্য দিয়ে নিচের ফর্মটি পূরণ করুন</p>
                    </div>

                    <div class="space-y-4">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <input type="text" name="customer_name"
                                   placeholder="আপনার নাম *"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-red-400 focus:ring-2 focus:ring-red-100 outline-none text-sm transition-all">
                            <input type="tel" name="customer_phone"
                                   placeholder="মোবাইল নম্বর *"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-red-400 focus:ring-2 focus:ring-red-100 outline-none text-sm transition-all">
                        </div>

                        <input type="text" name="address_line"
                               placeholder="পূর্ণ ঠিকানা (বাসা, রোড, এলাকা, জেলা) *"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-red-400 focus:ring-2 focus:ring-red-100 outline-none text-sm transition-all">

                        {{-- Bulk CTA --}}
                        <div class="p-3.5 bg-amber-50 border border-dashed border-amber-300 rounded-xl flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span class="flex h-2.5 w-2.5 relative shrink-0">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                                </span>
                                <p class="text-xs text-amber-900">পাইকারি বা বেশি পরিমাণে নিতে চান?</p>
                            </div>
                            <a href="tel:01334943783" class="text-xs font-black text-red-600 hover:underline shrink-0">কল করুন</a>
                        </div>

                        {{-- Delivery Zone --}}
                        <div class="bg-red-50/60 p-4 rounded-2xl border border-red-100">
                            <p class="text-sm font-bold text-gray-700 mb-3">
                                ডেলিভারি এলাকা <span class="text-red-500">*</span>
                            </p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach($zones as $zone)
                                    <label data-lp-zone-label
                                           data-active-class="border-red-500 bg-red-50"
                                           class="flex flex-col items-center p-3 border-2 rounded-xl cursor-pointer text-center transition-all border-gray-200 bg-white hover:border-red-200">
                                        <input type="radio" name="zone" value="{{ $zone->id }}"
                                               data-lp-zone
                                               class="hidden">
                                        <span class="text-xs font-bold text-gray-800 leading-tight">{{ $zone->name }}</span>
                                        <span class="text-xs text-red-600 font-bold font-bengali mt-0.5">৳{{ number_format($zone->base_charge, 0) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Order Summary --}}
                        <div class="p-4 bg-gray-50 rounded-2xl border border-dashed border-gray-200 space-y-2 text-sm">
                            <div class="flex justify-between text-gray-500">
                                <span>পণ্যের মূল্য</span>
                                <span data-lp-display="subtotal" class="font-semibold text-gray-800">—</span>
                            </div>
                            <div data-lp-display-row="tier-discount" style="display:none"
                                 class="flex justify-between text-green-600">
                                <span>বাল্ক ডিসকাউন্ট</span>
                                <span data-lp-display="tier-discount" class="font-semibold">—</span>
                            </div>
                            <div class="flex justify-between text-gray-500">
                                <span>ডেলিভারি চার্জ</span>
                                <span data-lp-zone-note class="text-gray-400 italic text-xs">এলাকা নির্বাচন করুন</span>
                                <span data-lp-display="shipping" style="display:none"
                                      class="font-semibold text-gray-800 font-bengali"></span>
                            </div>
                            <div class="flex justify-between items-center border-t border-gray-200 pt-2">
                                <span class="font-black text-gray-800">সর্বমোট</span>
                                <span data-lp-display="total" class="text-xl font-black text-red-600 font-bengali">—</span>
                            </div>
                        </div>

                        {{-- Error --}}
                        <div data-lp-error style="display:none"
                             class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl p-3"></div>

                        {{-- No items warning --}}
                        <div data-lp-no-items
                             style="{{ $salesItems->where('is_preselected', true)->count() === 0 ? '' : 'display:none' }}"
                             class="text-center text-xs text-gray-400 py-1">
                            অর্ডার দিতে অন্তত একটি পণ্য নির্বাচন করুন
                        </div>

                        {{-- Submit --}}
                        <button data-lp-submit type="button"
                                class="w-full py-4 bg-red-600 hover:bg-red-700 text-white font-bold text-lg rounded-2xl shadow-lg transition-all active:scale-[.98] disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-3 cursor-pointer">
                            <svg data-lp-submit-spinner style="display:none"
                                 class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                 fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span data-lp-submit-label>অর্ডার কনফার্ম করুন</span>
                        </button>

                        {{-- Rules (mobile) --}}
                        <div class="md:hidden mt-2 pt-4 border-t border-gray-100 space-y-2">
                            <p class="flex items-start gap-2 text-xs text-gray-500">
                                <i class="fas fa-hand-holding-usd text-red-400 mt-0.5 shrink-0"></i>
                                ক্যাশ অন ডেলিভারি — পণ্য পেয়ে পরিশোধ করুন।
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Success Modal --}}
        <div data-lp-success-modal style="display:none"
             class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 px-4">
            <div class="bg-white p-8 rounded-3xl max-w-sm w-full text-center shadow-2xl border border-red-100">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-black text-gray-900 mb-2">অর্ডার সফল হয়েছে!</h2>
                <p class="text-gray-500 text-sm mb-6">আমাদের প্রতিনিধি শীঘ্রই আপনার সাথে যোগাযোগ করবেন।</p>
                <button onclick="window.location.href='/'"
                        class="w-full bg-red-600 text-white py-3 rounded-xl font-bold hover:bg-red-700 transition cursor-pointer">
                    ঠিক আছে
                </button>
            </div>
        </div>
    </div>

</section>
@endsection

@push('scripts')
    <script src="{{ asset('js/landing-checkout.js') }}"></script>
@endpush

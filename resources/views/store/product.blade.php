@extends('layouts.app')

@php
    $variants = $product->variants->map->toFrontend()->values();
    $initialVariant = $variants->first();
    $gallery = collect($product->gallery ?? [])
        ->filter()
        ->values();
    $mainImage = $product->image_url;
    $certifications = $product->certifications;
@endphp

@section('title', $product->name)
@push('styles')
    <style>
        .fire-bg {
            background: linear-gradient(120deg, #1d6327, #2d6a4f, #40916c, #1d6327);
            background-size: 300% 300%;
            animation: waveMove 4s ease-in-out infinite;
        }

        .fire-bg::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 20% 50%, rgba(64, 145, 108, 0.4), transparent 50%),
                radial-gradient(circle at 80% 50%, rgba(45, 106, 79, 0.3), transparent 50%);
            mix-blend-mode: overlay;
            animation: pulseGlow 2s infinite alternate;
        }

        @keyframes waveMove {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        @keyframes pulseGlow {
            0% {
                opacity: 0.6;
                transform: scale(1);
            }

            100% {
                opacity: 1;
                transform: scale(1.05);
            }
        }

        .star-glow {
            filter: drop-shadow(0 0 2px rgba(251, 191, 36, 0.4));
            animation: starPulse 2s infinite ease-in-out;
        }

        /* Staggered animation for stars */
        .star-glow:nth-child(2) {
            animation-delay: 0.2s;
        }

        .star-glow:nth-child(3) {
            animation-delay: 0.4s;
        }

        .star-glow:nth-child(4) {
            animation-delay: 0.6s;
        }

        .star-glow:nth-child(5) {
            animation-delay: 0.8s;
        }

        @keyframes starPulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.8;
                filter: drop-shadow(0 0 5px rgba(251, 191, 36, 0.8));
            }
        }

        /* Soft pulsing effect to attract attention */
        @keyframes softPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(29, 99, 39, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(29, 99, 39, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(29, 99, 39, 0);
            }
        }

        .animate-soft-pulse {
            animation: softPulse 2s infinite;
        }

        /* Shimmer light streak effect */
        .btn-shimmer::after {
            content: "";
            position: absolute;
            top: -50%;
            left: -60%;
            width: 20%;
            height: 200%;
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(30deg);
            transition: none;
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% {
                left: -60%;
            }

            20% {
                left: 120%;
            }

            100% {
                left: 120%;
            }
        }
    </style>
@endpush
@section('content')
    <section class="bg-[#f0f5f1] min-h-screen">
        <div class="max-w-8xl mx-auto px-4 py-6 md:py-10">
            <x-page-header :breadcrumbs="[['label' => 'Home', 'url' => route('shop')], ['label' => $product->name, 'url' => null]]" />

            {{-- Main Product Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-[2fr_3fr] gap-6 md:gap-10 lg:gap-x-16">
                {{-- Left: Gallery --}}
                <div class="space-y-4">
                    <div class="aspect-square rounded-2xl overflow-hidden border border-gray-200 bg-white shadow-sm">
                        <img id="productMainImage" src="{{ $mainImage }}" alt="{{ $product->name }}"
                            class="w-full h-full object-contain p-4">
                    </div>

                    @if ($gallery->isNotEmpty())
                        <div class="grid grid-cols-5 gap-3">
                            <button type="button"
                                class="thumbBtn aspect-square rounded-xl overflow-hidden border-2 border-primary p-1 bg-white shadow-sm"
                                data-src="{{ $mainImage }}">
                                <img src="{{ $mainImage }}" alt="thumbnail"
                                    class="w-full h-full object-cover rounded-lg">
                            </button>
                            @foreach ($gallery as $image)
                                <button type="button"
                                    class="thumbBtn aspect-square rounded-xl overflow-hidden border border-gray-200 p-1 bg-white hover:border-primary transition"
                                    data-src="{{ asset('storage/' . $image) }}">
                                    <img src="{{ asset('storage/' . $image) }}" alt="thumbnail"
                                        class="w-full h-full object-cover rounded-lg">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Right: Product Details --}}
                <div id="productDetailBox" data-variants='@json($variants)' class="space-y-5">

                    {{-- Category & Five Star --}}
                    <div class="flex items-center gap-4">
                        {{-- Category Badge --}}
                        <div class="relative inline-block px-3 py-1 rounded overflow-hidden fire-bg shrink-0">
                            <p class="relative z-10 text-sm font-medium text-white uppercase tracking-wide">
                                {{ $product->category?->name ?? 'Bionic' }}
                            </p>
                        </div>

                        {{-- Five Star Rating --}}
                        <div class="flex items-center gap-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="w-5 h-5 star-glow {{ $i <= ($product->rating ?? 5) ? 'text-amber-600' : 'text-gray-300' }}"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor

                            @if (isset($product->reviews_count))
                                <span class="text-xs text-gray-500 ml-1">({{ $product->reviews_count }})</span>
                            @endif
                        </div>
                    </div>

                    {{-- Product Name --}}
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ $product->name }}</h1>

                    {{-- Short Description --}}
                    @if ($product->short_description)
                        <p title="{{ $product->short_description }}"
                            class="text-gray-600 leading-snug line-clamp-2 truncate-2">{{ $product->short_description }}
                        </p>
                    @endif

                    {{-- Price --}}
                    <div class="flex items-center gap-3">
                        <span id="variantFinalPrice" class="text-2xl font-medium text-gray-900 font-bengali">
                            ৳{{ number_format($initialVariant['final_price'] ?? 0, 2) }}
                        </span>
                        <span id="variantOriginalPrice"
                            class="text-base text-gray-400 line-through {{ !($initialVariant['discount_percent'] ?? null) ? 'hidden' : '' }} font-bengali">
                            ৳{{ number_format($initialVariant['price'] ?? 0, 2) }}
                        </span>
                        <span id="variantDiscountBadge"
                            class="px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded {{ !($initialVariant['discount_percent'] ?? null) ? 'hidden' : '' }}">
                            -{{ $initialVariant['discount_percent'] ?? 0 }}%
                        </span>
                        {{-- Tier Prices (Bulk Offers) --}}
                        <div id="tierBox" class="text-sm"></div>
                    </div>

                    {{-- Variant Selector with Discount Badges --}}
                    <div class="flex flex-wrap items-center justify-start gap-4">
                        <label class="text-base font-semibold text-gray-700 mb-2 block">Select Variant:</label>

                        <div class="flex flex-wrap gap-3" id="variantCapsuleContainer">
                            @foreach ($product->variants as $variant)
                                @php
                                    $variantData = $variants->firstWhere('id', $variant->id);
                                    $hasDiscount = !empty($variantData['discount_percent']);
                                @endphp
                                <div class="relative">
                                    <button type="button" data-variant-id="{{ $variant->id }}"
                                        class="variant-btn px-5 py-2 rounded-lg border-2 text-sm font-semibold transition-all duration-200 select-none
                                            {{ $loop->first ? 'border-primary bg-primary text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-primary' }}">
                                        {{ $variant->title }}
                                    </button>
                                    @if ($hasDiscount)
                                        <span
                                            class="absolute -top-2 -right-2 px-1.5 py-0.5 text-[10px] font-bold bg-red-500 text-white rounded-lg shadow-sm">
                                            -{{ $variantData['discount_percent'] }}%
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        {{-- Hidden input to keep your existing JS logic working --}}
                        <input type="hidden" id="variantSelect" name="variant_id"
                            value="{{ $product->variants->first()?->id }}">
                    </div>

                    {{-- Key Value --}}
                    <div
                        class="flex items-center justify-start gap-3 text-primary font-semibold text-sm md:text-base tracking-wide">
                        <span>100% Natural</span>
                        <span class="text-primary/40 text-xl flex items-center">•</span>
                        <span>No Preservatives</span>
                        <span class="text-primary/40 text-xl flex items-center">•</span>
                        <span>Lab Tested</span>
                    </div>

                    {{-- Certifications --}}
                    @if ($certifications->isNotEmpty())
                        <div class="flex flex-wrap items-center gap-6 py-2">
                            @foreach ($certifications as $cert)
                                <div class="flex flex-col items-center gap-2">
                                    @if ($cert->logo_path)
                                        <img src="{{ asset($cert->logo_url) }}" alt="{{ $cert->name }}"
                                            class="h-16 w-16 aspect-square object-contain">
                                    @else
                                        <span class="text-xs font-medium text-gray-700">{{ $cert->name }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Stock & Quantity --}}
                    <div class="flex flex-wrap items-center justify-start gap-6 py-2">
                        <div>
                            <span class="text-sm text-gray-500">Stock:</span>
                            <span id="stockText"
                                class="ml-1 font-medium text-gray-900">{{ $initialVariant['available_stock'] ?? 0 }}
                                units</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500">Qty:</span>
                            {{-- Your exact Qty control preserved --}}
                            <div class="flex items-center border border-gray-200 overflow-hidden bg-white">
                                <button id="qtyMinus" type="button"
                                    class="cursor-pointer w-9 h-9 flex items-center justify-center text-gray-400 hover:bg-gray-50 hover:text-gray-700 transition-colors font-bold text-lg border-r border-gray-200">−</button>
                                <div id="qtyInput"
                                    class="w-10 h-9 flex items-center justify-center text-sm font-bold text-gray-800 select-none">
                                    1
                                </div>
                                <button id="qtyPlus" type="button"
                                    class="cursor-pointer w-9 h-9 flex items-center justify-center text-gray-400 hover:bg-gray-50 hover:text-gray-700 transition-colors font-bold text-lg border-l border-gray-200">+</button>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col md:flex-row md:items-center md:justify-center gap-4 mt-6">
                        {{-- Add to Cart with Shimmer and Pulse --}}
                        <button id="addToCartBtn" type="button"
                            class="addToCartBtn relative overflow-hidden w-full rounded-lg bg-primary text-white font-bold py-4 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-lg active:scale-95 cursor-pointer btn-shimmer animate-soft-pulse disabled:cursor-not-allowed disabled:opacity-50"
                            data-variant="{{ $initialVariant['id'] ?? '' }}">
                            <span class="relative z-10 flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                ADD TO CART
                            </span>
                        </button>

                        {{-- Buy Now with Sleek Hover --}}
                        <button id="buyNowBtn" type="button"
                            class="w-full rounded-lg bg-gray-900 text-white font-bold py-4 transition-all duration-300 transform hover:-translate-y-1 hover:bg-black hover:shadow-lg active:scale-95 cursor-pointer disabled:cursor-not-allowed disabled:opacity-50">
                            BUY NOW
                        </button>
                    </div>

                </div>
            </div>

            {{-- Product Description & Details --}}
            @if ($product->description || $product->nutritional_info)
                <div class="mt-12 bg-white rounded-2xl border border-gray-200 p-6 md:p-8 shadow-sm">
                    {{-- Tabs Navigation --}}
                    <div class="border-b border-gray-200 mb-6">
                        <nav class="flex space-x-8" aria-label="Tabs">
                            {{-- Tab: Description --}}
                            <button type="button" data-tab-target="description"
                                class="tab-btn border-b-2 border-primary text-primary px-1 pb-4 text-sm font-semibold whitespace-nowrap transition-all duration-300 cursor-pointer">
                                Description
                            </button>

                            {{-- Tab: Nutritional Info --}}
                            <button type="button" data-tab-target="nutrition"
                                class="tab-btn border-b-2 border-transparent text-gray-500 hover:text-primary hover:border-gray-300 px-1 pb-4 text-sm font-medium whitespace-nowrap transition-all duration-300 cursor-pointer">
                                Nutritional Info
                            </button>
                        </nav>
                    </div>

                    {{-- Content: Description --}}
                    <div id="description" class="tab-content block space-y-5 animate-fadeIn">
                        <p class="text-sm md:text-base text-gray-600 leading-relaxed max-w-7xl font-bengali">
                            {!! $product->description !!}
                        </p>
                    </div>

                    @if (!empty($product->nutritional_info))
                        <div id="nutrition" class="tab-content hidden space-y-5 animate-fadeIn">
                            <div class="max-w-md">
                                {{-- We pull the serving size from the JSON, or default to 100g --}}
                                <h3 class="text-sm md:text-base font-semibold text-gray-900 tracking-tight mb-4">
                                    Nutritional Facts (per {{ $product->nutritional_info['Serving Size'] ?? '100g' }})
                                </h3>

                                <div class="divide-y divide-gray-100">
                                    @foreach ($product->nutritional_info as $label => $value)
                                        {{-- Skip the 'Serving Size' key since we used it in the header above --}}
                                        @if ($label !== 'Serving Size' && !empty($value))
                                            <div class="flex justify-between py-2">
                                                <span class="text-gray-600">{{ $label }}</span>
                                                <span class="font-bold text-primary">{{ $value }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                <p class="text-xs text-gray-400 mt-4 italic">* Percent Daily Values are based on a 2,000
                                    calorie diet.</p>
                            </div>
                        </div>
                    @else
                        <div id="nutrition" class="tab-content hidden space-y-5 animate-fadeIn">
                            <div
                                class="max-w-lg py-8 text-center text-gray-500 bg-gray-50 rounded-lg border border-gray-100">
                                Nutritional information is currently unavailable for this product.
                            </div>
                        </div>
                    @endif
                </div>
            @endif
            {{-- Related Products --}}
            @if ($relatedProducts->isNotEmpty())
                <div class="mt-12">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-1.5 h-7 bg-primary rounded-full"></span>
                            You May Also Like
                        </h2>
                        <div class="flex items-center gap-2">
                            <button id="relatedPrev"
                                class="w-10 h-10 rounded-full border border-gray-200 bg-white hover:bg-gray-100 text-gray-600 text-xl shadow-sm">‹</button>
                            <button id="relatedNext"
                                class="w-10 h-10 rounded-full border border-gray-200 bg-white hover:bg-gray-100 text-gray-600 text-xl shadow-sm">›</button>
                        </div>
                    </div>

                    <div id="relatedCarousel"
                        class="grid grid-flow-col auto-cols-[85%] sm:auto-cols-[45%] lg:auto-cols-[31%] gap-5 overflow-x-auto snap-x snap-mandatory pb-4 no-scrollbar scroll-smooth">
                        @foreach ($relatedProducts as $related)
                            <div class="snap-start">
                                <x-product-card :product="$related" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const detailBox = document.getElementById('productDetailBox');
                const variants = JSON.parse(detailBox?.dataset.variants || '[]');
                if (!variants.length) return;

                const variantBtns = document.querySelectorAll('.variant-btn');
                const variantSelect = document.getElementById('variantSelect');
                const finalPrice = document.getElementById('variantFinalPrice');
                const originalPrice = document.getElementById('variantOriginalPrice');
                const discountBadge = document.getElementById('variantDiscountBadge');
                const stockText = document.getElementById('stockText');
                const tierBox = document.getElementById('tierBox');
                const qtyDisplay = document.getElementById('qtyInput');
                const addToCartBtn = document.getElementById('addToCartBtn');
                const buyNowBtn = document.getElementById('buyNowBtn');
                const qtyMinus = document.getElementById('qtyMinus');
                const qtyPlus = document.getElementById('qtyPlus');

                function activeVariant() {
                    return variants.find(v => String(v.id) === String(variantSelect.value)) || variants[0];
                }

                function updateQtyBoundaries() {
                    const v = activeVariant();
                    const max = Math.max(1, Number(v.available_stock || 1));
                    let q = Number(qtyDisplay.textContent.trim() || 1);
                    if (isNaN(q) || q < 1) q = 1;
                    if (q > max) q = max;
                    qtyDisplay.textContent = q;
                }

                function renderTierInfo(v) {
                    if (!v.tiers?.length) {
                        tierBox.innerHTML = '';
                        return;
                    }
                    tierBox.innerHTML = `
                <div class="flex flex-wrap gap-2">
                    ${v.tiers.map(t => `<span class="inline-flex items-center rounded-full bg-green-50 text-green-700 px-3 py-1.5 text-xs font-semibold border border-green-200 font-bengali">Buy ${t.qty}+ → Save ${t.type === 'percentage' ? t.value + '%' : '৳' + t.value}/unit</span>`).join('')}
                </div>
            `;
                }

                function renderVariant() {
                    const v = activeVariant();
                    finalPrice.textContent = `৳${Number(v.final_price).toFixed(2)}`;
                    originalPrice.textContent = `৳${Number(v.price).toFixed(2)}`;
                    stockText.textContent = `${v.available_stock} units`;
                    addToCartBtn.dataset.variant = v.id;

                    if (v.discount_percent) {
                        originalPrice.classList.remove('hidden');
                        discountBadge.classList.remove('hidden');
                        discountBadge.textContent = `-${v.discount_percent}%`;
                    } else {
                        originalPrice.classList.add('hidden');
                        discountBadge.classList.add('hidden');
                    }

                    const outOfStock = Number(v.available_stock) <= 0;
                    addToCartBtn.disabled = outOfStock;
                    buyNowBtn.disabled = outOfStock;
                    addToCartBtn.classList.toggle('opacity-50', outOfStock);
                    addToCartBtn.classList.toggle('pointer-events-none', outOfStock);
                    buyNowBtn.classList.toggle('opacity-50', outOfStock);
                    buyNowBtn.classList.toggle('pointer-events-none', outOfStock);

                    renderTierInfo(v);
                    updateQtyBoundaries();

                    // Update variant buttons styling
                    variantBtns.forEach(btn => {
                        const isActive = btn.dataset.variantId === String(v.id);
                        if (isActive) {
                            btn.classList.remove('border-gray-200', 'bg-white', 'text-gray-600');
                            btn.classList.add('border-primary', 'bg-primary', 'text-white');
                        } else {
                            btn.classList.remove('border-primary', 'bg-primary', 'text-white');
                            btn.classList.add('border-gray-200', 'bg-white', 'text-gray-600');
                        }
                    });
                }

                // Variant button click handler
                variantBtns.forEach(btn => {
                    btn.addEventListener('click', () => {
                        const variantId = btn.dataset.variantId;
                        variantSelect.value = variantId;
                        renderVariant();
                    });
                });

                // Qty controls - using textContent as per your design
                qtyMinus?.addEventListener('click', () => {
                    let qty = Number(qtyDisplay.textContent.trim() || 1);
                    qtyDisplay.textContent = Math.max(1, qty - 1);
                });

                qtyPlus?.addEventListener('click', () => {
                    const v = activeVariant();
                    let qty = Number(qtyDisplay.textContent.trim() || 1);
                    qtyDisplay.textContent = Math.min(Number(v.available_stock || 1), qty + 1);
                });

                // Manual input prevention (since it's a div, not input)
                qtyDisplay?.addEventListener('blur', updateQtyBoundaries);

                variantSelect?.addEventListener('change', renderVariant);

                // Cart actions
                addToCartBtn?.addEventListener('click', async () => {
                    const variantId = addToCartBtn.dataset.variant;
                    const qty = Math.max(1, Number(qtyDisplay.textContent.trim() || 1));
                    await window.Cart?.add(variantId, qty, addToCartBtn);
                });

                buyNowBtn?.addEventListener('click', async () => {
                    const variantId = addToCartBtn.dataset.variant;
                    const qty = Math.max(1, Number(qtyDisplay.textContent.trim() || 1));
                    await window.Cart?.add(variantId, qty, buyNowBtn);
                    window.location.href = '/checkout';
                });

                // Initialize
                renderVariant();
            });

            document.addEventListener('DOMContentLoaded', function() {
                const tabBtns = document.querySelectorAll('.tab-btn');
                const tabContents = document.querySelectorAll('.tab-content');

                tabBtns.forEach(btn => {
                    btn.addEventListener('click', () => {
                        const target = btn.getAttribute('data-tab-target');

                        // 1. Remove active styles from all buttons
                        tabBtns.forEach(b => {
                            b.classList.remove('border-primary', 'text-primary',
                                'font-semibold');
                            b.classList.add('border-transparent', 'text-gray-500',
                                'font-medium');
                        });

                        // 2. Add active styles to clicked button
                        btn.classList.add('border-primary', 'text-primary', 'font-semibold');
                        btn.classList.remove('border-transparent', 'text-gray-500', 'font-medium');

                        // 3. Hide all content sections
                        tabContents.forEach(content => {
                            content.classList.add('hidden');
                            content.classList.remove('block');
                        });

                        // 4. Show target content section
                        document.getElementById(target).classList.remove('hidden');
                        document.getElementById(target).classList.add('block');
                    });
                });
            });
        </script>
    @endpush
@endsection

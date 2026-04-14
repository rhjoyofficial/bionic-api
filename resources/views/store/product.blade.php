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
            background: linear-gradient(120deg, #ff4d00, #ff9900, #ff1a00);
            background-size: 200% 200%;
            animation: flameMove 3s ease-in-out infinite;
        }

        .fire-bg::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.3), transparent 40%),
                radial-gradient(circle at 80% 50%, rgba(255, 255, 255, 0.2), transparent 40%);
            mix-blend-mode: overlay;
            animation: flameFlicker 1.5s infinite alternate;
        }

        @keyframes flameMove {
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

        @keyframes flameFlicker {
            0% {
                opacity: 0.6;
                transform: scale(1);
            }

            100% {
                opacity: 1;
                transform: scale(1.05);
            }
        }
    </style>
@endpush
@section('content')
    <section class="bg-[#f0f5f1] min-h-screen">
        <div class="max-w-8xl mx-auto px-4 py-6 md:py-10">
            <x-page-header :breadcrumbs="[['label' => 'Home', 'url' => route('shop')], ['label' => $product->name, 'url' => null]]" />

            {{-- Main Product Section --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
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
                    {{-- Category --}}
                    <div class="relative inline-block px-3 py-1 rounded overflow-hidden fire-bg">
                        <p class="relative z-10 text-sm font-medium text-white uppercase tracking-wide">
                            {{ $product->category?->name ?? 'Uncategorized' }}
                        </p>
                    </div>

                    {{-- Product Name --}}
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ $product->name }}</h1>

                    {{-- Short Description --}}
                    @if ($product->short_description)
                        <p class="text-gray-600">{{ $product->short_description }}</p>
                    @endif

                    {{-- Price --}}
                    <div class="flex items-center gap-3">
                        <span id="variantFinalPrice" class="text-2xl font-bold text-gray-900 font-bengali">
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
                    </div>

                    {{-- Tier Prices (Bulk Offers) --}}
                    <div id="tierBox" class="text-sm"></div>

                    {{-- Variant Selector --}}
                    @if ($product->variants->count() > 1)
                        <div>
                            <label class="text-sm font-medium text-gray-700 mb-1.5 block">Size / Variant</label>
                            <select id="variantSelect"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 bg-white focus:border-primary focus:ring-1 focus:ring-primary font-bengali">
                                @foreach ($product->variants as $variant)
                                    <option value="{{ $variant->id }}">
                                        {{ $variant->title }} — ৳{{ number_format($variant->final_price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input id="variantSelect" type="hidden" value="{{ $product->variants->first()?->id }}">
                    @endif

                    {{-- Stock & Quantity --}}
                    <div class="flex items-center justify-between py-2">
                        <div>
                            <span class="text-sm text-gray-500">Stock:</span>
                            <span id="stockText"
                                class="ml-1 font-medium text-gray-900">{{ $initialVariant['available_stock'] ?? 0 }}
                                units</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500">Qty:</span>
                            <div class="flex items-center">
                                <button id="qtyMinus" type="button"
                                    class="w-8 h-8 rounded-l border border-gray-300 bg-white hover:bg-gray-50">−</button>
                                <input id="qtyInput" type="number" min="1" value="1"
                                    class="w-12 h-8 text-center border-y border-gray-300 bg-white text-sm">
                                <button id="qtyPlus" type="button"
                                    class="w-8 h-8 rounded-r border border-gray-300 bg-white hover:bg-gray-50">+</button>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col md:flex-row md:items-center md:justify-center gap-3">
                        <button id="addToCartBtn" type="button"
                            class="addToCartBtn w-full rounded-lg bg-primary text-white font-medium py-3 hover:bg-primary/90 transition"
                            data-variant="{{ $initialVariant['id'] ?? '' }}">
                            Add to Cart
                        </button>
                        <button id="buyNowBtn" type="button"
                            class="w-full rounded-lg bg-gray-900 text-white font-medium py-3 hover:bg-black transition">
                            Buy Now
                        </button>
                    </div>

                    {{-- Certifications --}}
                    @if ($certifications->isNotEmpty())
                        <div class="border-t border-gray-200 pt-6 mt-4">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Quality Certifications</h3>
                            <div class="flex flex-wrap items-center gap-4">
                                @foreach ($certifications as $cert)
                                    <div class="flex flex-col items-center gap-2 bg-gray-100 rounded-full pl-2 pr-4 py-1.5">
                                        @if ($cert->logo_path)
                                            <img src="{{ asset('storage/' . $cert->logo_path) }}"
                                                alt="{{ $cert->name }}"
                                                class="h-12 w-12 object-contain rounded-full bg-white p-0.5">
                                        @endif
                                        <span class="text-xs font-medium text-gray-700">{{ $cert->name }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Product Description & Details --}}
            @if ($product->description)
                <div class="mt-12 bg-white rounded-2xl border border-gray-200 p-6 md:p-8 shadow-sm">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-1 h-6 bg-primary rounded-full"></span>
                        Product Details
                    </h2>
                    <div class="prose prose-gray max-w-none text-gray-700 leading-relaxed">
                        {!! nl2br(e($product->description)) !!}
                    </div>
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

                const variantSelect = document.getElementById('variantSelect');
                const finalPrice = document.getElementById('variantFinalPrice');
                const originalPrice = document.getElementById('variantOriginalPrice');
                const discountBadge = document.getElementById('variantDiscountBadge');
                const stockText = document.getElementById('stockText');
                const tierBox = document.getElementById('tierBox');
                const qtyInput = document.getElementById('qtyInput');
                const addToCartBtn = document.getElementById('addToCartBtn');
                const buyNowBtn = document.getElementById('buyNowBtn');
                const qtyMinus = document.getElementById('qtyMinus');
                const qtyPlus = document.getElementById('qtyPlus');

                // New UI elements
                const variantBtns = document.querySelectorAll('.variant-radio-btn');

                function activeVariant() {
                    return variants.find(v => String(v.id) === String(variantSelect.value)) || variants[0];
                }

                function updateQtyBoundaries() {
                    const v = activeVariant();
                    const max = Math.max(1, Number(v.available_stock || 1));
                    let q = Number(qtyInput.value || 1);
                    if (q < 1) q = 1;
                    if (q > max) q = max;
                    qtyInput.value = q;
                }

                function renderTierInfo(v) {
                    if (!v.tiers?.length) {
                        tierBox.innerHTML = '';
                        return;
                    }
                    tierBox.innerHTML = `
                <p class="text-sm font-semibold text-gray-700 mb-2">Bulk Savings</p>
                <div class="flex flex-wrap gap-2">
                    ${v.tiers.map(t => `<span class="inline-flex items-center rounded-full bg-green-50 text-green-700 px-3 py-1.5 text-xs font-semibold border border-green-200 font-bengali">Buy ${t.qty}+ → Save ${t.type === 'percentage' ? t.value + '%' : '৳' + t.value}</span>`).join('')}
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
                        discountBadge.textContent = `Save ${v.discount_percent}%`;
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

                    // Update the new UI variant buttons styling
                    variantBtns.forEach(btn => {
                        const isActive = btn.dataset.variantId === String(v.id);
                        if (isActive) {
                            btn.classList.add('border-primary', 'bg-primary/5', 'ring-1', 'ring-primary');
                            btn.classList.remove('border-gray-200', 'bg-white');
                        } else {
                            btn.classList.remove('border-primary', 'bg-primary/5', 'ring-1', 'ring-primary');
                            btn.classList.add('border-gray-200', 'bg-white');
                        }
                    });
                }

                // Event Listeners for new variant buttons
                variantBtns.forEach(btn => {
                    btn.addEventListener('click', () => {
                        const variantId = btn.dataset.variantId;
                        variantSelect.value = variantId;
                        renderVariant();
                    });
                });

                qtyMinus?.addEventListener('click', () => {
                    qtyInput.value = Math.max(1, Number(qtyInput.value || 1) - 1);
                });

                qtyPlus?.addEventListener('click', () => {
                    const v = activeVariant();
                    qtyInput.value = Math.min(Number(v.available_stock || 1), Number(qtyInput.value || 1) + 1);
                });

                qtyInput?.addEventListener('input', updateQtyBoundaries);
                variantSelect?.addEventListener('change', renderVariant);

                addToCartBtn?.addEventListener('click', async () => {
                    const variantId = addToCartBtn.dataset.variant;
                    const qty = Math.max(1, Number(qtyInput.value || 1));
                    await window.Cart?.add(variantId, qty, addToCartBtn);
                });

                buyNowBtn?.addEventListener('click', async () => {
                    const variantId = addToCartBtn.dataset.variant;
                    const qty = Math.max(1, Number(qtyInput.value || 1));
                    await window.Cart?.add(variantId, qty, buyNowBtn);
                    window.location.href = '/checkout';
                });

                // Thumbnail Gallery
                document.querySelectorAll('.thumbBtn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        document.getElementById('productMainImage').src = btn.dataset.src;
                        document.querySelectorAll('.thumbBtn').forEach(b => b.classList.remove(
                            'border-primary', 'border-2'));
                        btn.classList.add('border-primary', 'border-2');
                    });
                });

                // Related Carousel
                const carousel = document.getElementById('relatedCarousel');
                document.getElementById('relatedPrev')?.addEventListener('click', () => {
                    carousel?.scrollBy({
                        left: -360,
                        behavior: 'smooth'
                    });
                });
                document.getElementById('relatedNext')?.addEventListener('click', () => {
                    carousel?.scrollBy({
                        left: 360,
                        behavior: 'smooth'
                    });
                });

                renderVariant();
            });
        </script>
    @endpush
@endsection

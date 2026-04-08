@extends('layouts.app')

@php
    $variants = $product->variants->map->toFrontend()->values();
    $initialVariant = $variants->first();
    $gallery = collect($product->gallery ?? [])
        ->filter()
        ->values();
    $mainImage = $product->image_url;
@endphp

@section('title', $product->name)

@section('content')

    <section class="max-w-7xl mx-auto px-4 md:px-8 py-8 md:py-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 bg-white p-5 md:p-8 rounded-3xl border border-gray-100 shadow-sm">
            <div>
                <div class="aspect-square rounded-2xl overflow-hidden border border-gray-100 bg-gray-50">
                    <img id="productMainImage" src="{{ $mainImage }}" alt="{{ $product->name }}"
                        class="w-full h-full object-cover">
                </div>

                @if ($gallery->isNotEmpty())
                    <div class="grid grid-cols-5 gap-2 mt-3">
                        <button type="button"
                            class="thumbBtn border border-primary rounded-xl p-1.5 overflow-hidden bg-white ring-2 ring-primary"
                            data-src="{{ $mainImage }}">
                            <img src="{{ $mainImage }}" alt="thumbnail" class="w-full h-14 object-cover rounded-lg">
                        </button>
                        @foreach ($gallery as $image)
                            <button type="button"
                                class="thumbBtn border border-gray-200 rounded-xl p-1.5 overflow-hidden bg-white"
                                data-src="{{ asset('storage/' . $image) }}">
                                <img src="{{ asset('storage/' . $image) }}" alt="thumbnail"
                                    class="w-full h-14 object-cover rounded-lg">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div id="productDetailBox" data-variants='@json($variants)' class="space-y-5">
                <p class="text-xs uppercase tracking-wider text-primary font-semibold">
                    {{ $product->category?->name ?? 'Product' }}</p>
                <h1 class="text-2xl md:text-4xl font-extrabold text-gray-900 leading-tight">{{ $product->name }}</h1>

                <p class="text-gray-600 leading-relaxed">{{ $product->short_description }}</p>

                <div class="flex items-baseline gap-3 font-bengali">
                    <p id="variantFinalPrice" class="text-3xl font-black text-gray-900">
                        ৳{{ number_format($initialVariant['final_price'] ?? 0, 2) }}
                    </p>
                    <p id="variantOriginalPrice"
                        class="text-lg text-gray-400 line-through {{ !($initialVariant['discount_percent'] ?? null) ? 'hidden' : '' }}">
                        ৳{{ number_format($initialVariant['price'] ?? 0, 2) }}
                    </p>
                    <span id="variantDiscountBadge"
                        class="px-2 py-1 text-xs rounded-md bg-red-100 text-red-700 {{ !($initialVariant['discount_percent'] ?? null) ? 'hidden' : '' }}">
                        {{ $initialVariant['discount_percent'] ?? 0 }}% OFF
                    </span>
                </div>

                @if ($product->variants->count() > 1)
                    <div>
                        <label class="text-sm font-semibold text-gray-700 mb-2 block">Choose Variant</label>
                        <select id="variantSelect"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 bg-white focus:outline-none focus:ring-2 focus:ring-green-600">
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

                <div class="flex items-center justify-between bg-gray-50 border border-gray-100 rounded-2xl p-4">
                    <div>
                        <p class="text-xs uppercase text-gray-500">Available Stock</p>
                        <p id="stockText" class="font-bold text-gray-900">{{ $initialVariant['available_stock'] ?? 0 }}
                            units</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-gray-500 text-right">Quantity</p>
                        <div class="flex items-center gap-2 mt-1">
                            <button id="qtyMinus" type="button"
                                class="w-9 h-9 rounded-lg border border-gray-200 bg-white hover:bg-gray-100 font-bold">−</button>
                            <input id="qtyInput" type="number" min="1" value="1"
                                class="w-16 text-center rounded-lg border border-gray-200 py-1.5">
                            <button id="qtyPlus" type="button"
                                class="w-9 h-9 rounded-lg border border-gray-200 bg-white hover:bg-gray-100 font-bold">+</button>
                        </div>
                    </div>
                </div>

                <div id="tierBox" class="space-y-2"></div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <button id="addToCartBtn" type="button"
                        class="addToCartBtn w-full rounded-xl bg-primary text-white font-semibold py-3 hover:opacity-90 transition"
                        data-variant="{{ $initialVariant['id'] ?? '' }}">
                        Add to Cart
                    </button>
                    <button id="buyNowBtn" type="button"
                        class="w-full rounded-xl bg-gray-900 text-white font-semibold py-3 hover:bg-black transition">
                        Buy Now
                    </button>
                </div>
            </div>
        </div>

        @if ($product->description)
            <div class="mt-8 bg-white p-6 md:p-8 rounded-3xl border border-gray-100 shadow-sm">
                <h2 class="text-xl font-bold text-gray-900 mb-3">Product Details</h2>
                <div class="prose max-w-none text-gray-700 leading-relaxed">
                    {!! nl2br(e($product->description)) !!}
                </div>
            </div>
        @endif

        @if ($relatedProducts->isNotEmpty())
            <div class="mt-10">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-bold text-gray-900">Related Products</h2>
                    <div class="flex items-center gap-2">
                        <button id="relatedPrev"
                            class="w-9 h-9 rounded-full border border-gray-200 bg-white hover:bg-gray-100">‹</button>
                        <button id="relatedNext"
                            class="w-9 h-9 rounded-full border border-gray-200 bg-white hover:bg-gray-100">›</button>
                    </div>
                </div>

                <div id="relatedCarousel"
                    class="grid grid-flow-col auto-cols-[75%] sm:auto-cols-[48%] lg:auto-cols-[31%] gap-4 overflow-x-auto snap-x snap-mandatory pb-2 no-scrollbar">
                    @foreach ($relatedProducts as $related)
                        <div class="snap-start">
                            <x-product-card :product="$related" />
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endsection

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

            function activeVariant() {
                return variants.find(v => String(v.id) === String(variantSelect.value)) ||
                    variants[0];
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
                    <p class="text-sm font-semibold text-gray-700">Bulk Offers</p>
                    <div class="flex flex-wrap gap-2">
                        ${v.tiers.map(t => `<span class="inline-flex items-center rounded-full bg-green-50 text-green-700 px-3 py-1 text-xs font-semibold">Buy ${t.qty}+ → Save ${t.type === 'percentage' ? t.value + '%' : '৳' + t.value}</span>`).join('')}
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
                    discountBadge.textContent = `${v.discount_percent}% OFF`;
                } else {
                    originalPrice.classList.add('hidden');
                    discountBadge.classList.add('hidden');
                }

                const outOfStock = Number(v.available_stock) <= 0;
                addToCartBtn.disabled = outOfStock;
                buyNowBtn.disabled = outOfStock;
                addToCartBtn.classList.toggle('opacity-50', outOfStock);
                buyNowBtn.classList.toggle('opacity-50', outOfStock);

                renderTierInfo(v);
                updateQtyBoundaries();
            }

            qtyMinus?.addEventListener('click', () => {
                qtyInput.value = Math.max(1, Number(qtyInput.value || 1) - 1);
            });

            qtyPlus?.addEventListener('click', () => {
                const v = activeVariant();
                qtyInput.value = Math.min(Number(v.available_stock || 1), Number(
                    qtyInput.value || 1) + 1);
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

            document.querySelectorAll('.thumbBtn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('productMainImage').src = btn
                        .dataset.src;
                    document.querySelectorAll('.thumbBtn').forEach(b => b
                        .classList.remove('ring-2', 'ring-primary',
                            'border-primary'));
                    btn.classList.add('ring-2', 'ring-primary',
                        'border-primary');
                });
            });

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

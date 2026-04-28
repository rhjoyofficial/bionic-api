@extends('layouts.app')

@section('title', $landing->meta_title ?? $product->name)
@section('meta_description', $landing->meta_description ?? $product->short_description)

@section('content')
    <section class="bg-[#f0f5f1] min-h-screen" x-data="productLanding()" x-init="init()">

        {{-- Hero Section --}}
        <div class="relative bg-linear-to-br from-green-900 via-green-800 to-green-700 text-white overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 py-12 md:py-20">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
                    {{-- Product Image --}}
                    <div class="flex justify-center">
                        @if ($landing->hero_image)
                            <img src="{{ asset('storage/' . $landing->hero_image) }}" alt="{{ $product->name }}"
                                class="max-w-sm w-full rounded-2xl shadow-2xl">
                        @elseif($product->thumbnail)
                            <img src="{{ asset('storage/' . $product->thumbnail) }}" alt="{{ $product->name }}"
                                class="max-w-sm w-full rounded-2xl shadow-2xl">
                        @endif
                    </div>

                    {{-- Product Info --}}
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold mb-4">
                            {{ $landing->title ?? $product->name }}
                        </h1>
                        @if ($product->short_description)
                            <p class="text-green-100 text-lg mb-6">{{ $product->short_description }}</p>
                        @endif

                        {{-- Variant Selector --}}
                        @if ($product->variants->count() > 1)
                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-green-200 mb-2">Select Variant</label>
                                <div class="flex flex-wrap gap-3">
                                    @foreach ($product->variants as $variant)
                                        <button type="button"
                                            @click="selectVariant({{ $variant->id }}, {{ $variant->price }}, @json($variant->tierPrices->map(fn($t) => ['min_qty' => $t->min_qty, 'price' => $t->price])->values()))"
                                            :class="selectedVariantId === {{ $variant->id }} ?
                                                'bg-white text-green-800 ring-2 ring-white' :
                                                'bg-green-700/50 text-white hover:bg-green-600/50'"
                                            class="px-4 py-2 rounded-xl text-sm font-semibold transition-all">
                                            {{ $variant->name ?? $variant->sku }}
                                            &mdash; <span
                                                class="font-bengali">&#2547;{{ number_format($variant->price, 0) }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Tier price hints for selected variant --}}
                        <template x-if="tierPrices.length > 0">
                            <div class="flex flex-wrap gap-1.5 mb-4">
                                <template x-for="tier in tierPrices" :key="tier.min_qty">
                                    <span
                                        class="text-xs bg-white/20 text-white border border-white/30 rounded-full px-2.5 py-0.5 font-semibold"
                                        x-text="tier.min_qty + '+ → ৳' + tier.price"></span>
                                </template>
                            </div>
                        </template>

                        {{-- Quantity --}}
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-green-200 mb-2">Quantity</label>
                            <div class="flex items-center gap-3">
                                <button @click="changeQty(-1)" type="button"
                                    class="w-10 h-10 rounded-full bg-green-700/50 hover:bg-green-600/50 flex items-center justify-center text-white text-xl font-bold transition-all">
                                    &minus;
                                </button>
                                <span class="text-2xl font-bold w-12 text-center" x-text="quantity"></span>
                                <button @click="changeQty(1)" type="button"
                                    class="w-10 h-10 rounded-full bg-green-700/50 hover:bg-green-600/50 flex items-center justify-center text-white text-xl font-bold transition-all">
                                    +
                                </button>
                            </div>
                        </div>

                        {{-- Price Display --}}
                        <div class="flex items-baseline gap-3 flex-wrap">
                            <span class="text-3xl font-bold font-bengali"
                                x-text="'৳' + (effectivePrice() * quantity).toFixed(0)"></span>
                            <span x-show="effectivePrice() < unitPrice"
                                class="text-green-300 text-base line-through font-bengali"
                                x-text="'৳' + (unitPrice * quantity).toFixed(0)"></span>
                            <span class="text-green-200 text-sm" x-show="quantity > 1"
                                x-text="'(' + quantity + ' × ৳' + effectivePrice().toFixed(0) + ' each)'"></span>
                        </div>

                        {{-- Scroll to checkout CTA --}}
                        <a href="#landingCheckout"
                            class="mt-6 inline-flex items-center gap-2 bg-white text-green-800 px-8 py-3 rounded-full font-bold text-base hover:bg-green-50 transition-all shadow-lg">
                            Order Now
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Content Section (from CMS) --}}
        @if ($landing->content)
            <div class="max-w-4xl mx-auto px-4 py-12">
                <div class="prose prose-green max-w-none bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                    {!! $landing->content !!}
                </div>
            </div>
        @endif

        {{-- Checkout Section --}}
        <div class="max-w-2xl mx-auto px-4 py-12">
            @include('landing.partials._checkout')
        </div>
    </section>

    <script>
        function productLanding() {
            return {
                selectedVariantId: {{ $product->variants->first()?->id ?? 'null' }},
                unitPrice: {{ $product->variants->first()?->price ?? 0 }},
                tierPrices: @json(($product->variants->first()?->tierPrices ?? collect())->map(fn($t) => ['min_qty' => $t->min_qty, 'price' => $t->price])->sortBy('min_qty')->values()),
                quantity: 1,

                init() {
                    this.syncItems();
                },

                effectivePrice() {
                    if (!this.tierPrices.length) return this.unitPrice;
                    const sorted = [...this.tierPrices].sort((a, b) => b.min_qty - a.min_qty);
                    for (const tier of sorted) {
                        if (this.quantity >= tier.min_qty) return tier.price;
                    }
                    return this.unitPrice;
                },

                selectVariant(id, price, tierPrices) {
                    this.selectedVariantId = id;
                    this.unitPrice = price;
                    this.tierPrices = (tierPrices || []).slice().sort((a, b) => a.min_qty - b.min_qty);
                    this.syncItems();
                },

                changeQty(delta) {
                    this.quantity = Math.max(1, this.quantity + delta);
                    this.syncItems();
                },

                syncItems() {
                    if (!this.selectedVariantId) return;
                    const items = [{
                        variant_id: this.selectedVariantId,
                        quantity: this.quantity
                    }];
                    window.initialItems = items;
                    const checkout = document.getElementById('landingCheckout');
                    if (checkout && checkout.__x) {
                        checkout.__x.$data.updateItems(items);
                    }
                },
            };
        }

        var initialItems = [{
            variant_id: {{ $product->variants->first()?->id ?? 'null' }},
            quantity: 1
        }];
    </script>
@endsection

@extends('layouts.app')

@section('title', $landing->meta_title ?? $landing->title)
@section('meta_description', $landing->meta_description ?? 'Browse and shop our curated selection')

@section('content')
<section class="bg-[#f0f5f1] min-h-screen">

    {{-- Hero Section --}}
    <div class="relative bg-gradient-to-br from-green-900 via-green-800 to-green-700 text-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 py-12 md:py-16 text-center">
            @if($landing->hero_image)
                <img src="{{ asset('storage/' . $landing->hero_image) }}"
                     alt="{{ $landing->title }}"
                     class="max-w-xs w-full mx-auto mb-8 rounded-2xl shadow-2xl">
            @endif
            <h1 class="text-3xl md:text-4xl font-bold mb-3">{{ $landing->title }}</h1>
            @if($landing->content)
                <p class="text-green-100 text-base max-w-2xl mx-auto">
                    {{ Str::limit(strip_tags($landing->content), 200) }}
                </p>
            @endif
        </div>
    </div>

    {{-- Items Grid --}}
    <div class="max-w-6xl mx-auto px-4 py-12">

        @if($listingItems->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-sm">No items have been added to this listing yet.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($listingItems as $item)
                    @php
                        $isVariant = $item->product_variant_id !== null;
                        $name      = $isVariant
                            ? (($item->variant->product->name ?? '') . ($item->variant->name ? ' — ' . $item->variant->name : ''))
                            : ($item->combo->name ?? 'Combo');
                        $price     = $isVariant
                            ? ($item->variant->price ?? 0)
                            : ($item->combo->price ?? 0);
                        $image     = $isVariant
                            ? ($item->variant->product->thumbnail ?? null)
                            : ($item->combo->image ?? null);
                        $tierPrices = $isVariant ? ($item->variant->tierPrices ?? collect()) : collect();
                        $itemId    = $isVariant ? 'v' . $item->product_variant_id : 'c' . $item->combo_id;
                    @endphp

                    <div x-data="listingItem({
                            isVariant: {{ $isVariant ? 'true' : 'false' }},
                            variantId: {{ $item->product_variant_id ?? 'null' }},
                            comboId: {{ $item->combo_id ?? 'null' }},
                            basePrice: {{ $price }},
                            tierPrices: @json($tierPrices->map(fn($t) => ['min_qty' => $t->min_qty, 'price' => $t->price])->values()),
                        })"
                         class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col hover:shadow-md transition-shadow">

                        {{-- Image --}}
                        @if($image)
                            <div class="aspect-square bg-gray-50 flex items-center justify-center p-4 shrink-0">
                                <img src="{{ asset('storage/' . $image) }}" alt="{{ $name }}"
                                     class="max-h-full max-w-full object-contain">
                            </div>
                        @else
                            <div class="aspect-square bg-gray-50 flex items-center justify-center shrink-0">
                                <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif

                        <div class="p-4 flex flex-col flex-1">
                            {{-- Name --}}
                            <h3 class="font-semibold text-gray-800 text-sm leading-snug mb-2">{{ $name }}</h3>

                            {{-- Price + Tier Badges --}}
                            <div class="mb-3">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-lg font-bold text-green-800 font-bengali"
                                          x-text="'৳' + effectivePrice().toFixed(0)"></span>
                                    <span x-show="quantity > 1 && effectivePrice() < basePrice"
                                          class="text-xs text-gray-400 line-through font-bengali"
                                          x-text="'৳' + basePrice.toFixed(0)"></span>
                                </div>

                                {{-- Tier price hints --}}
                                @if($tierPrices->isNotEmpty())
                                    <div class="flex flex-wrap gap-1 mt-1.5">
                                        @foreach($tierPrices->sortBy('min_qty') as $tier)
                                            <span class="text-[10px] bg-green-50 text-green-700 border border-green-200 rounded-full px-2 py-0.5 font-semibold">
                                                {{ $tier->min_qty }}+&nbsp;&rarr;&nbsp;&#2547;{{ number_format($tier->price, 0) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="mt-auto space-y-2">
                                {{-- Quantity stepper --}}
                                <div class="flex items-center gap-2">
                                    <button @click="changeQty(-1)" type="button"
                                            class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 font-bold text-sm transition-all">
                                        &minus;
                                    </button>
                                    <span class="w-8 text-center font-bold text-gray-800 text-sm" x-text="quantity"></span>
                                    <button @click="changeQty(1)" type="button"
                                            class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 font-bold text-sm transition-all">
                                        +
                                    </button>
                                    <span class="text-xs text-gray-400 ml-1 font-bengali"
                                          x-show="quantity > 1"
                                          x-text="'৳' + (effectivePrice() * quantity).toFixed(0) + ' total'"></span>
                                </div>

                                {{-- Add to Cart button --}}
                                <button @click="addToCart($el)" type="button"
                                        :disabled="adding"
                                        class="w-full bg-green-800 text-white py-2 rounded-xl font-semibold text-sm hover:bg-green-900 transition-all disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer">
                                    <template x-if="adding">
                                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    </template>
                                    <template x-if="!adding">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </template>
                                    <span x-text="adding ? 'Adding...' : 'Add to Cart'"></span>
                                </button>

                                {{-- Added flash --}}
                                <p x-show="added" x-transition
                                   class="text-center text-xs text-green-600 font-semibold">
                                    ✓ Added to cart!
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- CMS Content (below grid) --}}
        @if($landing->content && $listingItems->isNotEmpty())
            <div class="mt-12 max-w-4xl mx-auto">
                <div class="prose prose-green max-w-none bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                    {!! $landing->content !!}
                </div>
            </div>
        @endif
    </div>
</section>

<script>
function listingItem({ isVariant, variantId, comboId, basePrice, tierPrices }) {
    return {
        isVariant,
        variantId,
        comboId,
        basePrice,
        tierPrices,
        quantity: 1,
        adding: false,
        added: false,

        effectivePrice() {
            if (!this.tierPrices.length) return this.basePrice;
            const sorted = [...this.tierPrices].sort((a, b) => b.min_qty - a.min_qty);
            for (const tier of sorted) {
                if (this.quantity >= tier.min_qty) return tier.price;
            }
            return this.basePrice;
        },

        changeQty(delta) {
            this.quantity = Math.max(1, this.quantity + delta);
        },

        async addToCart(btn) {
            if (this.adding || !window.cart) return;
            this.adding = true;
            try {
                if (this.isVariant) {
                    await window.cart.add(this.variantId, this.quantity, btn);
                } else {
                    await window.cart.addCombo(this.comboId, this.quantity, btn);
                }
                this.added = true;
                setTimeout(() => { this.added = false; }, 2500);
            } finally {
                this.adding = false;
            }
        },
    };
}
</script>
@endsection

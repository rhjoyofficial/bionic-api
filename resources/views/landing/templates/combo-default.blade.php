@extends('layouts.app')

@section('title', $landing->meta_title ?? $combo->name)
@section('meta_description', $landing->meta_description ?? $combo->description)

@section('content')
<section class="bg-[#f0f5f1] min-h-screen"
         x-data="comboLanding()"
         x-init="init()">

    {{-- Hero Section --}}
    <div class="relative bg-gradient-to-br from-green-900 via-green-800 to-green-700 text-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 py-12 md:py-20">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
                {{-- Combo Image --}}
                <div class="flex justify-center">
                    @if($landing->hero_image)
                        <img src="{{ asset('storage/' . $landing->hero_image) }}"
                             alt="{{ $combo->name }}"
                             class="max-w-sm w-full rounded-2xl shadow-2xl">
                    @elseif($combo->image)
                        <img src="{{ asset('storage/' . $combo->image) }}"
                             alt="{{ $combo->name }}"
                             class="max-w-sm w-full rounded-2xl shadow-2xl">
                    @endif
                </div>

                {{-- Combo Info --}}
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold mb-4">
                        {{ $landing->title ?? $combo->name }}
                    </h1>
                    @if($combo->description)
                        <p class="text-green-100 text-lg mb-6">{{ $combo->description }}</p>
                    @endif

                    {{-- What's Inside --}}
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-green-200 mb-3 uppercase tracking-wider">What's Inside</h3>
                        <div class="space-y-2">
                            @foreach($combo->items as $comboItem)
                                <div class="flex items-center gap-3 bg-green-700/30 rounded-xl px-4 py-2.5">
                                    <div class="w-2 h-2 rounded-full bg-green-300 shrink-0"></div>
                                    <span class="text-sm">
                                        {{ $comboItem->variant->product->name ?? 'Product' }}
                                        &mdash; {{ $comboItem->variant->name ?? $comboItem->variant->sku }}
                                        <span class="text-green-300">x{{ $comboItem->quantity }}</span>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

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
                    <div class="flex items-baseline gap-3">
                        <span class="text-3xl font-bold font-bengali">
                            &#2547;{{ number_format($combo->price, 0) }}
                        </span>
                        @if($combo->compare_price && $combo->compare_price > $combo->price)
                            <span class="text-green-300 line-through text-lg font-bengali">
                                &#2547;{{ number_format($combo->compare_price, 0) }}
                            </span>
                            <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                {{ round((1 - $combo->price / $combo->compare_price) * 100) }}% OFF
                            </span>
                        @endif
                    </div>

                    {{-- Scroll to checkout --}}
                    <a href="#landingCheckout"
                       class="mt-6 inline-flex items-center gap-2 bg-white text-green-800 px-8 py-3 rounded-full font-bold text-base hover:bg-green-50 transition-all shadow-lg">
                        Order Now
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Content Section --}}
    @if($landing->content)
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
function comboLanding() {
    return {
        comboId: {{ $combo->id }},
        quantity: 1,

        init() {
            this.syncItems();
        },

        changeQty(delta) {
            this.quantity = Math.max(1, this.quantity + delta);
            this.syncItems();
        },

        syncItems() {
            const items = [{ combo_id: this.comboId, quantity: this.quantity }];
            window.initialItems = items;
            const checkout = document.getElementById('landingCheckout');
            if (checkout && checkout.__x) {
                checkout.__x.$data.updateItems(items);
            }
        },
    };
}

var initialItems = [{ combo_id: {{ $combo->id }}, quantity: 1 }];
</script>
@endsection

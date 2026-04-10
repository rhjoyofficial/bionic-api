@extends('layouts.app')

@section('title', $landing->meta_title ?? $landing->title)
@section('meta_description', $landing->meta_description ?? 'Special offers on premium products')

@section('content')
<section class="bg-[#f0f5f1] min-h-screen"
         x-data="salesLanding()"
         x-init="init()">

    {{-- Hero Section --}}
    <div class="relative bg-gradient-to-br from-green-900 via-green-800 to-green-700 text-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 py-12 md:py-20 text-center">
            @if($landing->hero_image)
                <img src="{{ asset('storage/' . $landing->hero_image) }}"
                     alt="{{ $landing->title }}"
                     class="max-w-md w-full mx-auto mb-8 rounded-2xl shadow-2xl">
            @endif
            <h1 class="text-3xl md:text-5xl font-bold mb-4">{{ $landing->title }}</h1>
            @if($landing->content)
                <p class="text-green-100 text-lg max-w-2xl mx-auto">{{ Str::limit(strip_tags($landing->content), 200) }}</p>
            @endif
        </div>
    </div>

    {{-- Products Grid --}}
    <div class="max-w-6xl mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            @foreach($salesItems as $item)
                @php
                    $isVariant = $item->product_variant_id !== null;
                    $label = $isVariant
                        ? ($item->variant->product->name ?? '') . ' - ' . ($item->variant->name ?? $item->variant->sku)
                        : ($item->combo->name ?? 'Combo');
                    $price = $isVariant ? $item->variant->price : ($item->combo->price ?? 0);
                    $image = $isVariant
                        ? ($item->variant->product->thumbnail ?? null)
                        : ($item->combo->image ?? null);
                    $itemKey = $isVariant ? 'v_' . $item->product_variant_id : 'c_' . $item->combo_id;
                @endphp

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition-all hover:shadow-md"
                     :class="isSelected('{{ $itemKey }}') ? 'ring-2 ring-green-500' : ''">

                    {{-- Image --}}
                    @if($image)
                        <div class="aspect-square bg-gray-50 flex items-center justify-center p-4">
                            <img src="{{ asset('storage/' . $image) }}" alt="{{ $label }}"
                                 class="max-h-full max-w-full object-contain">
                        </div>
                    @endif

                    <div class="p-5">
                        {{-- Title & Price --}}
                        <h3 class="font-bold text-gray-800 mb-1">{{ $label }}</h3>
                        <p class="text-lg font-bold text-green-800 font-bengali mb-4">
                            &#2547;{{ number_format($price, 0) }}
                        </p>

                        {{-- Selection + Quantity --}}
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox"
                                       :checked="isSelected('{{ $itemKey }}')"
                                       @change="toggleItem('{{ $itemKey }}', {{ $isVariant ? $item->product_variant_id : 'null' }}, {{ !$isVariant ? $item->combo_id : 'null' }})"
                                       class="w-5 h-5 rounded accent-green-700">
                                <span class="text-sm font-semibold text-gray-600">Select</span>
                            </label>

                            <div x-show="isSelected('{{ $itemKey }}')" class="flex items-center gap-2">
                                <button @click="changeItemQty('{{ $itemKey }}', -1)" type="button"
                                        class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 font-bold transition-all">
                                    &minus;
                                </button>
                                <span class="w-8 text-center font-bold text-gray-800" x-text="getQty('{{ $itemKey }}')"></span>
                                <button @click="changeItemQty('{{ $itemKey }}', 1)" type="button"
                                        class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 font-bold transition-all">
                                    +
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Selected Items Summary --}}
        <div x-show="Object.keys(selected).length > 0"
             class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-8">
            <h3 class="font-bold text-gray-800 mb-3">
                Selected Items (<span x-text="Object.keys(selected).length"></span>)
            </h3>
            <div class="space-y-2">
                <template x-for="(item, key) in selected" :key="key">
                    <div class="flex justify-between items-center text-sm text-gray-600 py-1 border-b border-gray-50">
                        <span x-text="item.label"></span>
                        <span class="font-semibold" x-text="'x' + item.quantity"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Checkout Section --}}
    <div class="max-w-2xl mx-auto px-4 pb-16">
        @include('landing.partials._checkout')
    </div>
</section>

<script>
function salesLanding() {
    return {
        selected: {},

        // Pre-selected items from the database
        preselected: @json($salesItems->filter(fn($i) => $i->is_preselected)->map(function($item) {
            return [
                'key' => $item->product_variant_id ? 'v_' . $item->product_variant_id : 'c_' . $item->combo_id,
                'variant_id' => $item->product_variant_id,
                'combo_id' => $item->combo_id,
                'label' => $item->product_variant_id
                    ? (($item->variant->product->name ?? '') . ' - ' . ($item->variant->name ?? $item->variant->sku))
                    : ($item->combo->name ?? 'Combo'),
            ];
        })->values()),

        init() {
            // Auto-select preselected items
            this.preselected.forEach(item => {
                this.selected[item.key] = {
                    variant_id: item.variant_id,
                    combo_id: item.combo_id,
                    quantity: 1,
                    label: item.label,
                };
            });
            this.syncItems();
        },

        isSelected(key) {
            return key in this.selected;
        },

        getQty(key) {
            return this.selected[key]?.quantity || 0;
        },

        toggleItem(key, variantId, comboId) {
            if (this.isSelected(key)) {
                delete this.selected[key];
            } else {
                // Find label from preselected or DOM
                const label = key;
                this.selected[key] = {
                    variant_id: variantId,
                    combo_id: comboId,
                    quantity: 1,
                    label: label,
                };
            }
            // Force Alpine reactivity
            this.selected = { ...this.selected };
            this.syncItems();
        },

        changeItemQty(key, delta) {
            if (!this.selected[key]) return;
            const newQty = this.selected[key].quantity + delta;
            if (newQty < 1) {
                delete this.selected[key];
                this.selected = { ...this.selected };
            } else {
                this.selected[key].quantity = newQty;
            }
            this.syncItems();
        },

        syncItems() {
            const items = Object.values(this.selected).map(s => {
                const item = { quantity: s.quantity };
                if (s.variant_id) item.variant_id = s.variant_id;
                if (s.combo_id) item.combo_id = s.combo_id;
                return item;
            });

            window.initialItems = items;
            const checkout = document.getElementById('landingCheckout');
            if (checkout && checkout.__x) {
                checkout.__x.$data.updateItems(items);
            }
        },
    };
}

var initialItems = @json($salesItems->filter(fn($i) => $i->is_preselected)->map(function($item) {
    $data = ['quantity' => 1];
    if ($item->product_variant_id) $data['variant_id'] = $item->product_variant_id;
    if ($item->combo_id) $data['combo_id'] = $item->combo_id;
    return $data;
})->values());
</script>
@endsection

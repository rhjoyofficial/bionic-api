@props(['product'])
@php
    $variants = $product->variants;
    $frontendVariants = $variants->map->toFrontend();
    $first = $variants->first();
@endphp

{{-- Main Card - Named as group/card --}}
<div class="product-card group/card flex flex-col h-full border border-gray-100 rounded-2xl p-2 md:p-4 transition-all duration-300 hover:shadow-lg hover:border-primary/20 bg-white"
    data-variants='@json($frontendVariants)'>

    {{-- IMAGE --}}
    <div class="relative aspect-square overflow-hidden rounded-xl bg-gray-50 mb-4">
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
            class="aspect-square object-cover w-full h-full group-hover/card:scale-105 transition-transform duration-500">

        {{-- DISCOUNT BADGE --}}
        <span
            class="discountBadge {{ $first?->discount_percent ? '' : 'hidden' }}
            absolute top-1 right-1 md:top-3 md:right-3 bg-red-600 text-white text-[10px] font-light px-2 py-1 rounded-md uppercase tracking-wider">
            {{ $first?->discount_percent }}%
        </span>

        {{-- TIER PREVIEW (TOP LEFT) --}}
        <div class="tierPreview absolute top-2 left-2 flex flex-col gap-1 pointer-events-none font-bengali">
            @if ($first?->tierPrices?->count())
                @foreach ($first->tierPrices->take(2) as $tier)
                    <div
                        class="bg-white/90 backdrop-blur-sm border border-primary/20 text-primary px-2 py-0.5 rounded-md shadow-sm">
                        <p class="text-[9px] md:text-[10px] font-bold leading-tight uppercase tracking-tight">
                            {{ $tier->min_quantity }}+ Items
                        </p>
                        <p class="text-[11px] md:text-[12px] font-black leading-tight">
                            Save
                            {{ $tier->discount_type === 'percentage' ? $tier->discount_value . '%' : '৳' . $tier->discount_value }}
                        </p>
                    </div>
                @endforeach
            @endif
        </div>

    </div>

    <div class="flex flex-col grow">

        {{-- TITLE --}}
        <a href="{{ route('product.show', $product->slug) }}"
            class="text-xs md:text-base font-bengali text-left text-gray-800 font-medium leading-snug line-clamp-2 min-h-8 group-hover/card:text-primary transition-colors truncate-2 hover:underline">
            {{ $product->name }}
        </a>

        {{-- VARIANT SELECT --}}
        @if ($variants->count() > 1)
            <div class="relative mb-2">
                <select
                    class="variantSelect w-full appearance-none bg-gray-50 border border-gray-200 hover:bg-white/80 rounded-xl px-4 py-2 text-sm font-medium text-gray-900 font-bengali focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all cursor-pointer">
                    @foreach ($variants as $v)
                        <option value="{{ $v->id }}" {{ $loop->first ? 'selected' : '' }}>
                            {{ $v->title }} — {{ format_currency($v->final_price) }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>
        @else
            {{-- PRICE (Only shows if there is 1 variant) --}}
            <div class="priceBox flex items-center gap-2 mt-auto mb-2 md:mb-4 font-bengali">
                <span class="finalPrice text-sm md:text-lg font-bold text-gray-900">
                    ৳{{ number_format($first?->final_price) }}
                </span>
                <span
                    class="oldPrice text-xs md:text-sm text-gray-400 line-through {{ $first?->discount_percent ? '' : 'hidden' }}">
                    ৳{{ number_format($first?->price) }}
                </span>
            </div>
        @endif



        {{-- ADD BUTTON --}}
        <button
            class="addToCartBtn {{ $first?->available_stock <= 0 ? 'hidden' : '' }}
            w-full flex items-center justify-center gap-2 px-2.5 py-1.5 md:py-2.5 rounded-lg md:rounded-xl bg-primary/10 text-primary font-semibold hover:bg-primary hover:text-white! transition-all duration-300 active:scale-95 cursor-pointer focus:outline-none"
            data-variant="{{ $first?->id }}">
            Add To Cart
        </button>

        {{-- CONTACT BUTTON --}}
        <button
            class="contactBtn {{ $first?->available_stock > 0 ? 'hidden' : '' }}
            w-full flex items-center justify-center gap-2 px-2.5 py-1.5 md:py-2.5 rounded-lg md:rounded-xl bg-gray-200 text-gray-700 font-semibold transition-all">
            Contact Us
        </button>

    </div>
</div>

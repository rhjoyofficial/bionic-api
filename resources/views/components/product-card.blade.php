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
            <div class="variant-container flex flex-wrap gap-2 mb-3">
                @foreach ($variants as $v)
                    <button type="button" data-variant-id="{{ $v->id }}"
                        class="variant-capsule cursor-pointer px-3 py-1.5 rounded-md md:rounded-lg border text-sm font-medium text-gray-900 font-bengali transition-all duration-200 leading-tight
                {{ $loop->first
                    ? 'border-primary bg-primary/10 text-primary'
                    : 'border-gray-200 bg-gray-50 text-gray-600 hover:border-primary/50' }}">
                        {{ $v->title }} {{ format_currency($v->final_price) }}
                    </button>
                @endforeach
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

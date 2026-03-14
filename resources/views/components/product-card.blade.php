@props(['product'])

<div
    class="product-card group flex flex-col h-full border border-gray-100 rounded-2xl p-2 md:p-4 transition-all duration-300 hover:shadow-lg hover:border-primary/20 bg-white">

    <div class="relative aspect-square overflow-hidden rounded-xl bg-gray-50 mb-4">
        <img src="{{ $product->image_url }}" alt=" {{ $product->name }}"
            class="aspect-square object-cover w-full h-full group-hover:scale-105 transition-transform duration-500">

        @if (100 % 2 == 0)
            <span
                class="absolute top-1 right-1 md:top-3 md:right-3 bg-red-600 text-white text-[10px] font-light px-2 py-1 rounded-md uppercase tracking-wider">-28%</span>
        @endif
    </div>

    <div class="flex flex-col grow">
        <a href="#"
            class="text-xs md:text-base font-bengali text-left text-gray-800 font-medium leading-snug line-clamp-2 min-h-8 group-hover:text-primary transition-colors truncate-2 hover:underline">
            {{ $product->name }}
        </a>

        @if ($product->variants->count() > 1)
            <div class="relative group mb-1">
                <select
                    class="variantSelect w-full appearance-none bg-gray-50 border border-gray-200 group-hover:bg-primary/50 rounded-xl px-4 py-1 md:text-lg font-medium text-gray-900 font-bengali focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all cursor-pointer">
                    @foreach ($product->variants as $var)
                        <option value="{{ $var->id }}">
                            {{ $var->title }} — {{ format_currency($var->price) }}
                        </option>
                    @endforeach
                </select>
                {{-- Custom Arrow Icon --}}
                <div
                    class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400 group-hover:text-gray-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>
        @else
            <div class="flex items-center gap-2 mt-auto mb-2 md:mb-4 font-bengali">
                <span class="text-sm md:text-lg font-bold text-gray-900">৳1,920</span>
                <span class="text-xs md:text-sm text-gray-400 line-through">৳1,920</span>
            </div>
        @endif

        <button
            class="addToCartBtn w-full flex items-center justify-center gap-2 px-2.5 py-1.5 md:py-2.5 rounded-lg md:rounded-xl bg-primary/10 text-primary font-semibold group-hover:bg-primary group-hover:text-white transition-all duration-300 active:scale-95 cursor-pointer focus:outline-none">
            <svg class="md:block hidden w-6 h-full fill-current group-hover:text-white"
                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path
                    d="M15.25 7.25V7c0-.86-.34-1.69-.95-2.3s-1.44-.95-2.3-.95-1.69.34-2.3.95-.95 1.44-.95 2.3v.25zm-8 4.75V8.75h-.41c-.65 0-1.19.5-1.25 1.15l-.66 8c-.06.72.51 1.35 1.24 1.35h11.66c.73 0 1.3-.63 1.24-1.35l-.66-8a1.26 1.26 0 0 0-1.25-1.15h-.41V12c0 .41-.34.75-.75.75s-.75-.34-.75-.75V8.75h-6.5V12c0 .41-.34.75-.75.75s-.75-.34-.75-.75m0-5c0-1.26.5-2.47 1.39-3.36A4.753 4.753 0 0 1 16.75 7v.25h.41c1.43 0 2.62 1.1 2.74 2.52l.67 8c.13 1.6-1.13 2.98-2.74 2.98H6.17c-1.61 0-2.87-1.38-2.74-2.98l.67-8a2.753 2.753 0 0 1 2.74-2.52h.41z" />
            </svg>
            <span class="text-sm group-hover:text-white">Add To Cart</span>
        </button>
    </div>
</div>

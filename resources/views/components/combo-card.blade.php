@props(['combo'])

<div
    class="group/card bg-white rounded-xl border border-gray-100 overflow-hidden active:bg-gray-50 transition-all duration-300 hover:shadow-md hover:border-primary/20">
    <div class="flex flex-row items-stretch h-full">
        <div class="w-2/5 sm:w-32 md:w-5/12 aspect-square shrink-0 overflow-hidden">
            <img src="{{ $combo->image_url }}" alt="{{ $combo->title }}"
                class="w-full h-full object-cover group-hover/card:scale-105 transition-transform duration-500">
        </div>

        <div class="flex-1 p-3 flex flex-col min-w-0">
            <div class="grow">
                <div class="flex items-start justify-between gap-2 mb-1 relative">
                    <a href="#"
                        class="font-bengali text-left text-gray-800 font-medium leading-snug line-clamp-2 min-h-8 group-hover/card:text-primary transition-colors truncate-2 hover:underline">
                        {{ $combo->title }}
                    </a>
                    @if ($combo->total_savings > 0)
                        <span
                            class="absolute top-0 right-0 font-bengali bg-primary/10 text-primary text-xs font-bold px-1.5 py-0.5 rounded shrink-0">
                            -{{ number_format($combo->total_savings) }}৳
                        </span>
                    @endif
                </div>

                <p class="text-xs text-gray-400 mb-2 truncate ">
                    {{ $combo->items->map(fn($item) => $item->variant->product->name)->implode(' • ') }}</p>

                <div class="flex items-baseline gap-1.5 mb-3 font-bengali">
                    <span class="text-base font-bold text-primary">{{ number_format($combo->final_price) }}৳</span>
                    @if ($combo->pricing_mode === 'manual' || $combo->discount_value > 0)
                        <span class="text-xs text-gray-400 line-through">{{ number_format($combo->auto_price) }}৳</span>
                    @endif
                </div>
            </div>

            <button
                class="addComboBtn group w-full flex items-center justify-center gap-2 py-2.5 rounded-xl bg-primary/10 text-primary font-semibold hover:bg-primary hover:text-white transition-all duration-300 active:scale-95 cursor-pointer focus:outline-none"
                data-combo="{{ $combo->id }}">
                <svg class="md:block hidden w-4.5 h-full fill-current group-hover:text-white"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path
                        style="fill:none; stroke:currentColor; stroke-width:20; stroke-linecap:round; stroke-linejoin:round; stroke-miterlimit:10"
                        d="m363.527 381.826-7.256-85.984m-150.485-58.478c-27.52 6.707-47.675 30.252-50.057 58.478m133.188-161.846a243 243 0 0 1 30.612-30.905l4.787-4.037a107.9 107.9 0 0 0 36.881-64.803v0S310.249 3.747 235.611 11.162v0a58.8 58.8 0 0 0 9.702 32.349h0a64.9 64.9 0 0 1 10.715 35.725v2.686" />
                    <path
                        style="fill:none; stroke:currentColor; stroke-width:20; stroke-linecap:round; stroke-linejoin:round; stroke-miterlimit:10"
                        d="M237.711 26.702c-53.006-.908-86.907 7.549-86.907 7.549a107.9 107.9 0 0 0 36.881 64.803l4.787 4.037a243 243 0 0 1 30.612 30.905m81.285.813h-96.737c-11.151 0-20.191 9.04-20.191 20.191v0c0 11.151 9.04 20.191 20.191 20.191h96.737c11.151 0 20.191-9.04 20.191-20.191v0c0-11.151-9.04-20.191-20.191-20.191" />
                    <path
                        style="fill:none; stroke:currentColor; stroke-width:20; stroke-linecap:round; stroke-linejoin:round; stroke-miterlimit:10"
                        d="m189.383 166.115-20.148 4.91c-47.553 11.589-82.378 52.273-86.494 101.045L70.202 420.643c-1.429 16.931-7.756 33.3-19.098 45.951a21.16 21.16 0 0 0-5.419 14.17h0c0 11.728 9.508 21.236 21.236 21.236H445.08c11.728 0 21.236-9.507 21.236-21.236h0a21.15 21.15 0 0 0-5.419-14.17c-11.342-12.652-17.669-29.02-19.098-45.951l-12.54-148.573c-4.116-48.771-38.941-89.455-86.494-101.045l-20.706-5.046" />
                </svg>
                <span class="text-sm group-hover:text-white">Add To Cart</span>
            </button>
        </div>
    </div>
</div>

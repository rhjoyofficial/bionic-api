@props(['image', 'name'])

<div class="group flex flex-col items-center text-center transition duration-300 hover:scale-105 hover:-translate-y-1">
    <img src="{{ asset($image) }}" alt="{{ $name }} Certified"
        class="h-16 md:h-20 object-contain transition-transform duration-300 group-hover:scale-110" loading="lazy" />
    <span class="mt-3 text-sm font-medium text-gray-800 font-inter">
        {{ $name }}
    </span>
</div>

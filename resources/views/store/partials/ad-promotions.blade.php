<section class="py-12 px-4 md:px-8 bg-white">
    <div class="max-w-8xl mx-auto space-y-8">

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @foreach ([1, 2, 3] as $ad)
                <div
                    class="relative group overflow-hidden rounded-2xl aspect-square shadow-sm transition-all duration-500 hover:shadow-xl">
                    <img src="{{ asset('assets/ads/promo-image-' . $ad . '.jpg') }}" alt="Product Promotion" loading="lazy"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                </div>
            @endforeach
        </div>

        <div
            class="relative overflow-hidden rounded-2xl md:rounded-3xl bg-gray-50 group shadow-sm border border-gray-100 transition-all duration-500 hover:shadow-2xl">
            <a href="/ramadan-special" class="block relative w-full">
                <picture>
                    <source media="(min-width: 1280px)" srcset="{{ asset('assets/ads/ghee-mustard-oil-desktop.jpg') }}">

                    <source media="(min-width: 768px)" srcset="{{ asset('assets/ads/ghee-mustard-oil-tablet.jpg') }}">

                    <img src="{{ asset('assets/ads/ghee-mustard-oil-mobile.jpg') }}" alt="Ramadan Special Offer"
                        loading="lazy"
                        class="w-full h-auto object-cover transition-transform duration-1000 group-hover:scale-[1.02]">
                </picture>
            </a>
        </div>

    </div>
</section>

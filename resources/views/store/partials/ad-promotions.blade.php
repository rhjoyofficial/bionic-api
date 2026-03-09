<section class="py-12 px-4 md:px-8 bg-white">
    <div class="max-w-8xl mx-auto space-y-8">

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @foreach ([1, 2, 3] as $ad)
                <div
                    class="relative group overflow-hidden rounded-2xl aspect-square shadow-sm transition-all duration-500 hover:shadow-xl">
                    <img src="{{ asset('assets/ads/promo-image-' . $ad . '.jpg') }}" alt="Product Promotion"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                </div>
            @endforeach
        </div>

        <div
            class="relative overflow-hidden rounded-2xl md:rounded-3xl bg-gray-50 group shadow-sm border border-gray-100 transition-all duration-500 hover:shadow-2xl">
            <a href="/ramadan-special" class="block relative w-full overflow-hidden">
                <img src="{{ asset('assets/ads/ramadan-banner.jpg') }}" alt="Ramadan Power Box Special Offer"
                    class="w-full object-cover transition-transform duration-1000 group-hover:scale-[1.02] 
                            aspect-video sm:aspect-21/9 md:aspect-3/1 lg:aspect-4/1">
            </a>
        </div>

    </div>
</section>

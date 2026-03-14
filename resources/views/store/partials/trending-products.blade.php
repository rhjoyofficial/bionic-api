<section class="py-12 px-4 md:px-8 bg-white">
    <div class="max-w-8xl mx-auto">

        <div class="flex items-center justify-between gap-6 mb-8 pb-4">
            <h2 class="font-heading text-2xl md:text-3xl font-bold text-brand text-left gap-3 md:shrink-0">
                Trending Products
            </h2>
            <span class="h-0.5 w-full bg-gray-200 hidden md:block"></span>
            <div class="flex gap-2 md:shrink-0">
                <button
                    class="trending-prev p-2 rounded-md border border-gray-200 hover:bg-gray-50 transition-colors cursor-pointer focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button
                    class="trending-next p-2 rounded-md border border-gray-200 hover:bg-gray-50 transition-colors cursor-pointer focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="swiper trending-swiper overflow-hidden">
            <div class="swiper-wrapper">
                @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9, 10] as $i)
                    <div class="swiper-slide h-auto">
                        <x-product-card :i="$i" />
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const swiper = new Swiper('.trending-swiper', {
                slidesPerView: 2,
                spaceBetween: 12,
                loop: true,
                navigation: {
                    nextEl: '.trending-next',
                    prevEl: '.trending-prev',
                },
                breakpoints: {
                    // When window width is >= 640px (Tablets)
                    640: {
                        slidesPerView: 2,
                        spaceBetween: 20,
                    },
                    // When window width is >= 1024px (Laptops)
                    1024: {
                        slidesPerView: 3,
                        spaceBetween: 24,
                    },
                    // When window width is >= 1280px (Large Desktops)
                    1280: {
                        slidesPerView: 4,
                        spaceBetween: 30,
                    },
                },
            });
        });
    </script>
@endpush

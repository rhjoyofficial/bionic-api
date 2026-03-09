<section class="bg-[#246E231A] py-8 px-4 md:px-8">
    <div class="max-w-8xl mx-auto">
        {{-- Grid Layout --}}
        <div class="grid grid-cols-1 md:grid-cols-12 grid-rows-none md:grid-rows-6 gap-4">

            {{-- Main Hero Swiper --}}
            <div class="col-span-12 md:col-span-9 md:row-span-3 bg-white rounded-3xl overflow-hidden relative group">
                <div class="swiper mainHeroSwiper h-full">
                    <div class="swiper-wrapper">
                        {{-- Slide 1 --}}
                        <div class="swiper-slide p-6 md:p-8 lg:p-12">
                            <div class="inline-flex items-center gap-2 bg-[#f0f7f0] px-4 py-1.5 rounded-full mb-6">
                                <span class="text-[#2d6a4f] text-xs font-bold">🌿 100% Pure & Natural</span>
                            </div>
                            <div class="flex flex-col lg:flex-row justify-between items-center gap-8 w-full">
                                <div>
                                    <h1
                                        class="font-heading text-3xl sm:text-4xl md:text-5xl lg:text-6xl text-slate-900 leading-[1.1] mb-6">
                                        Pure Organic <br> Mangrove Gold <br> Honey
                                    </h1>

                                    <div class="flex items-center gap-4 mb-8">
                                        <span class="text-3xl font-light text-slate-300">01</span>
                                        <div class="h-px w-12 bg-slate-200"></div>
                                        <div class="text-slate-500 text-sm max-w-xs">
                                            <strong>Farm to Jar Freshness</strong><br>
                                            Premium organic dates, raw honey, cold-pressed oils & natural superfoods.
                                        </div>
                                    </div>

                                    <div class="flex flex-col flex-wrap justify-start items-start gap-6">
                                        <a href="#"
                                            class="bg-primary hover:opacity-90 text-white px-8 py-4 rounded-full font-bold flex items-center gap-3 transition-all transform hover:scale-105 active:scale-95 shadow-xl shadow-green-900/20">
                                            All Products
                                            <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="relative">
                                    <img src="{{ asset('assets/images/honey-jar.png') }}" alt="Raw Honey"
                                        class="max-w-full h-auto">
                                </div>
                            </div>
                        </div>

                        {{-- Slide 2 --}}
                        <div class="swiper-slide p-6 md:p-8 lg:p-12">
                            <div class="inline-flex items-center gap-2 bg-[#f0f7f0] px-4 py-1.5 rounded-full mb-6">
                                <span class="text-[#2d6a4f] text-xs font-bold">🌿 Raw & Unfiltered</span>
                            </div>
                            <div class="flex flex-col lg:flex-row justify-between items-center gap-8 w-full">
                                <div>
                                    <h1
                                        class="font-heading text-3xl sm:text-4xl md:text-5xl lg:text-6xl text-slate-900 leading-[1.1] mb-6">
                                        Wild Forest <br> Raw Honey <br> 100% Pure
                                    </h1>

                                    <div class="flex items-center gap-4 mb-8">
                                        <span class="text-3xl font-light text-slate-300">02</span>
                                        <div class="h-px w-12 bg-slate-200"></div>
                                        <div class="text-slate-500 text-sm max-w-xs">
                                            <strong>Harvested from Sundarbans</strong><br>
                                            Direct from the mangrove forests, rich in medicinal properties.
                                        </div>
                                    </div>

                                    <div class="flex flex-col flex-wrap justify-start items-start gap-6">
                                        <a href="#"
                                            class="bg-primary hover:opacity-90 text-white px-8 py-4 rounded-full font-bold flex items-center gap-3 transition-all transform hover:scale-105 active:scale-95 shadow-xl shadow-green-900/20">
                                            Explore Collection
                                            <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="relative">
                                    <img src="{{ asset('assets/images/honey-jar-2.png') }}" alt="Raw Honey"
                                        class="max-w-full h-auto">
                                </div>
                            </div>
                        </div>

                        {{-- Slide 3 --}}
                        <div class="swiper-slide p-6 md:p-8 lg:p-12">
                            <div class="inline-flex items-center gap-2 bg-[#f0f7f0] px-4 py-1.5 rounded-full mb-6">
                                <span class="text-[#2d6a4f] text-xs font-bold">🌿 Gift Box Special</span>
                            </div>
                            <div class="flex flex-col lg:flex-row justify-between items-center gap-8 w-full">
                                <div>
                                    <h1
                                        class="font-heading text-3xl sm:text-4xl md:text-5xl lg:text-6xl text-slate-900 leading-[1.1] mb-6">
                                        Organic Honey <br> Gift Set <br> Perfect Gift
                                    </h1>

                                    <div class="flex items-center gap-4 mb-8">
                                        <span class="text-3xl font-light text-slate-300">03</span>
                                        <div class="h-px w-12 bg-slate-200"></div>
                                        <div class="text-slate-500 text-sm max-w-xs">
                                            <strong>Curated Collection</strong><br>
                                            Three premium honey varieties in an elegant gift box.
                                        </div>
                                    </div>

                                    <div class="flex flex-col flex-wrap justify-start items-start gap-6">
                                        <a href="#"
                                            class="bg-primary hover:opacity-90 text-white px-8 py-4 rounded-full font-bold flex items-center gap-3 transition-all transform hover:scale-105 active:scale-95 shadow-xl shadow-green-900/20">
                                            Shop Gift Sets
                                            <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="relative">
                                    <img src="{{ asset('assets/images/honey-gift.png') }}" alt="Honey Gift Set"
                                        class="max-w-full h-auto">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Navigation Buttons --}}
                    <div
                        class="swiper-button-next !text-[#1D6327] after:text-xl! bg-white/80 backdrop-blur w-10 h-10 rounded-full shadow-md">
                    </div>
                    <div
                        class="swiper-button-prev !text-[#1D6327] after:text-xl! bg-white/80 backdrop-blur w-10 h-10 rounded-full shadow-md">
                    </div>

                </div>
                {{-- Social Media Links - Fixed positioning --}}
                <div class="absolute bottom-6 left-6 flex items-center gap-3 text-slate-400 z-10">
                    <span class="text-xs font-medium tracking-widest mr-2">Follow Us On:</span>
                    <a href="#"
                        class="w-8 h-8 rounded-full border border-slate-100 flex items-center justify-center hover:bg-slate-50 transition-colors"><i
                            class="fa-brands fa-facebook-f text-sm"></i></a>
                    <a href="#"
                        class="w-8 h-8 rounded-full border border-slate-100 flex items-center justify-center hover:bg-slate-50 transition-colors"><i
                            class="fa-brands fa-instagram text-sm"></i></a>
                    <a href="#"
                        class="w-8 h-8 rounded-full border border-slate-100 flex items-center justify-center hover:bg-slate-50 transition-colors"><i
                            class="fa-brands fa-youtube text-sm"></i></a>
                </div>
            </div>

            {{-- Explore Categories  --}}
            <div class="col-span-12 md:col-span-3 md:row-span-1 md:col-start-10 bg-white rounded-3xl p-6 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4">Explore Categories</h3>
                <div class="swiper categoriesSwiper">
                    <div class="swiper-wrapper">
                        @foreach (['Honey', 'Dates', 'Oils', 'Seeds', 'Nuts', 'Spices'] as $cat)
                            <div class="swiper-slide">
                                <div class="flex flex-col items-center gap-1 group cursor-pointer">
                                    <div
                                        class="w-16 h-16 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center group-hover:border-primary transition-all">
                                        <img src="{{ asset('assets/images/' . strtolower($cat) . '.png') }}"
                                            alt="{{ $cat }}" class="w-6 h-6 object-contain">
                                    </div>
                                    <span
                                        class="text-base font-medium text-slate-400 tracking-tighter">{{ $cat }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- New Arrival  --}}
            <div
                class="col-span-12 md:col-span-3 md:row-span-1 md:col-start-10 md:row-start-2 bg-white rounded-3xl p-6 relative overflow-hidden group">
                <div
                    class="flex items-center justify-between gap-3 bg-white rounded-2xl group overflow-hidden relative h-full">

                    <div class="flex flex-col justify-between h-full flex-1 z-10">

                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">New
                                Arrival</span>
                        </div>

                        <div class="flex-1 flex items-center">
                            <h4 class="text-sm font-bold text-slate-800 leading-tight">
                                Egyptian Medjool <br> Large Dates
                            </h4>
                        </div>

                        <div>
                            <div
                                class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center transition-all duration-300 group-hover:bg-[#1D6327] group-hover:text-white cursor-pointer shadow-sm">
                                <i class="fa-solid fa-arrow-right text-[12px]"></i>
                            </div>
                        </div>
                    </div>

                    {{-- take middle right --}}
                    <div class="relative w-24 h-24 flex-shrink-0 flex items-center">
                        <img src="{{ asset('assets/images/dates.png') }}" alt="Dates"
                            class="w-full h-full object-contain transform group-hover:scale-110 group-hover:-rotate-6 transition-all duration-500">
                    </div>

                </div>
            </div>

            {{-- Offer Swiper  --}}
            <div
                class="col-span-12 md:col-span-3 md:row-span-2 md:col-start-10 md:row-start-3 rounded-3xl relative overflow-hidden bg-white">
                <div class="swiper offerSwiper w-full">
                    <div class="swiper-wrapper">
                        @foreach ([1, 2, 3] as $i)
                            <div class="swiper-slide">
                                <img src="{{ asset('assets/images/offer' . $i . '.png') }}"
                                    alt="Offer {{ $i }}" class="w-full h-full object-cover rounded-lg">
                            </div>
                        @endforeach
                    </div>
                </div>
                <div
                    class="absolute top-0 left-0 w-full h-full bg-gradient-to-t from-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                    <div
                        class="w-8 h-8 rounded-full bg-white flex items-center justify-center hover:bg-primary hover:text-white transition-all cursor-pointer pointer-events-auto">
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </div>
                </div>
            </div>

            {{-- More Products  --}}
            <div
                class="col-span-12 md:col-span-3 md:row-span-1 md:row-start-4 bg-white rounded-3xl p-6 flex flex-col justify-between">
                <div class="flex justify-between items-center gap-4">
                    <div>
                        <h3 class="font-bold text-slate-800">More Products</h3>
                        <p class="text-xs text-slate-400">40+ Natural Items</p>
                    </div>
                    <div
                        class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 cursor-pointer hover:bg-primary hover:text-white transition-all">
                        <i class="fa-solid fa-chevron-right text-sm"></i>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-4">
                    <div class="flex -space-x-3">
                        @foreach ([1, 2, 3] as $i)
                            <img src="{{ asset('assets/images/product' . $i . '.png') }}"
                                alt="Product {{ $i }}"
                                class="w-16 h-16 md:w-20 md:h-20 rounded-2xl object-cover border-2 border-white">
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Happy Customers  --}}
            <div
                class="col-span-12 md:col-span-2 md:row-span-1 md:col-start-4 md:row-start-4 bg-white rounded-3xl p-6 flex flex-col items-center justify-center text-center">
                <div class="flex justify-center items-center gap-1 mt-4">
                    @foreach ([1, 2, 3, 4, 5] as $i)
                        <img src="{{ asset('assets/images/customer' . $i . '.png') }}" alt="Happy Customer"
                            class="rounded-full w-6 h-6 object-cover border-2 border-white -ml-2 first:ml-0">
                    @endforeach
                </div>
                <div class="bg-primary text-white text-base font-black px-3 py-1 rounded-full mb-2">10k+</div>
                <p class="text-sm font-bold text-slate-800">Happy Customers</p>
                <div class="text-sm text-slate-400 mt-1">⭐ 4.9 Rating</div>
            </div>

            {{-- Certifications Swiper  --}}
            <div
                class="col-span-12 md:col-span-4 md:row-span-1 md:col-start-6 md:row-start-4 bg-white rounded-3xl p-6">
                <span
                    class="text-sm font-bold bg-primary/10 text-primary border border-secondary/10 p-2 rounded-2xl mb-4 inline-block">
                    <i class="fa-solid fa-certificate text-sm"></i> Certifications
                </span>

                <div class="swiper certificationsSwiper">
                    <div class="swiper-wrapper">
                        @foreach (['bsti', 'gmo-free', 'haccp', 'iso-22000', 'no-msg', 'halal', 'bsti', 'gmo-free', 'haccp'] as $cert)
                            <div class="swiper-slide">
                                <img src="{{ asset('assets/images/certificates/' . $cert . '.png') }}"
                                    alt="{{ $cert }}"
                                    class="w-14 h-14 md:w-16 md:h-16 lg:w-20 lg:h-20 object-contain mx-auto hover:scale-110 transition-transform">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Main Hero Swiper
            const mainSwiper = new Swiper('.mainHeroSwiper', {
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },
                speed: 1000,
            });

            // Categories Swiper
            const categoriesSwiper = new Swiper('.categoriesSwiper', {
                loop: true,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                },
                slidesPerView: 4,
                spaceBetween: 10,
                grabCursor: true,
                breakpoints: {
                    640: {
                        slidesPerView: 5,
                    },
                    768: {
                        slidesPerView: 4,
                    },
                    1024: {
                        slidesPerView: 4,
                    },
                },
            });

            // Offer Swiper
            const offerSwiper = new Swiper('.offerSwiper', {
                loop: true,
                autoplay: {
                    delay: 2500,
                    disableOnInteraction: false,
                },
                effect: 'slide',
                speed: 800,
                grabCursor: true,
            });

            // Certifications Swiper - Fixed for auto-loop
            const certSwiper = new Swiper('.certificationsSwiper', {
                loop: true,
                autoplay: {
                    delay: 2000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                slidesPerView: 3,
                spaceBetween: 15,
                grabCursor: true,
                speed: 800,
                loopedSlides: 9, // Match the number of slides we have (including duplicates)
                breakpoints: {
                    640: {
                        slidesPerView: 4,
                        loopedSlides: 9,
                    },
                    768: {
                        slidesPerView: 5,
                        loopedSlides: 9,
                    },
                    1024: {
                        slidesPerView: 6,
                        loopedSlides: 9,
                    },
                },
                on: {
                    init: function() {
                        // Force autoplay to start
                        this.autoplay.start();
                    }
                }
            });

            // Add click pause/resume functionality
            [mainSwiper, categoriesSwiper, offerSwiper, certSwiper].forEach(swiper => {
                if (swiper) {
                    swiper.el.addEventListener('mouseenter', () => {
                        swiper.autoplay.stop();
                    });
                    swiper.el.addEventListener('mouseleave', () => {
                        swiper.autoplay.start();
                    });
                }
            });
        });
    </script>
@endpush

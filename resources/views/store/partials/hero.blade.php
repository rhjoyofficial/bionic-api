<section class="bg-[#246E231A] py-6 md:py-8 px-4 lg:px-8">
    <div class="max-w-8xl mx-auto">
        {{-- Grid Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 grid-rows-none lg:grid-rows-4 gap-4">

            {{-- Main Hero Swiper --}}
            <div
                class="col-span-12 lg:col-span-9 lg:row-span-3 bg-white rounded-3xl overflow-hidden pb-6 md:pb-0 relative">
                <div class="swiper mainHeroSwiper h-full">
                    <div class="swiper-wrapper">
                        @foreach ($heroBanners as $banner)
                            {{-- Slide 1 --}}
                            <div class="swiper-slide p-6 lg:p-12">
                                <div class="inline-flex items-center gap-2 bg-[#f0f7f0] px-4 py-1.5 rounded-full mb-6">
                                    <span class="text-secondary text-xs font-bold">🌿
                                        {{ $banner->badge ?? 'New Arrival' }}
                                    </span>
                                </div>
                                <div class="flex flex-col lg:flex-row justify-between items-center gap-8 w-full">
                                    <div>
                                        <h1
                                            class="font-heading text-3xl sm:text-4xl lg:text-5xl lg:text-[52px] text-slate-900 leading-[1.1] mb-6">
                                            {!! $banner->title !!}
                                        </h1>

                                        <div class="flex items-center gap-4 mb-8">
                                            <span
                                                class="text-3xl font-light text-slate-300">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                            <div class="h-px w-12 bg-slate-200"></div>
                                            <div class="text-slate-500 text-sm max-w-xs">
                                                <strong>{{ $banner->subtitle }}</strong><br>
                                                {{ $banner->description }}
                                            </div>
                                        </div>

                                        <div class="flex flex-col flex-wrap justify-start items-start gap-6">
                                            <a href="{{ $banner->button_url ?? route('products.index') }}"
                                                class="bg-primary hover:opacity-90 text-white px-8 py-4 rounded-full font-bold flex items-center gap-3 transition-all transform hover:scale-105 active:scale-95 shadow-xl shadow-green-900/20">
                                                {{ $banner->button_text ?? 'Shop All Products' }}
                                                <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="relative group">
                                        <img src="{{ $banner->image_url }}" alt="Raw Honey"
                                            class="w-full h-full lg:min-w-md lg:max-w-md aspect-square object-cover group-hover:scale-105 duration-300 transform-all">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Navigation Buttons --}}
                    <!-- Next Button -->
                    <div
                        class="swiper-button-next after:hidden flex items-center justify-center bg-[#246E231A] hover:bg-[#246E2333] backdrop-blur w-10 h-10 rounded-full shadow-md transition-colors">
                        <svg xmlns="http://www.w3.org" class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>

                    <!-- Prev Button -->
                    <div
                        class="swiper-button-prev after:hidden flex items-center justify-center bg-[#246E231A] hover:bg-[#246E2333] backdrop-blur w-10 h-10 rounded-full shadow-md transition-colors">
                        <svg xmlns="http://www.w3.org" class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </div>

                </div>

                {{-- Social Media Links - Fixed positioning --}}
                <div
                    class="absolute bottom-4 left-4 md:bottom-6 md:left-16 flex items-center gap-1 md:gap-3 text-slate-400 z-10">
                    <span class="text-sm md:text-base font-medium md:tracking-widest mr-1 md:mr-2">Follow Us
                        On:</span>

                    {{-- Facebook --}}
                    <a href="https://www.facebook.com/BionicGardenOfficial" target="_blank"
                        class="w-8 h-8 md:w-12 md:h-12 rounded-full border border-secondary/50 md:border-slate-100 flex items-center justify-center hover:bg-[#1877f2] hover:border-[#1877f2] transition-colors group">
                        <svg class="w-4 h-4 md:w-5 md:h-5 fill-current text-slate-400 group-hover:text-white"
                            viewBox="0 0 320 512">
                            <path
                                d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z" />
                        </svg>
                    </a>

                    {{-- Instagram --}}
                    <a href="#"
                        class="w-8 h-8 md:w-12 md:h-12 rounded-full border border-secondary/50 md:border-slate-100 flex items-center justify-center hover:bg-[linear-gradient(45deg,#f09433_0%,#e6683c_25%,#dc2743_50%,#cc2366_75%,#bc1888_100%)] hover:border-transparent transition-colors group">
                        <svg class="w-4 h-4 md:w-5 md:h-5 fill-current text-slate-400 group-hover:text-white"
                            viewBox="0 0 448 512">
                            <path
                                d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z" />
                        </svg>
                    </a>

                    {{-- YouTube --}}
                    <a href="https://www.youtube.com/@BionicGardenOfficial" target="_blank"
                        class="w-8 h-8 md:w-12 md:h-12 rounded-full border border-secondary/50 md:border-slate-100 flex items-center justify-center hover:bg-[#ff0000] hover:border-[#ff0000] transition-colors group">
                        <svg class="w-4 h-4 md:w-5 md:h-5 fill-current text-slate-400 group-hover:text-white"
                            viewBox="0 0 576 512">
                            <path
                                d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z" />
                        </svg>
                    </a>

                    {{-- WhatsApp --}}
                    <a href="https://wa.me/8801334943785" target="_blank"
                        class="w-8 h-8 md:w-12 md:h-12 rounded-full border border-secondary/50 md:border-slate-100 flex items-center justify-center hover:bg-[#25D366] hover:border-[#25D366] transition-colors group">
                        <svg class="w-4 h-4 md:w-5 md:h-5 fill-current text-slate-400 group-hover:text-white"
                            viewBox="0 0 448 512" stroke-width="2">
                            <path
                                d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z" />
                        </svg>
                    </a>

                    {{-- TikTok --}}
                    <a href="#"
                        class="w-8 h-8 md:w-12 md:h-12  rounded-full border border-secondary/50 md:border-slate-100 flex items-center justify-center hover:bg-black hover:border-black transition-colors group">
                        <svg class="w-4 h-4 md:w-5 md:h-5 fill-current text-slate-400 group-hover:text-white"
                            viewBox="0 0 448 512">
                            <path
                                d="M448,209.91a210.06,210.06,0,0,1-122.77-39.25V349.38A162.55,162.55,0,1,1,185,188.31V278.2a74.62,74.62,0,1,0,52.23,71.18V0l88,0a121.18,121.18,0,0,0,1.86,22.17h0A122.18,122.18,0,0,0,381,102.39a121.43,121.43,0,0,0,67,20.14Z" />
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Explore Categories  --}}
            <div class="col-span-12 lg:col-span-3 lg:row-span-1 lg:col-start-10 bg-white rounded-3xl p-6 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4">Explore Categories</h3>
                <div class="swiper categoriesSwiper">
                    <div class="swiper-wrapper">
                        @foreach ($categories as $cat)
                            <div class="swiper-slide">

                                <a href="{{ $cat->category_page }}"
                                    class="flex flex-col items-center gap-1 group cursor-pointer">
                                    <div
                                        class="w-16 h-16 rounded-full bg-slate-50 border border-secondary/50 md:border-slate-100 flex items-center justify-center group-hover:border-primary transition-all overflow-hidden">
                                        <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}"
                                            class="w-full h-full aspect-square object-contain group-hover:scale-110 duration-300 transform-all"
                                            lazy="loading">
                                    </div>
                                    <span
                                        class="text-base font-medium text-slate-400 tracking-tighter">{{ $cat->name }}</span>
                                </a>

                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- New Arrival  --}}
            <div
                class="col-span-12 lg:col-span-3 lg:row-span-1 lg:col-start-10 lg:row-start-2 bg-white rounded-3xl p-6 relative overflow-hidden group">
                <div
                    class="flex items-center justify-between gap-3 bg-white rounded-2xl group overflow-hidden relative h-full">

                    <div class="flex flex-col justify-between h-full flex-1 z-10">

                        <div>
                            <span class="text-sm font-bold text-slate-400 uppercase tracking-wider">New
                                Arrival</span>
                        </div>

                        <div class="flex-1 flex items-center">
                            <h4 class="text-sm font-bold text-slate-800 leading-tight">
                                Egyptian Medjool <br> Large Dates
                            </h4>
                        </div>

                        <a href="{{ route('product.show', 'egyptian-medjool') }}"
                            class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center transition-all duration-300 group-hover:bg-primary group-hover:text-white cursor-pointer shadow-sm">
                            <svg class="w-5 h-5 group-hover:text-white" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg" fill="currentColor">
                                <path
                                    d="M17.92 6.62a1 1 0 0 0-.54-.54A1 1 0 0 0 17 6H7a1 1 0 0 0 0 2h7.59l-8.3 8.29a1 1 0 0 0 0 1.42 1 1 0 0 0 1.42 0L16 9.41V17a1 1 0 0 0 2 0V7a1 1 0 0 0-.08-.38" />
                            </svg>
                        </a>
                    </div>

                    {{-- take middle right --}}
                    <div class="relative w-36 h-36 md:shrink-0 flex items-center">
                        <img src="{{ asset('assets/images/dates.png') }}" alt="Dates"
                            class="w-full h-full aspect-square object-contain transform group-hover:scale-110 group-hover:-rotate-6 transition-all duration-500"
                            lazy="loading">
                    </div>

                </div>
            </div>

            {{-- Offer Swiper  --}}
            {{-- <div
                class="col-span-12 lg:col-span-3 lg:row-span-2 lg:col-start-10 lg:row-start-3 rounded-3xl relative overflow-hidden bg-white">
                <div class="swiper offerSwiper w-full">
                    <div class="swiper-wrapper">
                        @foreach ([1, 2, 3] as $i)
                            <div class="swiper-slide">
                                <img src="{{ asset('assets/images/offer-' . $i . '.jpg') }}"
                                    alt="Offer {{ $i }}"
                                    class="w-full h-full aspect-4/5 object-cover rounded-lg" lazy="loading">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div> --}}
            {{-- <div
                class="col-span-12 lg:col-span-3 lg:row-span-2 lg:col-start-10 lg:row-start-3 rounded-3xl relative overflow-hidden bg-white">
                <div class="w-full">
                    <img src="{{ asset('assets/offer/products.gif') }}" alt="Offer"
                        class="w-full h-full aspect-4/5 object-cover rounded-lg" lazy="loading">
                </div>
            </div> --}}
            <div
                class="col-span-12 lg:col-span-3 lg:row-span-2 lg:col-start-10 lg:row-start-3 rounded-3xl relative overflow-hidden bg-white group/vid">
                <div class="w-full h-full">
                    <video id="hero-promo-video" src="{{ asset('assets/offer/products.mp4') }}"
                        class="w-full h-full aspect-4/5 object-cover rounded-lg" autoplay loop muted playsinline>
                        Your browser does not support the video tag.
                    </video>
                </div>

                {{-- Hover controls — visible only on hover, no layout impact --}}
                <div class="absolute inset-x-0 bottom-0 flex items-center justify-between px-3 py-2.5
                            bg-gradient-to-t from-black/55 to-transparent
                            opacity-0 group-hover/vid:opacity-100 transition-opacity duration-300">

                    {{-- Play / Pause --}}
                    <button id="hero-vid-pp" class="text-white p-1.5 rounded-full hover:bg-white/20 transition-colors">
                        <svg id="hero-pause-ic" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                            <path fill-rule="evenodd" d="M6.75 5.25a.75.75 0 0 1 .75-.75H9a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H7.5a.75.75 0 0 1-.75-.75V5.25Zm7.5 0A.75.75 0 0 1 15 4.5h1.5a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H15a.75.75 0 0 1-.75-.75V5.25Z" clip-rule="evenodd"/>
                        </svg>
                        <svg id="hero-play-ic" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 hidden">
                            <path fill-rule="evenodd" d="M4.5 5.653c0-1.427 1.529-2.33 2.779-1.643l11.54 6.347c1.295.712 1.295 2.573 0 3.286L7.28 19.99c-1.25.687-2.779-.217-2.779-1.643V5.653Z" clip-rule="evenodd"/>
                        </svg>
                    </button>

                    {{-- Mute / Unmute --}}
                    <button id="hero-vid-mute" class="text-white p-1.5 rounded-full hover:bg-white/20 transition-colors flex items-center gap-1.5">
                        <span id="hero-sound-label" class="text-[11px] font-medium hidden">Tap for sound</span>
                        <svg id="hero-muted-ic" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                            <path d="M13.5 4.06c0-1.336-1.616-2.005-2.56-1.06l-4.5 4.5H4.508c-1.141 0-2.318.664-2.66 1.905A9.76 9.76 0 0 0 1.5 12c0 .898.121 1.768.35 2.595.341 1.24 1.518 1.905 2.659 1.905H6.44l4.5 4.5c.945.945 2.561.276 2.561-1.06V4.06ZM17.78 9.22a.75.75 0 1 0-1.06 1.06L18.44 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06l1.72-1.72 1.72 1.72a.75.75 0 1 0 1.06-1.06L20.56 12l1.72-1.72a.75.75 0 1 0-1.06-1.06l-1.72 1.72-1.72-1.72Z"/>
                        </svg>
                        <svg id="hero-sound-ic" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 hidden">
                            <path d="M13.5 4.06c0-1.336-1.616-2.005-2.56-1.06l-4.5 4.5H4.508c-1.141 0-2.318.664-2.66 1.905A9.76 9.76 0 0 0 1.5 12c0 .898.121 1.768.35 2.595.341 1.24 1.518 1.905 2.659 1.905H6.44l4.5 4.5c.945.945 2.561.276 2.561-1.06V4.06ZM18.584 5.106a.75.75 0 0 1 1.06 0c3.808 3.807 3.808 9.98 0 13.788a.75.75 0 0 1-1.06-1.06 8.25 8.25 0 0 0 0-11.668.75.75 0 0 1 0-1.06ZM15.932 7.757a.75.75 0 0 1 1.061 0 6 6 0 0 1 0 8.486.75.75 0 0 1-1.06-1.061 4.5 4.5 0 0 0 0-6.364.75.75 0 0 1 0-1.06Z"/>
                        </svg>
                    </button>

                </div>
            </div>

            {{-- More Products  --}}
            <div
                class="hidden col-span-12 lg:col-span-3 lg:row-span-1 lg:row-start-4 bg-white rounded-3xl p-6 lg:flex flex-col justify-between ">
                <div class="flex justify-between items-center gap-4 ">
                    <div>
                        <h3 class="font-bold text-slate-800">More Products</h3>
                        <p class="text-xs text-slate-400">20+ Natural Items</p>
                    </div>
                    <a href="#allProducts"
                        class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center group transition-all duration-300 hover:bg-primary hover:text-white shadow-sm">
                        <svg class="w-5 h-5 hover:text-white rotate-45 group-hover:rotate-90 duration-300"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
                            <path
                                d="M17.92 6.62a1 1 0 0 0-.54-.54A1 1 0 0 0 17 6H7a1 1 0 0 0 0 2h7.59l-8.3 8.29a1 1 0 0 0 0 1.42 1 1 0 0 0 1.42 0L16 9.41V17a1 1 0 0 0 2 0V7a1 1 0 0 0-.08-.38" />
                        </svg>
                    </a>
                </div>
                <div class="flex items-center gap-2 mt-4 overflow-hidden">
                    <div class="flex -space-x-5">
                        @foreach ([1, 2, 3, 4, 5, 6] as $i)
                            <img src="{{ asset('assets/images/product-' . $i . '.png') }}"
                                alt="Product {{ $i }}"
                                class="w-16 h-16 lg:w-20 lg:h-20 rounded-2xl aspect-square object-cover border-2 border-white"
                                lazy="loading">
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Happy Customers  --}}
            <div
                class="hidden col-span-12 lg:col-span-2 lg:row-span-1 lg:col-start-4 lg:row-start-4 bg-white rounded-3xl p-6 lg:flex flex-col items-center justify-center text-center">
                <div class="flex justify-center items-center gap-1">
                    @foreach ([1, 2, 3] as $i)
                        <img src="{{ asset('assets/images/customer' . $i . '.png') }}" alt="Happy Customer"
                            class="rounded-full w-12 h-12 aspect-square object-cover border-2 border-white -ml-2 first:ml-0">
                    @endforeach
                </div>
                <div class="bg-primary text-white text-base font-black px-3 py-1 rounded-full mb-2">10k+</div>
                <p class="text-sm font-bold text-slate-800">Happy Customers</p>
                <div class="text-sm text-slate-400 mt-1">⭐ 4.9 Rating</div>
            </div>

            {{-- Certifications Swiper  --}}
            <div
                class="col-span-12 lg:col-span-4 lg:row-span-1 lg:col-start-6 lg:row-start-4 bg-white rounded-3xl p-3 md:p-6">
                <span
                    class="text-xs md:text-sm font-bold bg-primary/10 text-primary border border-secondary/10 px-2 py-1.5 md:p-2 rounded-2xl inline-block">
                    <i class="fa-solid fa-certificate text-xs md:text-sm"></i> Certifications
                </span>

                <div class="swiper certificationsSwiper">
                    <div class="swiper-wrapper">
                        @foreach ($heroCertifications as $cert)
                            <div class="swiper-slide" title="{{ $cert->name }}">
                                <img src="{{ $cert->logo_url }}" alt="{{ $cert->name }}" loading="lazy"
                                    class="w-14 h-14 lg:w-28 lg:h-28 aspect-square object-contain mx-auto hover:scale-110 transition-transform">
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
        /* ── Hero promo video controls ── */
        (function () {
            const vid        = document.getElementById('hero-promo-video');
            if (!vid) return;

            const ppBtn      = document.getElementById('hero-vid-pp');
            const muteBtn    = document.getElementById('hero-vid-mute');
            const pauseIc    = document.getElementById('hero-pause-ic');
            const playIc     = document.getElementById('hero-play-ic');
            const mutedIc    = document.getElementById('hero-muted-ic');
            const soundIc    = document.getElementById('hero-sound-ic');
            const soundLabel = document.getElementById('hero-sound-label');

            // Try to play with audio — browsers block this on cold load, so we
            // fall back to muted autoplay and show the "Tap for sound" hint.
            vid.muted = false;
            vid.play().catch(() => {
                vid.muted = true;
                vid.play().catch(() => {});
                if (soundLabel) soundLabel.classList.remove('hidden');
            });

            // Play / pause toggle
            ppBtn.addEventListener('click', () => vid.paused ? vid.play() : vid.pause());

            vid.addEventListener('play',  () => { pauseIc.classList.remove('hidden'); playIc.classList.add('hidden'); });
            vid.addEventListener('pause', () => { pauseIc.classList.add('hidden');    playIc.classList.remove('hidden'); });

            // Mute / unmute toggle
            muteBtn.addEventListener('click', () => {
                vid.muted = !vid.muted;
                mutedIc.classList.toggle('hidden', !vid.muted);
                soundIc.classList.toggle('hidden',  vid.muted);
                if (soundLabel) soundLabel.classList.add('hidden');
            });
        })();

        document.addEventListener('DOMContentLoaded', function() {
            // Common Autoplay Config to avoid repetition
            const commonAutoplay = {
                disableOnInteraction: false,
                pauseOnMouseEnter: true, // This replaces your manual event listeners
            };

            // Main Hero Swiper
            const mainSwiper = new Swiper('.mainHeroSwiper', {
                loop: true,
                autoplay: {
                    ...commonAutoplay,
                    delay: 5000
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
                    ...commonAutoplay,
                    delay: 3000
                },
                slidesPerView: 4,
                spaceBetween: 10,
                grabCursor: true,
                breakpoints: {
                    640: {
                        slidesPerView: 5
                    },
                    768: {
                        slidesPerView: 4
                    },
                    1024: {
                        slidesPerView: 4
                    },
                },
            });

            // Offer Swiper
            const offerSwiper = new Swiper('.offerSwiper', {
                loop: true,
                autoplay: {
                    ...commonAutoplay,
                    delay: 2500
                },
                effect: 'slide',
                speed: 800,
                grabCursor: true,
            });

            // Certifications Swiper 
            const certSwiper = new Swiper('.certificationsSwiper', {
                loop: true,
                autoplay: {
                    ...commonAutoplay,
                    delay: 2000
                },
                slidesPerView: 4,
                spaceBetween: 12,
                grabCursor: true,
                speed: 800,
                breakpoints: {
                    640: {
                        slidesPerView: 6
                    },
                    768: {
                        slidesPerView: 4
                    },
                    1024: {
                        slidesPerView: 5
                    },
                },
            });

        });
    </script>
@endpush

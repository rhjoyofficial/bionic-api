<section class="w-full bg-white py-12">
    <div class="max-w-5xl mx-auto px-4 md:px-8">
        <div
            class="relative w-full aspect-video overflow-hidden rounded-2xl md:rounded-3xl bg-gray-900 shadow-2xl group">

            <img src="{{ asset('assets/video-thumbnail.jpg') }}" alt="Product Showcase Video"
                class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105 opacity-80">

            <div class="absolute inset-0 bg-linear-to-t from-black/40 via-transparent to-black/20"></div>

            <div class="absolute inset-0 flex items-center justify-center">
                <button
                    class="relative flex items-center justify-center w-20 h-20 md:w-28 md:h-28 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white transition-all duration-500 group-hover:bg-primary group-hover:border-primary group-hover:scale-110 cursor-pointer shadow-[0_0_50px_rgba(0,0,0,0.3)]">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                        class="w-10 h-10 md:w-14 md:h-14 ml-1">
                        <path fill-rule="evenodd"
                            d="M4.5 5.653c0-1.427 1.529-2.33 2.779-1.643l11.54 6.347c1.295.712 1.295 2.573 0 3.286L7.28 19.99c-1.25.687-2.779-.217-2.779-1.643V5.653Z"
                            clip-rule="evenodd" />
                    </svg>

                    <span
                        class="absolute inset-0 rounded-full bg-white/30 animate-ping opacity-20 group-hover:hidden"></span>
                </button>
            </div>

            <div class="absolute bottom-6 left-6 md:bottom-10 md:left-10 text-white">
                <p class="font-heading text-lg md:text-2xl font-bold tracking-wide drop-shadow-lg">
                    Experience the Bionic Garden
                </p>
                <p class="text-sm md:text-base opacity-90 font-sans">100% Organic & Naturally Sourced</p>
            </div>

            <a href="#" class="absolute inset-0 z-10" aria-label="Play Video"></a>
        </div>
    </div>
</section>

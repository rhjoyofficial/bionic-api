<section class="py-16 px-4 md:px-8 bg-gray-50/50">
    <div class="max-w-8xl mx-auto">

        <div class="flex items-center justify-between gap-6 mb-4 md:mb-8 pb-4">
            <div class="max-w-2xl md:shrink-0">
                <h2 class="font-heading text-2xl md:text-4xl font-bold text-brand mb-2 md:mb-4">
                    Exclusive Combo Packs
                </h2>
                <p class="text-gray-600 text-center md:text-left font-sans">
                    Save more with our carefully curated organic bundles.
                </p>
            </div>
            <span class="h-0.5 w-full bg-gray-200 hidden md:block"></span>
            <div class="shrink-0 hidden md:block">
                <div
                    class="relative transition-all duration-300 border rounded-2xl border-primary/20 group/btn hover:bg-primary hover:border-primary">
                    <a href="#"
                        class="flex items-center gap-3 px-4 py-2 transition-all duration-300 text-primary hover:text-white!">
                        <span class="text-sm font-bold tracking-tight md:text-base">View All</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                            class="w-4 h-4 transition-all duration-300 transform group-hover/btn:translate-x-1">
                            <path d="M5 12h14m-7-7 7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 md:gap-4">
            @foreach (range(1, 9) as $i)
                <x-combo-card :i="$i" />
            @endforeach
        </div>
    </div>
</section>

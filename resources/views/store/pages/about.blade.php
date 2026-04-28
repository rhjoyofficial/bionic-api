@extends('layouts.app')

@section('title', $data['title'])

@section('content')
    <!-- Elegant, Light-Themed Cinematic Design -->
    <div
        class="bg-[#f0f5f1] min-h-screen text-slate-600 font-light overflow-hidden selection:bg-primary/30 shadow-2xl border-x border-slate-200">

        <section
            class="relative h-100 flex items-center justify-center border-b border-slate-200 bg-primary overflow-hidden">
            <div class="absolute inset-0 z-0">
                <img src="https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&q=80&w=2000"
                    class="w-full h-full object-cover opacity-20 scale-105" alt="Nature">
                <div class="absolute inset-0 bg-linear-to-b from-transparent via-primary/80 to-primary"></div>
            </div>

            <div class="z-10 text-center px-4 max-w-4xl mx-auto mt-4">
                <p class="text-yellow-400 uppercase tracking-[0.4em] text-[10px] font-medium mb-3">{{ $data['brand_name'] }}
                    • {{ $data['slogan'] }}</p>

                <h1
                    class="text-4xl md:text-5xl lg:text-6xl font-playfair text-white mb-4 leading-tight tracking-tight drop-shadow-2xl">
                    Pure. Powerful. <br>
                    <span class="italic text-green-100">Nature-Driven.</span>
                </h1>

                <p class="text-sm md:text-base text-green-50 max-w-lg mx-auto leading-relaxed mb-6 font-light line-clamp-2">
                    {{ $data['description'] }}
                </p>

                <div class="flex flex-wrap justify-center gap-6">
                    @foreach ($data['values_tags'] as $tag)
                        <span class="text-[9px] uppercase tracking-[0.2em] text-white flex items-center gap-2">
                            <span class="w-1 h-1 bg-yellow-400 rounded-full"></span>
                            {{ $tag }}
                        </span>
                    @endforeach
                </div>
            </div>

        </section>

        <div class="max-w-8xl mx-auto px-4 py-12 space-y-16">

            <!-- What We Offer -->
            <section>
                <div class="flex flex-col md:flex-row gap-8 items-end mb-12">
                    <div class="md:w-1/3">
                        <h2 class="text-4xl md:text-5xl font-playfair text-slate-900 tracking-wide">What We Offer</h2>
                        <div class="w-16 h-px bg-primary mt-6"></div>
                    </div>
                    <div class="md:w-2/3">
                        <p
                            class="text-lg leading-relaxed max-w-2xl text-slate-500 font-light border-l border-slate-200 pl-6">
                            Curating the finest natural elements to elevate your daily routine, uncompromising on purity and
                            design.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-10 gap-y-12">
                    @foreach ($data['offerings'] as $offering)
                        <div
                            class="group border-t border-slate-200 pt-6 hover:border-primary transition-colors duration-700">
                            <div
                                class="text-2xl text-primary/70 mb-6 group-hover:text-primary transition-colors duration-700 transform group-hover:-translate-y-1">
                                <i class="{{ $offering['icon'] }}"></i>
                            </div>
                            <h3 class="text-xl text-slate-900 font-playfair mb-3 tracking-wide">{{ $offering['title'] }}
                            </h3>
                            <p class="text-sm leading-relaxed text-slate-600 font-light uppercase tracking-wider">
                                {{ $offering['items'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </section>

            <!-- Parent Brand (Bor de Guna) -->
            <section class="relative">
                <div class="grid md:grid-cols-12 gap-12 items-center">
                    <div class="md:col-span-5 relative group">
                        <div class="aspect-[3/4] overflow-hidden shadow-xl">
                            <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&q=80&w=800"
                                class="w-full h-full object-cover grayscale opacity-80 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-[2s] scale-105 group-hover:scale-100"
                                alt="Bor de Guna Vision">
                        </div>
                        <div class="absolute -bottom-6 -right-6 bg-white border border-slate-100 p-8 shadow-xl z-10">
                            <p class="text-sm uppercase tracking-[0.2em] font-medium text-slate-500 mb-2">Established</p>
                            <p class="text-4xl font-playfair text-primary tracking-widest">
                                {{ $data['parent_brand']['founded'] }}</p>
                        </div>
                    </div>
                    <div class="md:col-span-7 md:pl-12">
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-primary mb-6">Our Heritage</p>
                        <h2
                            class="text-4xl md:text-5xl lg:text-6xl font-playfair text-slate-900 mb-8 leading-tight tracking-wide">
                            A Sub-Brand of <br>
                            <span class="italic text-primary">{{ $data['parent_brand']['name'] }}</span>
                        </h2>
                        <div class="space-y-6 text-lg leading-relaxed text-slate-600 font-light max-w-xl">
                            <p class="">{{ $data['parent_brand']['vision'] }}</p>
                            <blockquote
                                class="border-l-2 border-primary/40 pl-6 my-8 italic text-slate-800 text-xl leading-relaxed">
                                "{{ $data['parent_brand']['mission'] }}"
                            </blockquote>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Core Pillars -->
            <section class="border-y border-slate-200 py-16 bg-white max-w-8xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-base font-bold font-sans text-primary tracking-[0.2em] uppercase mb-4">Core Pillars</h2>
                </div>
                <div class="flex flex-wrap justify-center gap-10 md:gap-16 max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
                    @foreach ($data['pillars'] as $pillar)
                        <div class="text-center group cursor-default">
                            <p
                                class="text-lg text-primary/50 tracking-[0.1em] mb-2 font-mono group-hover:text-primary transition-colors duration-500">
                                0{{ $loop->iteration }}</p>
                            <h3
                                class="text-lg uppercase tracking-[0.1em] font-semibold text-slate-700 group-hover:text-slate-900 transition-colors duration-500">
                                {{ $pillar }}</h3>
                        </div>
                    @endforeach
                </div>
            </section>

            <!-- Founder Section (Cinematic Split but Light/Brand Colored) -->
            <section>
                <div class="grid lg:grid-cols-12 gap-0 border border-slate-200 bg-white shadow-xl">
                    <div class="lg:col-span-6 p-8 md:p-12 lg:p-16 flex flex-col justify-center">
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-primary mb-6">The Visionary</p>
                        <h2 class="text-4xl md:text-5xl font-playfair text-slate-900 mb-4 leading-tight tracking-tight">
                            {{ $data['founder']['name'] }}</h2>
                        <p class="text-xl text-slate-500 italic font-playfair mb-8 tracking-wide">
                            {{ $data['founder']['short_name'] }}</p>

                        <p class="text-lg leading-relaxed text-slate-600 font-light mb-10 max-w-lg">
                            {{ $data['founder']['bio'] }}
                        </p>

                        <div class="flex flex-wrap gap-3 mb-12">
                            @foreach ($data['founder']['expertise'] as $skill)
                                <span
                                    class="px-5 py-2.5 border border-slate-200 text-sm font-medium uppercase tracking-[0.1em] text-slate-500 hover:border-primary hover:text-primary transition-all duration-300">
                                    {{ $skill }}
                                </span>
                            @endforeach
                        </div>

                        <div class="mt-auto pt-8 border-t border-slate-100">
                            <p class="text-sm font-medium uppercase tracking-[0.2em] text-slate-400 mb-2">Designation</p>
                            <p class="text-base font-bold text-slate-800 uppercase tracking-widest">
                                {{ $data['founder']['designation'] }}</p>
                        </div>
                    </div>

                    <div class="lg:col-span-6 aspect-[3/4] lg:aspect-auto relative overflow-hidden group">
                        <img src="{{ $data['founder']['image'] }}"
                            class="w-full h-full object-cover object-center grayscale contrast-125 opacity-90 group-hover:scale-105 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-[3s]"
                            alt="{{ $data['founder']['name'] }}">
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent lg:hidden">
                        </div>
                    </div>
                </div>
            </section>

            <!-- Video Gallery (3 per row, aspect-video) -->
            <section>
                <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-8">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-primary mb-4">Our Visual Journey</p>
                        <h2 class="text-3xl md:text-4xl font-playfair text-slate-900 tracking-wide">Cinematic Archives</h2>
                    </div>
                    <div class="hidden md:block w-48 h-px bg-slate-200 mb-4"></div>
                </div>

                <!-- 3 Columns -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach ($data['videos'] as $video)
                        <div
                            class="group relative bg-white border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-500 overflow-hidden rounded-xl">
                            <div class="aspect-video w-full border-b border-slate-100" data-video
                                data-video-type="{{ $video['type'] }}" data-video-src="{{ $video['src'] }}"
                                data-video-thumbnail="{{ $video['thumbnail'] }}" data-video-title="{{ $video['title'] }}"
                                data-video-badge="{{ $video['badge'] }}">
                            </div>
                            <div class="p-6">
                                <div class="flex items-center gap-4 mb-3">
                                    <span class="w-4 h-px bg-primary/50"></span>
                                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">
                                        {{ $video['badge'] }}</p>
                                </div>
                                <h3
                                    class="text-lg font-playfair text-slate-900 tracking-wide group-hover:text-primary transition-colors duration-300">
                                    {{ $video['title'] }}</h3>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <!-- Gallery CTA -->
            <section
                class="relative py-24 border border-slate-200 overflow-hidden flex items-center justify-center text-center group bg-primary rounded-xl">
                <div class="absolute inset-0 z-0 opacity-20 group-hover:opacity-40 transition-opacity duration-[2s]">
                    <img src="https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&q=80&w=2000"
                        class="w-full h-full object-cover scale-110 group-hover:scale-100 transition-transform duration-[5s]"
                        alt="Background">
                    <div class="absolute inset-0 bg-primary/60"></div>
                </div>
                <div class="relative z-10 max-w-2xl px-4">
                    <h2 class="text-4xl md:text-5xl font-playfair text-white mb-6 tracking-wide">Enter the Gallery</h2>
                    <p class="text-lg text-green-50 font-light mb-10 leading-relaxed max-w-lg mx-auto">
                        A curated collection of imagery, documentaries, and literature that define our legacy.
                    </p>
                    <a href="{{ $data['gallery_link'] }}"
                        class="inline-block border border-white/40 text-sm font-bold uppercase tracking-[0.2em] text-white px-10 py-4 hover:bg-white hover:text-primary transition-colors duration-500 rounded-sm">
                        Discover More
                    </a>
                </div>
            </section>

        </div>
    </div>
@endsection

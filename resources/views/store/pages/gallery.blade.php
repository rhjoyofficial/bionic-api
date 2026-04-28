@extends('layouts.app')

@section('title', $data['title'])

@section('content')
    <!-- Elegant, Light-Themed Cinematic Design -->
    <div
        class="bg-[#f0f5f1] min-h-screen text-slate-600 font-light overflow-hidden selection:bg-primary/30 shadow-2xl bg-white border-x border-slate-200">

        <!-- Cinematic Hero -->
        <section
            class="relative h-100 flex items-center justify-center border-b border-slate-200 bg-primary overflow-hidden group">
            <div class="absolute inset-0 z-0">
                <img src="https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&q=80&w=2000"
                    class="w-full h-full object-cover opacity-20 scale-110 group-hover:scale-100 transition-transform duration-[7s] ease-out"
                    alt="Gallery">

                <div class="absolute inset-0 bg-gradient-to-tr from-primary via-transparent to-primary/30"></div>
                <div class="absolute inset-0 bg-linear-to-b from-transparent via-primary/60 to-primary"></div>
            </div>

            <div class="absolute inset-12 pointer-events-none">
                <div class="absolute top-0 left-0 w-4 h-4 border-t border-l border-white/20"></div>
                <div class="absolute top-0 right-0 w-4 h-4 border-t border-r border-white/20"></div>
                <div class="absolute bottom-0 left-0 w-4 h-4 border-b border-l border-white/20"></div>
                <div class="absolute bottom-0 right-0 w-4 h-4 border-b border-r border-white/20"></div>
            </div>

            <div class="relative z-10 text-center px-4 max-w-4xl mx-auto">
                <div class="flex items-center justify-center gap-4 mb-4">
                    <span class="h-px w-4 bg-yellow-400/30"></span>
                    <p class="text-yellow-400 uppercase tracking-[0.4em] text-[10px] font-semibold">Visual Archives</p>
                    <span class="h-px w-4 bg-yellow-400/30"></span>
                </div>

                <h1
                    class="text-4xl md:text-5xl lg:text-6xl font-playfair text-white mb-6 leading-[1.1] tracking-tight drop-shadow-2xl">
                    {{ $data['title'] }}
                </h1>

                <p class="text-sm md:text-base text-green-50/80 max-w-lg mx-auto leading-relaxed font-light line-clamp-2">
                    {{ $data['subtitle'] }}
                </p>

                <div
                    class="mt-8 flex justify-center gap-12 text-[9px] uppercase tracking-[0.2em] text-white/40 font-medium">
                    <div class="flex flex-col gap-1">
                        <span class="text-yellow-400/80">Est.</span>
                        <span>2026</span>
                    </div>
                    <div class="w-px h-8 bg-white/10"></div>
                    <div class="flex flex-col gap-1">
                        <span class="text-yellow-400/80">Format</span>
                        <span>Analog/Digital</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Gallery Grid -->
        <div class="px-4 py-24 max-w-8xl mx-auto">

            <!-- Masonry-style Grid -->
            <div class="columns-1 md:columns-2 lg:columns-3 gap-8 space-y-8">
                @foreach ($data['items'] as $item)
                    @if ($item['type'] === 'video')
                        <div class="relative group cursor-pointer break-inside-avoid overflow-hidden bg-white shadow-sm hover:shadow-2xl transition-all duration-700"
                            data-video data-video-type="youtube" data-video-src="{{ $item['src'] }}">

                            <!-- Using an aspect ratio to keep videos somewhat uniform in masonry, but allowing variation -->
                            <div class="relative overflow-hidden">
                                <img src="{{ $item['thumbnail'] }}" alt="{{ $item['title'] }}"
                                    class="w-full object-cover grayscale opacity-80 group-hover:grayscale-0 group-hover:opacity-100 group-hover:scale-105 transition-all duration-[2s]">

                                <!-- Video Play Overlay -->
                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-20">
                                    <div
                                        class="w-16 h-16 rounded-full bg-white/10 backdrop-blur-md border border-white/30 flex items-center justify-center group-hover:scale-110 group-hover:bg-primary group-hover:border-primary transition-all duration-500">
                                        <i class="fa-solid fa-play text-white ml-1"></i>
                                    </div>
                                </div>

                                <!-- Info Overlay -->
                                <div
                                    class="absolute bottom-0 left-0 right-0 p-8 bg-gradient-to-t from-slate-900 via-slate-900/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700 z-10 translate-y-4 group-hover:translate-y-0">
                                    <span
                                        class="text-[9px] uppercase tracking-[0.2em] text-yellow-400 mb-2 block">{{ $item['badge'] }}</span>
                                    <h3 class="text-white font-playfair text-xl leading-snug">{{ $item['title'] }}</h3>
                                </div>
                            </div>
                        </div>
                    @else
                        <div
                            class="relative group cursor-pointer break-inside-avoid overflow-hidden bg-white shadow-sm hover:shadow-2xl transition-all duration-700">
                            <div class="relative overflow-hidden">
                                <img src="{{ $item['src'] }}" alt="{{ $item['title'] }}"
                                    class="w-full object-cover grayscale opacity-80 group-hover:grayscale-0 group-hover:opacity-100 group-hover:scale-105 transition-all duration-[2s]">

                                <!-- Info Overlay -->
                                <div
                                    class="absolute bottom-0 left-0 right-0 p-8 bg-gradient-to-t from-slate-900 via-slate-900/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700 z-10 translate-y-4 group-hover:translate-y-0">
                                    <span
                                        class="text-[9px] uppercase tracking-[0.2em] text-yellow-400 mb-2 block">{{ $item['badge'] }}</span>
                                    <h3 class="text-white font-playfair text-xl leading-snug">{{ $item['title'] }}</h3>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

@endsection

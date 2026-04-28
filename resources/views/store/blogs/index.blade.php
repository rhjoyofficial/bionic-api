@extends('layouts.app')

@section('title', $data['title'])

@section('content')
    @push('styles')
        <style>
            .font-serif {
                font-family: 'Playfair Display', 'Merriweather', Georgia, serif;
            }
        </style>
    @endpush
    <!-- Elegant, Light-Themed Cinematic Design -->
    <div
        class="bg-[#f0f5f1] min-h-screen text-slate-600 font-light overflow-hidden selection:bg-primary/30 shadow-2xl bg-white border-x border-slate-200">

        <section
            class="relative h-100 flex items-center justify-center border-b border-slate-200 bg-primary overflow-hidden">
            <div class="absolute inset-0 z-0">
                <img src="https://images.unsplash.com/photo-1490818387583-1baba5e638af?auto=format&fit=crop&q=80&w=2000"
                    class="w-full h-full object-cover opacity-20 scale-105" alt="Blog Background">
                <div class="absolute inset-0 bg-linear-to-b from-transparent via-primary/80 to-primary"></div>
            </div>

            <div class="relative z-10 text-center px-4 max-w-3xl mx-auto">
                <p class="text-yellow-400 uppercase tracking-[0.4em] text-[10px] font-medium mb-3">Editorial</p>

                <h1
                    class="text-4xl md:text-5xl lg:text-6xl font-serif text-white mb-4 leading-tight tracking-tight drop-shadow-2xl">
                    {{ $data['title'] }}
                </h1>

                <p class="text-sm md:text-base text-green-50 max-w-xl mx-auto leading-relaxed font-light line-clamp-3">
                    {{ $data['subtitle'] }}
                </p>
            </div>
        </section>

        <!-- Main Content -->
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-12">
                @foreach ($data['posts'] as $post)
                    <div class="group relative bg-white rounded-2xl transition-all duration-500 hover:-translate-y-2">

                        <div
                            class="absolute -top-4 -right-4 w-24 h-24 bg-primary/5 rounded-full blur-2xl group-hover:bg-yellow-400/10 transition-colors duration-500">
                        </div>

                        <div class="relative overflow-hidden aspect-[16/10] rounded-2xl shadow-sm z-10">
                            <img src="{{ $post['image'] }}" alt="{{ $post['title'] }}"
                                class="w-full h-full object-cover grayscale opacity-90 group-hover:grayscale-0 group-hover:opacity-100 group-hover:scale-110 transition-all duration-[1.5s] ease-out">

                            <div class="absolute top-4 left-4 overflow-hidden rounded-full">
                                <span
                                    class="relative z-10 bg-white/20 backdrop-blur-md text-white text-[8px] font-bold px-5 py-2 uppercase tracking-[0.2em] border border-white/30 inline-block">
                                    {{ $post['category'] }}
                                </span>
                            </div>

                            <div
                                class="absolute inset-0 bg-gradient-to-t from-primary/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            </div>
                        </div>

                        <div class="pt-8 px-2 flex flex-col">
                            <div class="flex items-center gap-4 text-[9px] uppercase tracking-[0.2em] text-slate-400 mb-5">
                                <span
                                    class="flex items-center gap-2 group-hover:text-primary transition-colors cursor-default">
                                    <i class="fa-regular fa-calendar-check text-yellow-500"></i> {{ $post['date'] }}
                                </span>
                                <div class="w-px h-3 bg-slate-200"></div>
                                <span class="flex items-center gap-2 uppercase font-medium">{{ $post['author'] }}</span>
                            </div>

                            <h2
                                class="text-2xl font-serif text-slate-900 mb-4 line-clamp-2 leading-tight group-hover:text-primary transition-colors duration-300">
                                {{ $post['title'] }}
                            </h2>

                            <p class="text-slate-500 text-sm mb-8 line-clamp-3 leading-relaxed font-light">
                                {{ $post['excerpt'] }}
                            </p>

                            <div class="mt-auto">
                                <a href="#"
                                    class="inline-flex items-center gap-4 text-[10px] uppercase tracking-[0.3em] text-primary font-bold group/link">
                                    <span class="relative">
                                        Read Article
                                        <span
                                            class="absolute -bottom-1 left-0 w-0 h-[1px] bg-primary group-hover/link:w-full transition-all duration-500"></span>
                                    </span>
                                    <div
                                        class="w-8 h-8 rounded-full border border-slate-100 flex items-center justify-center group-hover/link:bg-primary group-hover/link:text-white transition-all duration-300">
                                        <i class="fa-solid fa-arrow-right-long text-[8px]"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

@endsection

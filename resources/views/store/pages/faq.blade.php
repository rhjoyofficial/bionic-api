@extends('layouts.app')

@section('title', $data['title'])

@section('content')
    <!-- Elegant, Light-Themed Cinematic Design -->
    <div
        class="bg-[#f0f5f1] min-h-screen text-slate-600 font-light overflow-hidden selection:bg-primary/30 shadow-2xl bg-white border-x border-slate-200">

        <!-- Cinematic Hero -->
        <section
            class="relative h-100 flex items-center justify-center border-b border-slate-200 bg-primary overflow-hidden">
            <div class="absolute inset-0 z-0">
                <img src="https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&q=80&w=2000"
                    class="w-full h-full object-cover opacity-20 scale-105" alt="FAQ Background">
                <div class="absolute inset-0 bg-linear-to-b from-transparent via-primary/80 to-primary"></div>
            </div>

            <div class="relative z-10 text-center px-4 max-w-3xl mx-auto mt-4">
                <p class="text-yellow-400 uppercase tracking-[0.4em] text-[10px] font-medium mb-3">Support</p>
                <h1
                    class="text-4xl md:text-5xl lg:text-6xl font-playfair text-white mb-4 leading-tight tracking-tight drop-shadow-2xl">
                    {{ $data['title'] }}
                </h1>
                <p class="text-sm md:text-base text-green-50 max-w-xl mx-auto leading-relaxed font-light">
                    {{ $data['subtitle'] }}
                </p>
            </div>
        </section>

        <!-- Content -->
        <div class="px-4 py-24 max-w-5xl mx-auto">
            <div class="space-y-16">
                @foreach ($data['categories'] as $categoryIndex => $category)
                    <div class="bg-white border border-slate-100 shadow-xl p-8 md:p-16">
                        <h2
                            class="text-3xl font-playfair text-slate-900 mb-8 border-b pb-6 border-slate-100 flex items-center gap-4">
                            <span class="w-1.5 h-8 bg-primary"></span>
                            {{ $category['name'] }}
                        </h2>
                        <div class="space-y-6">
                            @foreach ($category['items'] as $itemIndex => $item)
                                <div
                                    class="border border-slate-100 bg-[#f0f5f1]/50 p-8 hover:border-primary/30 transition-colors duration-500">
                                    <h3 class="font-playfair text-xl text-slate-900 mb-4 flex items-start gap-4">
                                        <span class="text-primary mt-1 text-sm"><i
                                                class="fa-solid fa-circle-question"></i></span>
                                        {{ $item['q'] }}
                                    </h3>
                                    <p
                                        class="text-lg text-slate-600 leading-relaxed font-light pl-8 border-l border-primary/20 ml-2.5">
                                        {{ $item['a'] }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-20 text-center bg-primary text-white p-16 shadow-xl relative overflow-hidden group">
                <div class="absolute inset-0 z-0 opacity-10 group-hover:opacity-20 transition-opacity duration-1000">
                    <img src="https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&q=80&w=2000"
                        class="w-full h-full object-cover">
                </div>
                <div class="relative z-10">
                    <h3 class="text-3xl font-playfair mb-4">Still have questions?</h3>
                    <p class="text-green-50 mb-10 font-light text-lg">Can't find the answer you're looking for? Please chat
                        to our friendly team.</p>
                    <a href="{{ route('contact') }}"
                        class="inline-block border border-white/40 text-sm font-bold uppercase tracking-[0.2em] px-10 py-4 hover:bg-white hover:text-primary transition-colors duration-500">
                        Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

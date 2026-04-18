@extends('layouts.app')

@section('title', 'Page Not Found')
@section('meta_description', 'The page you are looking for could not be found.')

@section('content')
    <section class="min-h-[70vh] flex items-center justify-center px-4 py-20 bg-[#f0f5f1]">
        <div class="text-center max-w-lg">

            {{-- 404 Graphic --}}
            <div class="relative inline-flex items-center justify-center mb-8">
                <span class="text-[9rem] font-extrabold text-primary leading-none select-none">404</span>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-20 h-20 rounded-full bg-green-700/10 flex items-center justify-center">
                        <i class="fa-solid fa-leaf text-green-600 text-3xl"></i>
                    </div>
                </div>
            </div>

            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">
                Page Not Found
            </h1>
            <p class="text-gray-500 text-base mb-8 leading-relaxed">
                The page you're looking for doesn't exist or may have been moved.
                Let's get you back on track.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('home') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-green-700 hover:bg-green-800 text-white font-semibold text-sm transition-all duration-200 shadow-sm hover:shadow-md">
                    <i class="fa-solid fa-house text-xs"></i>
                    Back to Home
                </a>
                <a href="{{ route('products.index') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 font-semibold text-sm transition-all duration-200">
                    <i class="fa-solid fa-bag-shopping text-xs"></i>
                    Browse Products
                </a>
            </div>

        </div>
    </section>
@endsection

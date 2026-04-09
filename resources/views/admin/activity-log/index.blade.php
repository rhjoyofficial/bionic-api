@extends('layouts.app')

@section('title', 'Combo Packs')

@section('content')
    <section class="py-10 md:py-14 px-4 md:px-8">
        <div class="max-w-8xl mx-auto">
            <nav class="flex text-gray-500 text-xs md:text-sm mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li><a href="/" class="hover:text-primary">Home</a></li>
                    <li><span class="mx-2">›</span></li>
                    <li class="text-gray-800 font-medium">Combos</li>
                </ol>
            </nav>

            <header class="mb-8">
                <h1 class="text-2xl md:text-4xl font-bold text-gray-900 mb-2">All Combo Packs</h1>
                <p class="text-gray-600 text-sm md:text-base">Browse every active bundle and add your favorite combos to
                    cart.</p>
            </header>

            @if ($combos->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 md:gap-4">
                    @foreach ($combos as $combo)
                        <x-combo-card :combo="$combo" />
                    @endforeach
                </div>

                <div class="mt-10 flex justify-center">
                    {{ $combos->links() }}
                </div>
            @else
                <div class="rounded-2xl border border-gray-100 bg-white p-8 text-center text-gray-500">
                    No combos available right now.
                </div>
            @endif
        </div>
    </section>
@endsection

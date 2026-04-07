@extends('layouts.app')

@section('title', 'Order Failed')

@section('content')
    <section class="bg-[#f0f5f1] min-h-screen flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-7xl">
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <h1 class="text-4xl font-bold text-red-600 mb-4">Order Failed</h1>
                <p class="text-gray-700 mb-6">Unfortunately, your order could not be processed at this time. Please try again
                    later or contact our support team for assistance.</p>
                <a href="{{ route('home') }}"
                    class="inline-block bg-primary text-white font-semibold px-6 py-3 rounded-full hover:bg-primary/90 transition">
                    Return to Home
                </a>
            </div>
        </div>
    </section>
@endsection

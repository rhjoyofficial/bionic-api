@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
    <div class="flex items-center justify-center min-h-[85vh] bg-gray-50 px-4 sm:px-6 lg:px-8 font-sans">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">

            {{-- Logo & Header --}}
            <div class="text-center">
                <a href="{{ url('/') }}" class="inline-block">
                    <img class="mx-auto h-16 w-auto" src="{{ asset('assets/images/bionic-logo.png') }}" alt="Bionic Garden Logo">
                </a>
                <h2 class="mt-6 text-3xl font-bold tracking-tight text-gray-900 font-['Plus_Jakarta_Sans']">
                    Forgot Password?
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Enter your email and we'll send you a reset link.
                </p>
            </div>

            {{-- Success Message --}}
            <div id="forgot-success"
                class="hidden p-3 text-sm text-green-700 bg-green-50 rounded-lg border border-green-200 font-['Noto_Sans_Bengali'] text-center">
            </div>

            {{-- Error Message --}}
            <div id="forgot-error"
                class="hidden p-3 text-sm text-red-600 bg-red-50 rounded-lg border border-red-100 font-['Noto_Sans_Bengali'] text-center">
            </div>

            {{-- Form --}}
            <form id="forgotForm" class="mt-8 space-y-6" novalidate>
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required
                            placeholder="আপনার ইমেইল লিখুন"
                            class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-green-600 focus:border-green-600 sm:text-sm transition-colors font-['Noto_Sans_Bengali'] placeholder:font-['Noto_Sans_Bengali']">
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-600 transition-all transform active:scale-95 disabled:opacity-70">
                        Send Reset Link
                    </button>
                </div>
            </form>

            {{-- Back to Login --}}
            <div class="mt-6 text-center text-sm">
                <p class="text-gray-600">
                    Remember your password?
                    <a href="{{ route('login') }}" class="font-bold text-green-600 hover:text-green-700 transition-colors">
                        Sign in here
                    </a>
                </p>
            </div>
        </div>
    </div>
@endsection

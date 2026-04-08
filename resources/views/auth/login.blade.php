@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="flex items-center justify-center min-h-[85vh] bg-gray-50 px-4 sm:px-6 lg:px-8 font-sans">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">

            {{-- Logo & Header --}}
            <div class="text-center">
                <a href="{{ url('/') }}" class="inline-block">
                    <img class="mx-auto h-16 w-auto" src="{{ asset('assets/images/bionic-logo.png') }}"
                        alt="Bionic Garden Logo">
                </a>
                <h2 class="mt-6 text-3xl font-bold tracking-tight text-gray-900 font-['Plus_Jakarta_Sans']">
                    Welcome back
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Sign in to your account to continue
                </p>
            </div>

            {{-- Error Container --}}
            <div id="error-message"
                class="hidden p-3 text-sm text-red-600 bg-red-50 rounded-lg border border-red-100 font-['Noto_Sans_Bengali'] text-center">
            </div>

            {{-- Form --}}
            <form id="loginForm" class="mt-8 space-y-6" action="#" method="POST">
                @csrf

                <div class="space-y-5">
                    {{-- Email or Phone Field --}}
                    <div>
                        <label for="login" class="block text-sm font-medium text-gray-700">Email or Phone Number</label>
                        <div class="mt-1">
                            <input id="login" name="email" type="text" required
                                placeholder="আপনার ইমেইল বা ফোন নম্বর লিখুন"
                                class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-green-600 focus:border-green-600 sm:text-sm transition-colors font-['Noto_Sans_Bengali'] placeholder:font-['Noto_Sans_Bengali']">
                        </div>
                    </div>

                    {{-- Password Field with Toggle --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="mt-1 relative">
                            <input id="password" name="password" type="password" autocomplete="current-password" required
                                placeholder="আপনার পাসওয়ার্ড লিখুন"
                                class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-green-600 focus:border-green-600 sm:text-sm transition-colors font-['Noto_Sans_Bengali'] placeholder:font-['Noto_Sans_Bengali']">

                            <button type="button" onclick="togglePassword()"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-green-600 transition-colors">
                                <i id="password-icon" class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember" type="checkbox"
                            class="h-4 w-4 text-green-600 focus:ring-green-600 border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="#" class="font-medium text-green-600 hover:text-green-700 transition-colors">
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" id="submitBtn"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-600 transition-all transform active:scale-95 disabled:opacity-70">
                        Sign in
                    </button>
                </div>
            </form>

            {{-- Register Link --}}
            <div class="mt-6 text-center text-sm">
                <p class="text-gray-600">
                    Don't have an account?
                    <a href="{{ route('register') }}"
                        class="font-bold text-green-600 hover:text-green-700 transition-colors">
                        Sign up here
                    </a>
                </p>
            </div>
        </div>
    </div>
@endsection


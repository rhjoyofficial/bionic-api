<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Login &mdash; {{ config('app.name') }}</title>

    <link rel="icon" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 font-[Inter] antialiased">

    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">

            {{-- Logo --}}
            <div class="text-center mb-8">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('assets/images/bionic-logo.png') }}" alt="{{ config('app.name') }}"
                         class="h-14 mx-auto">
                </a>
                <h1 class="mt-4 text-xl font-bold text-gray-800">Admin Panel</h1>
                <p class="text-sm text-gray-500 mt-1">Sign in to continue</p>
            </div>

            {{-- Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">

                {{-- Validation errors --}}
                @if ($errors->any())
                    <div class="mb-6 p-3 bg-red-50 border border-red-200 rounded-lg">
                        @foreach ($errors->all() as $error)
                            <p class="text-sm text-red-600">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                {{-- Status message (e.g. "You have been logged out") --}}
                @if (session('status'))
                    <div class="mb-6 p-3 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm text-green-700">{{ session('status') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.submit') }}">
                    @csrf

                    {{-- Email / Phone --}}
                    <div class="mb-5">
                        <label for="login" class="block text-sm font-medium text-gray-700 mb-1">
                            Email or Phone
                        </label>
                        <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus
                               autocomplete="username"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                                      focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-green-600
                                      placeholder-gray-400 transition"
                               placeholder="admin@example.com">
                    </div>

                    {{-- Password --}}
                    <div class="mb-5">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Password
                        </label>
                        <input id="password" name="password" type="password" required
                               autocomplete="current-password"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                                      focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-green-600
                                      placeholder-gray-400 transition"
                               placeholder="Enter your password">
                    </div>

                    {{-- Remember me --}}
                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember"
                                   class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-600">
                            <span class="text-sm text-gray-600">Remember me</span>
                        </label>
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                            class="w-full py-2.5 px-4 bg-green-700 text-white text-sm font-semibold rounded-lg
                                   hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-600
                                   focus:ring-offset-2 transition active:scale-[0.98]">
                        Sign In
                    </button>
                </form>
            </div>

            <p class="text-center text-xs text-gray-400 mt-6">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>

</body>
</html>

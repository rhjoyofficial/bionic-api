<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard') &mdash; {{ config('app.name') }} Admin</title>

    <link rel="icon" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    @stack('styles')
</head>

<body class="h-full bg-gray-50 font-[Inter] antialiased" x-data="{ sidebarOpen: false }">

    {{-- Mobile sidebar overlay --}}
    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 bg-black/50 lg:hidden" @click="sidebarOpen = false"
        x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    {{-- Sidebar --}}
    <aside
        class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 transform transition-transform duration-200 lg:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

        {{-- Brand --}}
        <div class="flex items-center gap-3 px-5 h-16 border-b border-gray-800">
            <img src="{{ asset('assets/images/bionic-logo.png') }}" alt=""
                class="h-8 w-auto brightness-0 invert">
            <span class="text-white font-bold text-base tracking-tight">Admin</span>
        </div>

        {{-- Navigation --}}
        <nav class="mt-4 px-3 space-y-1 overflow-y-auto" style="max-height: calc(100vh - 8rem);">
            @php
                $nav = [
                    [
                        'label' => 'Dashboard',
                        'route' => 'admin.dashboard',
                        'icon' => 'fa-chart-pie',
                        'permission' => null,
                    ],
                    ['label' => 'Orders', 'route' => 'admin.orders', 'icon' => 'fa-box', 'permission' => 'order.view'],
                    ['label' => 'Customers', 'route' => 'admin.customers', 'icon' => 'fa-users', 'permission' => 'customer.view'],
                    [
                        'label' => 'Products',
                        'route' => 'admin.products',
                        'icon' => 'fa-leaf',
                        'permission' => 'product.view',
                    ],
                    [
                        'label' => 'Categories',
                        'route' => 'admin.categories',
                        'icon' => 'fa-layer-group',
                        'permission' => 'category.view',
                    ],
                    [
                        'label' => 'Combos',
                        'route' => 'admin.combos',
                        'icon' => 'fa-cubes',
                        'permission' => 'product.view',
                    ],
                    [
                        'label' => 'Coupons',
                        'route' => 'admin.coupons',
                        'icon' => 'fa-ticket',
                        'permission' => 'coupon.view',
                    ],
                    [
                        'label' => 'Shipping',
                        'route' => 'admin.shipping',
                        'icon' => 'fa-truck',
                        'permission' => 'shipping.view',
                    ],
                    [
                        'label' => 'Webhooks',
                        'route' => 'admin.webhooks',
                        'icon' => 'fa-link',
                        'permission' => 'system.webhooks',
                    ],
                    [
                        'label' => 'Activity Log',
                        'route' => 'admin.activity-log',
                        'icon' => 'fa-clock-rotate-left',
                        'permission' => 'system.activity_log',
                    ],
                ];
            @endphp

            @foreach ($nav as $item)
                @if ($item['permission'] === null || auth()->user()->can($item['permission']))
                    <a href="{{ route($item['route']) }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                              {{ request()->routeIs($item['route'] . '*') ? 'bg-green-700 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                        <i class="fa-solid {{ $item['icon'] }} w-5 text-center text-xs"></i>
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
        </nav>

        {{-- Bottom: user info --}}
        <div class="absolute bottom-0 left-0 right-0 border-t border-gray-800 px-4 py-3">
            <div class="flex items-center gap-3">
                <div
                    class="w-8 h-8 rounded-full bg-green-700 flex items-center justify-center text-white text-xs font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-200 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()->roles->pluck('name')->first() }}</p>
                </div>
            </div>
        </div>
    </aside>

    {{-- Main content area --}}
    <div class="lg:ml-64 min-h-screen flex flex-col">

        {{-- Topbar --}}
        <header
            class="sticky top-0 z-30 bg-white border-b border-gray-200 h-16 flex items-center px-4 lg:px-6 shrink-0">
            {{-- Mobile hamburger --}}
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden mr-3 text-gray-500 hover:text-gray-700">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>

            {{-- Page title --}}
            <h1 class="text-lg font-bold text-gray-800">@yield('title', 'Dashboard')</h1>

            {{-- Right side --}}
            <div class="ml-auto flex items-center gap-4">
                {{-- Visit store --}}
                <a href="{{ route('home') }}" target="_blank"
                    class="hidden sm:flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-700 transition">
                    <i class="fa-solid fa-external-link text-xs"></i>
                    Visit Store
                </a>

                {{-- Logout --}}
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit"
                        class="cursor-pointer flex items-center gap-1.5 text-sm text-gray-500 hover:text-red-600 transition">
                        <i class="fa-solid fa-right-from-bracket text-xs"></i>
                        Logout
                    </button>
                </form>
            </div>
        </header>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mx-4 lg:mx-6 mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mx-4 lg:mx-6 mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">
                {{ session('error') }}
            </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 p-4 lg:p-6">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="px-4 lg:px-6 py-3 border-t border-gray-100 text-xs text-gray-400">
            &copy; {{ date('Y') }} {{ config('app.name') }} &mdash; Admin Panel
        </footer>
    </div>

    {{-- Alpine.js via CDN (lightweight, no build step needed for admin) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')
</body>

</html>

<header class="bg-[#246E231A] py-6 px-4 md:px-10">
    <div class="max-w-8xl mx-auto flex flex-wrap items-center justify-between gap-4">

        <div class="shrink-0">
            <a href="/" class=" transition-transform hover:scale-105 active:scale-95">
                <img src="{{ asset('assets/images/bionic-logo.png') }}" alt="Logo"
                    class="w-20 md:w-24 object-contain h-auto">
            </a>
        </div>

        <div class="hidden md:flex flex-1 max-w-3xl mx-8 bg-white rounded-full p-2 items-center shadow-lg">
            <button
                class="flex items-center gap-2 px-6 py-2 border-r border-gray-100 text-slate-700 text-sm font-medium whitespace-nowrap hover:text-primary transition-colors">
                All Categories
                <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <input type="text" placeholder="Search products/ Categories..."
                class="flex-1 px-4 py-2 bg-transparent text-slate-800 text-sm outline-none placeholder:text-slate-400">
            <div id="searchSuggestions"
                class="absolute left-0 top-full w-full bg-white shadow-lg rounded-lg hidden z-50"></div>
            <button
                class="group flex items-center bg-primary text-white p-2.5 rounded-full mr-1 hover:opacity-90 transition-all duration-1000 ease-in-out transform active:scale-95 cursor-pointer">
                <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>

                <span
                    class="max-w-0 overflow-hidden whitespace-nowrap text-sm font-medium transition-all duration-300 group-hover:max-w-xs group-hover:ml-2 group-hover:pr-2">
                    Search
                </span>
            </button>
        </div>

        <div class="flex items-center gap-4 bg-white rounded-full px-5 py-2 shadow-sm border border-slate-100">
            @guest
                <a href="{{ route('register') }}"
                    class="flex items-center gap-2 text-slate-700 hover:text-black transition-colors">
                    <div class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold tracking-tight">Sign In</span>
                </a>
            @endguest

            @auth
                <div class="relative group flex items-center gap-2 cursor-pointer text-slate-700">

                    <div class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>

                    <span class="text-sm font-semibold tracking-tight">
                        {{ auth()->user()->name }}
                    </span>

                    <div
                        class="absolute right-0 top-full mt-2 w-44 bg-white rounded-xl shadow-lg opacity-0 invisible group-hover:visible group-hover:opacity-100 transition-all">

                        <a href="/account/dashboard" class="block px-4 py-2 hover:bg-slate-50">
                            Dashboard
                        </a>

                        <a href="/account/orders" class="block px-4 py-2 hover:bg-slate-50">
                            Track Orders
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full text-left px-4 py-2 hover:bg-slate-50">
                                Logout
                            </button>
                        </form>

                    </div>

                </div>
            @endauth

            <span class="text-slate-300 h-4 w-1px bg-slate-200"></span>

            <a href="#" onclick="toggleCart()" class="flex items-center gap-2 group">
                <div class="relative p-1">
                    <svg class="w-6 h-6 text-slate-600 group-hover:text-black transition-colors" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span
                        class="absolute top-0 right-0 transform translate-x-1/2 -translate-y-1/2 bg-primary text-white text-[10px] font-bold min-w-18px h-18px px-1 rounded-full flex items-center justify-center border-2 border-white">
                        12
                    </span>
                </div>
                <span
                    class="text-sm font-semibold tracking-tight hidden sm:block text-slate-700 group-hover:text-black">Cart</span>
            </a>

            <button
                class="md:hidden flex items-center justify-center text-slate-600 hover:text-black transition-colors ml-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </button>
        </div>

        <div class="md:hidden w-full mt-2">
            <div class="flex bg-white rounded-full p-1 items-center">
                <input type="text" placeholder="Search products..."
                    class="flex-1 px-5 py-2.5 text-sm outline-none rounded-full">
                <button class="bg-primary text-white p-2.5 rounded-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</header>

<header class="sticky top-0 bg-[#246E231A] py-4 md:py-6 px-4 md:px-8 z-30">

    <div class="max-w-8xl mx-auto flex flex-wrap items-center justify-between gap-3 md:gap-6">

        <!-- LOGO -->
        <a href="/" class="shrink-0">
            <img src="{{ asset('assets/images/bionic-logo.png') }}" class="w-24 object-contain">
        </a>


        <!-- SEARCH (DESKTOP) -->
        <div
            class="hidden md:flex flex-1 max-w-3xl mx-8 bg-white rounded-full p-2 items-center shadow-lg relative overflow-visible">
            <!-- Categories Dropdown -->
            <div class="relative group">
                <button id="categoriesButton"
                    class="flex items-center gap-2 px-6 py-2 border-r border-gray-100 text-slate-700 text-sm font-medium whitespace-nowrap hover:text-primary transition-colors">
                    All Categories <svg
                        class="w-4 h-4 opacity-60 transition-transform group-hover/categories:rotate-180" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <!-- Categories Dropdown Menu -->
                <div id="categoriesDropdown"
                    class="absolute left-0 top-full w-64 bg-white rounded-xl shadow-xl opacity-0 invisible group-hover:visible group-hover:opacity-100 transition z-50">

                    @foreach ($globalCategories as $category)
                        <a href="{{ $category->category_page }}"
                            class="block px-4 py-2.5 text-sm hover:bg-slate-50 hover:text-primary">
                            {{ $category->name }}
                        </a>
                    @endforeach

                </div>
            </div>
            <!-- Search Input -->
            <div class="flex-1 relative"> <input type="text" id="searchInput"
                    placeholder="Search products/ Categories..."
                    class="w-full px-4 py-2 bg-transparent text-slate-800 text-sm outline-none placeholder:text-slate-400"
                    autocomplete="off">
                <!-- Search Suggestions -->
                <div id="searchSuggestions"
                    class="absolute left-0 top-full w-full bg-white shadow-lg rounded-lg hidden z-50 max-h-96 overflow-y-auto">
                    <!-- Suggestions will be populated here -->
                </div>
            </div> <!-- Search Button -->
            <button id="searchButton"
                class="group flex items-center bg-primary text-white p-2.5 rounded-full mr-1 hover:opacity-90 transition-all duration-1000 ease-in-out transform active:scale-95 cursor-pointer">
                <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg> <span
                    class="max-w-0 overflow-hidden whitespace-nowrap text-sm font-medium transition-all duration-300 group-hover:max-w-xs group-hover:ml-2 group-hover:pr-2">
                    Search </span> </button>
        </div>

        <!-- ACTIONS -->
        <div
            class="flex items-center gap-2 md:gap-4 bg-white rounded-full px-2 md:px-5 py-2 shadow-sm border border-slate-100">

            <!-- ACCOUNT -->
            @auth
                <div class="relative group py-2">
                    <div class="flex items-center gap-2 cursor-pointer">
                        <span
                            class="w-9 h-9 bg-green-800 text-white rounded-full flex items-center justify-center text-xs font-bold">
                            {{-- Helper to get initials --}}
                            {{ collect(explode(' ', auth()->user()->name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('') }}
                        </span>
                        <span class="text-sm font-semibold hidden md:block">{{ auth()->user()->name }}</span>
                    </div>

                    <div
                        class="absolute right-0 mt-2 w-72 bg-[#f0f4f0] rounded-2xl shadow-xl border border-white p-3 invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-300 z-50">

                        <div class="flex items-center gap-3 p-2 bg-white/50 rounded-xl mb-3">
                            <span
                                class="w-12 h-12 bg-green-800 text-white rounded-full flex items-center justify-center text-lg font-bold">
                                {{ collect(explode(' ', auth()->user()->name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('') }}
                            </span>
                            <div class="overflow-hidden">
                                <h4 class="font-bold text-slate-800 truncate">{{ auth()->user()->name }}</h4>
                                <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <a href="{{ route('account.dashboard') }}"
                                class="flex items-center gap-3 p-3 bg-white hover:bg-green-50 rounded-xl transition-colors group/item">
                                <i class="fa-regular fa-circle-user text-slate-400 group-hover/item:text-green-700"></i>
                                <span class="text-sm font-medium text-slate-700">My Account</span>
                            </a>

                            <a href="#"
                                class="flex items-center gap-3 p-3 bg-white hover:bg-green-50 rounded-xl transition-colors group/item">
                                <i class="fa-regular fa-clock text-slate-400 group-hover/item:text-green-700"></i>
                                <span class="text-sm font-medium text-slate-700">Track Order</span>
                            </a>

                            <a href="#"
                                class="flex items-center gap-3 p-3 bg-white hover:bg-green-50 rounded-xl transition-colors group/item">
                                <i class="fa-solid fa-cart-shopping text-slate-400 group-hover/item:text-green-700"></i>
                                <span class="text-sm font-medium text-slate-700">My Order</span>
                            </a>

                            <button id="logoutBtn"
                                class="w-full flex items-center gap-3 p-3 bg-white hover:bg-red-50 rounded-xl transition-colors group/item">
                                <i class="fa-solid fa-right-from-bracket text-slate-400 group-hover/item:text-red-600"></i>
                                <span class="text-sm font-medium text-slate-700">Logout</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endauth
            @guest
                <a href="{{ route('login') }}" class="flex items-center gap-2 text-sm font-semibold">
                    <span class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </span>
                    Sign In
                </a>
            @endguest
            <span class="text-slate-300 h-6 w-px bg-slate-200 hidden sm:block"></span>
            <!-- Cart -->
            <button onclick="toggleCart()" class="flex items-center gap-2 group relative cursor-pointer">
                <div class="relative p-1">
                    <svg class="w-6 h-6 text-slate-600 group-hover:text-black transition-colors" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span id="cartCount"
                        class="absolute top-0 right-0 transform translate-x-1/2 -translate-y-1/2 bg-primary text-white text-[10px] font-bold min-w-4.5 h-4.5 px-1 rounded-full flex items-center justify-center border-2 border-white">
                        0 </span>
                </div>
                <span
                    class="text-sm font-semibold tracking-tight hidden sm:block text-slate-700 group-hover:text-black">Cart</span>
            </button>

            <!-- MOBILE MENU -->
            <button id="mobileMenuToggle" class="md:hidden text-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </button>

        </div>


        <!-- MOBILE SEARCH -->
        <div class="w-full md:hidden">
            <div class="flex bg-white rounded-full shadow p-1">
                <input class="flex-1 px-4 py-2 text-sm outline-none placeholder:text-slate-400"
                    placeholder="Search products/ Categories..." autocomplete="off">
                <button class="bg-primary text-white px-4 rounded-full text-sm">
                    Go
                </button>
            </div>
        </div>

    </div>


    <!-- MOBILE CATEGORY PANEL -->
    <div id="mobileDropdown"
        class="hidden md:hidden absolute left-4 right-4 top-auto mt-3 bg-white rounded-2xl shadow-xl z-40 max-h-[60vh] no-scrollbar overflow-y-auto">

        @foreach ($globalCategories as $category)
            <a href="/category/{{ $category->slug }}"
                class="block px-5 py-3 border-b border-slate-100 text-sm hover:bg-slate-50">
                {{ $category->name }}
            </a>
        @endforeach

    </div>

</header>

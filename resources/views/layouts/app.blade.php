<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset('favicon.png') }}">

    <title>
        {{ config('app.name', 'Bionic Garden') }}
        @hasSection('title')
            — @yield('title')
        @endif
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Plus+Jakarta+Sans:wght@200..800&family=Noto+Sans+Bengali:wght@100..900&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

</head>

<body class="antialiased font-inter">

    <div class="min-h-screen flex flex-col">

        @include('store.partials.header')

        <x-flash-container />

        <main class="flex-1">
            @yield('content')
        </main>

        @include('store.partials.footer')

    </div>


    {{-- CART DRAWER --}}
    <div id="cartDrawer"
        class="fixed top-0 right-0 w-96 max-w-full h-full bg-white shadow-xl transform translate-x-full transition-transform duration-300 z-50">

        <div class="p-6 border-b flex justify-between items-center">

            <h3 class="font-bold text-lg">Your Cart</h3>

            <button onclick="toggleCart()">
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>

        <div id="cartItems" class="p-6 overflow-y-auto">

        </div>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12/dist/ScrollTrigger.min.js"></script>

    <script>
        function toggleCart() {
            const drawer = document.getElementById('cartDrawer');

            if (!drawer) return;

            drawer.classList.toggle('translate-x-full');
        }
    </script>
    <script>
        const searchInput = document.querySelector('input[placeholder="Search products/ Categories..."]');
        const suggestionBox = document.getElementById('searchSuggestions');

        if (searchInput) {

            searchInput.addEventListener('input', async function() {

                const q = this.value;

                if (q.length < 2) {
                    suggestionBox.classList.add('hidden');
                    return;
                }

                try {

                    const res = await fetch(`/api/products/search?q=${q}`);
                    const data = await res.json();

                    if (!data.data) return;

                    suggestionBox.innerHTML = data.data.map(p => `
                                        <a href="/product/${p.slug}" class="block px-4 py-2 hover:bg-slate-100">
                                        ${p.name}
                                        </a>
                                        `).join('');

                    suggestionBox.classList.remove('hidden');

                } catch (e) {
                    console.error(e);
                }

            });

        }
    </script>
    <script>
        document.querySelectorAll('.md\\:hidden button').forEach(btn => {
            btn.addEventListener('click', () => {
                alert('Mobile menu not implemented yet');
            });
        });
    </script>
    @stack('scripts')

</body>

</html>

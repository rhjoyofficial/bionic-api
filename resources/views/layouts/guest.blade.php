<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth no-scrollbar">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Dynamic SEO & Open Graph Meta Tags --}}
    <meta name="description" content="@yield('meta_description', 'Bionic Garden - Premium Quality Dates, Nuts, and Organic Foods.')">
    <meta name="keywords" content="@yield('meta_keywords', 'Bionic Garden, organic food, dates, nuts, healthy snacks')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title"
        content="{{ config('app.name', 'Bionic Garden') }} @hasSection('title')
— @yield('title')
@endif">
    <meta property="og:description" content="@yield('meta_description', 'Bionic Garden - Premium Quality Dates, Nuts, and Organic Foods.')">
    <meta property="og:image" content="@yield('meta_image', asset('favicon.png'))">

    <link rel="icon" href="{{ asset('favicon.png') }}">

    <title>
        {{ config('app.name', 'Bionic Garden') }}
        @hasSection('title')
            — @yield('title')
        @endif
    </title>

    {{-- Google Tag Manager - Head Script (Load as early as possible) --}}
    @if (config('services.gtm.id'))
        <script>
            (function(w, d, s, l, i) {
                w[l] = w[l] || [];
                w[l].push({
                    'gtm.start': new Date().getTime(),
                    event: 'gtm.js'
                });
                var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s),
                    dl = l != 'dataLayer' ? '&l=' + l : '';
                j.async = true;
                j.src =
                    'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', '{{ config('services.gtm.id') }}');
        </script>
    @endif
    {{-- End Google Tag Manager --}}

    {{-- Common Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Plus+Jakarta+Sans:wght@200..800&family=Noto+Sans+Bengali:wght@100..900&display=swap"
        rel="stylesheet">

    {{-- Load Swiper CSS only if NOT on auth pages --}}
    @unless (Route::is('login', 'register', 'password.*'))
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @endunless

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    {{-- Meta Pixel Code --}}
    @if (config('services.meta.pixel_id'))
        <script>
            ! function(f, b, e, v, n, t, s) {
                if (f.fbq) return;
                n = f.fbq = function() {
                    n.callMethod ?
                        n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                };
                if (!f._fbq) f._fbq = n;
                n.push = n;
                n.loaded = !0;
                n.version = '2.0';
                n.queue = [];
                t = b.createElement(e);
                t.async = !0;
                t.src = v;
                s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s)
            }(window, document, 'script',
                'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ config('services.meta.pixel_id') }}');
            fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none"
                src="https://www.facebook.com/tr?id={{ config('services.meta.pixel_id') }}&ev=PageView&noscript=1" /></noscript>
    @endif
    {{-- End Meta Pixel Code --}}

</head>

<body class="antialiased font-sans no-scrollbar">

    {{-- Google Tag Manager (noscript) - Must be immediately after opening <body> --}}
    @if (config('services.gtm.id'))
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ config('services.gtm.id') }}"
                height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif
    {{-- End Google Tag Manager (noscript) --}}

    <div class="min-h-screen flex flex-col relative">
        {{-- Show Header/Cart only for non-auth pages --}}
        @unless (Route::is('login', 'register', 'password.*'))
            @include('store.partials.header')
            @include('store.partials.cart-drawer')
        @endunless

        <x-flash-container />

        <main class="flex-1">
            @yield('content')
        </main>

        {{-- Show Footer only for non-auth pages --}}
        @unless (Route::is('login', 'register', 'password.*'))
            @include('store.partials.footer')
        @endunless
    </div>

    {{-- Heavy Scripts: Load only when needed --}}
    @unless (Route::is('login', 'register', 'password.*'))
        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/gsap@3.12/dist/gsap.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/gsap@3.12/dist/ScrollTrigger.min.js"></script>
        <script>
            gsap.registerPlugin(ScrollTrigger);
        </script>
    @endunless

    @stack('scripts')
</body>

</html>

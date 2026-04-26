@extends('layouts.app')

@php
    $finalPrice = $combo->final_price;
    $autoPrice = $combo->auto_price;
    $savings = $combo->total_savings;
    $inStock = $combo->isInStock();
    $isLowStock = $combo->isLowStock();
    $maxQty = max(1, $combo->available_stock);

    // Build a lightweight data object for the inline JS
    $comboData = [
        'id' => $combo->id,
        'title' => $combo->title,
        'final_price' => (float) $finalPrice,
        'image_url' => $combo->image_url ?? asset('images/placeholder.png'),
        'stock' => $combo->available_stock,
    ];
@endphp

@section('title', $combo->title)
@section('meta_description', Str::limit(strip_tags($combo->description ?? 'Exclusive combo pack'), 155))

@push('styles')
    <style>
        /* ── Animated gradient banner (same palette as product page) ─ */
        .combo-fire-bg {
            background: linear-gradient(120deg, #1d6327, #2d6a4f, #40916c, #1d6327);
            background-size: 300% 300%;
            animation: waveMove 4s ease-in-out infinite;
        }

        .combo-fire-bg::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 20% 50%, rgba(64, 145, 108, 0.4), transparent 50%),
                radial-gradient(circle at 80% 50%, rgba(45, 106, 79, 0.3), transparent 50%);
            mix-blend-mode: overlay;
            animation: pulseGlow 2s infinite alternate;
        }

        @keyframes waveMove {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        @keyframes pulseGlow {
            0% {
                opacity: 0.6;
                transform: scale(1);
            }

            100% {
                opacity: 1;
                transform: scale(1.05);
            }
        }

        /* ── CTA button shimmer ─────────────────────────────────────── */
        @keyframes softPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(29, 99, 39, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(29, 99, 39, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(29, 99, 39, 0);
            }
        }

        .animate-soft-pulse {
            animation: softPulse 2s infinite;
        }

        .btn-shimmer::after {
            content: "";
            position: absolute;
            top: -50%;
            left: -60%;
            width: 20%;
            height: 200%;
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(30deg);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% {
                left: -60%;
            }

            20% {
                left: 120%;
            }

            100% {
                left: 120%;
            }
        }

        /* ── Combo item card hover ──────────────────────────────────── */
        .combo-item-card {
            transition: box-shadow .2s, border-color .2s;
        }

        .combo-item-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, .07);
        }

        /* ── Tab fade-in ────────────────────────────────────────────── */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn .25s ease;
        }

        /* ── Star pulse (same as product page) ─────────────────────── */
        .star-glow {
            filter: drop-shadow(0 0 2px rgba(251, 191, 36, .4));
            animation: starPulse 2s infinite ease-in-out;
        }

        .star-glow:nth-child(2) {
            animation-delay: .2s;
        }

        .star-glow:nth-child(3) {
            animation-delay: .4s;
        }

        .star-glow:nth-child(4) {
            animation-delay: .6s;
        }

        .star-glow:nth-child(5) {
            animation-delay: .8s;
        }

        @keyframes starPulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.1);
                opacity: .8;
                filter: drop-shadow(0 0 5px rgba(251, 191, 36, .8));
            }
        }
    </style>
@endpush

@section('content')
    <section class="bg-[#f0f5f1] min-h-screen">
        <div class="max-w-8xl mx-auto px-4 py-6 md:py-10">

            {{-- Breadcrumb -------------------------------------------------- --}}
            <x-page-header :breadcrumbs="[
                ['label' => 'Home', 'url' => route('shop')],
                ['label' => 'Combos', 'url' => route('combos.index')],
                ['label' => $combo->title, 'url' => null],
            ]" />

            {{-- ── Main Grid ────────────────────────────────────────────────── --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-[2fr_3fr] gap-6 md:gap-10 lg:gap-x-16">

                {{-- Left: Combo Image ----------------------------------------- --}}
                <div class="space-y-4">
                    <div
                        class="aspect-square rounded-2xl overflow-hidden border border-gray-200 bg-white shadow-sm relative">
                        @if ($savings > 0)
                            <div
                                class="absolute top-3 left-3 z-10 bg-red-500 font-bengali text-white text-xs font-bold px-3 py-1.5 rounded-full shadow">
                                Save ৳{{ number_format($savings, 0) }}
                            </div>
                        @endif
                        <img id="comboMainImage" src="{{ $combo->image_url ?? asset('images/placeholder.png') }}"
                            alt="{{ $combo->title }}" class="w-full h-full object-contain p-4">
                    </div>

                    {{-- Combo contents label (mobile) ----------------------- --}}
                    <div class="md:hidden bg-white rounded-2xl border border-gray-100 p-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">What's inside</p>
                        @foreach ($combo->items as $item)
                            @php
                                $v = $item->variant;
                                $p = $v?->product;
                            @endphp
                            <div class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0">
                                <div class="w-8 h-8 rounded-lg bg-gray-50 border border-gray-100 overflow-hidden shrink-0">
                                    @if ($p?->image_url)
                                        <img src="{{ $p->image_url }}" alt="{{ $p->name }}"
                                            class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300 text-lg">📦
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-700 truncate">{{ $p?->name ?? 'Item' }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $v?->title }} &times; {{ $item->quantity }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Right: Combo Details -------------------------------------- --}}
                <div id="comboDetailBox" data-combo='@json($comboData)' class="space-y-5">

                    {{-- Badge + Stars --}}
                    <div class="flex items-center gap-4">
                        <div class="relative inline-block px-3 py-1 rounded overflow-hidden combo-fire-bg shrink-0">
                            <p class="relative z-10 text-sm font-medium text-white uppercase tracking-wide">Combo Pack</p>
                        </div>
                        <div class="flex items-center gap-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="w-5 h-5 star-glow text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                    </div>

                    {{-- Title --}}
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ $combo->title }}</h1>

                    {{-- Short description / teaser (first 120 chars of description) --}}
                    @if ($combo->description)
                        <p class="text-gray-600 leading-snug line-clamp-2">
                            {{ Str::limit(strip_tags($combo->description), 120) }}
                        </p>
                    @endif

                    {{-- ── Pricing ──────────────────────────────────────────── --}}
                    <div class="flex items-center gap-3 flex-wrap">
                        <span id="comboFinalPrice" class="text-2xl font-medium text-gray-900 font-bengali">
                            ৳{{ number_format($finalPrice, 2) }}
                        </span>

                        @if ($savings > 0)
                            <span class="text-base text-gray-400 line-through font-bengali">
                                ৳{{ number_format($autoPrice, 2) }}
                            </span>
                            <span class="px-2 py-0.5 font-bengali text-xs bg-red-100 text-red-700 rounded font-semibold">
                                Save ৳{{ number_format($savings, 2) }}
                            </span>
                        @endif
                    </div>

                    {{-- Key values (matching product page) --}}
                    <div
                        class="flex items-center justify-start gap-3 text-primary font-semibold text-sm md:text-base tracking-wide">
                        <span>Curated Bundle</span>
                        <span class="text-primary/40 text-xl">•</span>
                        <span>Best Value</span>
                        <span class="text-primary/40 text-xl">•</span>
                        <span>Lab Tested</span>
                    </div>

                    {{-- ── What's Inside (desktop) ──────────────────────────── --}}
                    <div class="hidden md:block">
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-widest mb-3">What's inside</p>
                        <div class="flex flex-wrap items-center justify-start gap-1">
                            @forelse ($combo->items as $item)
                                @php
                                    $v = $item->variant;
                                    $p = $v?->product;
                                @endphp
                                <div
                                    class="combo-item-card flex items-center gap-3 bg-white rounded-xl border border-gray-100 p-3">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-gray-50 border border-gray-100 overflow-hidden shrink-0">
                                        @if ($p?->image_url)
                                            <img src="{{ $p->image_url }}" alt="{{ $p?->name }}"
                                                class="w-full h-full object-cover" loading="lazy">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-2xl">📦</div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-800">{{ $p?->name ?? 'Product' }}</p>
                                        <p class="text-xs text-gray-500">{{ $v?->title }}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-xs font-bold text-gray-700 font-bengali">× {{ $item->quantity }}</p>
                                        @if ($v)
                                            <p class="text-xs text-primary font-semibold font-bengali">
                                                ৳{{ number_format($v->final_price * $item->quantity, 0) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-400 italic">No items configured for this combo yet.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- ── Stock & Quantity ─────────────────────────────────── --}}
                    <div class="flex flex-wrap items-center justify-start gap-6 py-2">
                        <div>
                            <span class="text-sm text-gray-500">Stock:</span>
                            <span id="comboStockText"
                                class="ml-1 font-medium
                            {{ $inStock ? ($isLowStock ? 'text-amber-600' : 'text-gray-900') : 'text-red-600' }}">
                                @if ($inStock)
                                    {{ $combo->available_stock }} sets
                                    @if ($isLowStock)
                                        &mdash; <span class="text-amber-600 font-semibold text-xs">Low stock!</span>
                                    @endif
                                @else
                                    Out of Stock
                                @endif
                            </span>
                        </div>

                        @if ($inStock)
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-500">Qty:</span>
                                <div class="flex items-center border border-gray-200 overflow-hidden bg-white">
                                    <button id="comboQtyMinus" type="button"
                                        class="cursor-pointer w-9 h-9 flex items-center justify-center text-gray-400 hover:bg-gray-50 hover:text-gray-700 transition-colors font-bold text-lg border-r border-gray-200">−</button>
                                    <div id="comboQtyDisplay"
                                        class="w-10 h-9 flex items-center justify-center text-sm font-bold text-gray-800 select-none">
                                        1
                                    </div>
                                    <button id="comboQtyPlus" type="button"
                                        class="cursor-pointer w-9 h-9 flex items-center justify-center text-gray-400 hover:bg-gray-50 hover:text-gray-700 transition-colors font-bold text-lg border-l border-gray-200">+</button>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- ── Action Buttons ───────────────────────────────────── --}}
                    <div class="flex flex-col md:flex-row md:items-center md:justify-center gap-4 mt-6">
                        @if ($inStock)
                            {{-- Add to Cart --}}
                            <button id="comboAddToCartBtn" type="button"
                                class="relative overflow-hidden w-full rounded-lg bg-primary text-white font-bold py-4
                                   transition-all duration-300 transform hover:-translate-y-1 hover:shadow-lg
                                   active:scale-95 cursor-pointer btn-shimmer animate-soft-pulse">
                                <span class="relative z-10 flex items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    ADD TO CART
                                </span>
                            </button>

                            {{-- Buy Now --}}
                            <button id="comboBuyNowBtn" type="button"
                                class="w-full rounded-lg bg-gray-900 text-white font-bold py-4
                                   transition-all duration-300 transform hover:-translate-y-1 hover:bg-black hover:shadow-lg
                                   active:scale-95 cursor-pointer">
                                BUY NOW
                            </button>
                        @else
                            {{-- Out of Stock state --}}
                            <div
                                class="w-full rounded-lg bg-gray-100 border border-gray-200 text-gray-400 font-bold py-4 text-center">
                                Out of Stock
                            </div>
                        @endif
                    </div>

                </div>{{-- /comboDetailBox --}}
            </div>{{-- /main grid --}}

            {{-- ── Description Tab ─────────────────────────────────────────── --}}
            @if ($combo->description)
                <div class="mt-12 bg-white rounded-2xl border border-gray-200 p-6 md:p-8 shadow-sm">
                    <div class="border-b border-gray-200 mb-6">
                        <nav class="flex space-x-8" aria-label="Tabs">
                            <button type="button" data-tab-target="comboDescription"
                                class="tab-btn border-b-2 border-primary text-primary px-1 pb-4 text-sm font-semibold whitespace-nowrap transition-all duration-300 cursor-pointer">
                                Description
                            </button>
                        </nav>
                    </div>
                    <div id="comboDescription" class="tab-content block animate-fadeIn">
                        <p class="text-sm md:text-base text-gray-600 leading-relaxed max-w-7xl font-bengali">
                            {!! $combo->description !!}
                        </p>
                    </div>
                </div>
            @endif

            {{-- ── Related Combos ───────────────────────────────────────────── --}}
            @if ($relatedCombos->isNotEmpty())
                <div class="mt-12">
                    <div class="flex items-center justify-between gap-6 mb-8 pb-4">
                        <h2 class="font-heading text-2xl md:text-3xl font-bold text-brand text-left gap-3 md:shrink-0">
                            More Combo Packs
                        </h2>
                        <span class="h-0.5 w-full bg-gray-200 hidden md:block"></span>
                        <div class="flex gap-2 md:shrink-0">
                            <button id="relatedComboPrev"
                                class="p-2 rounded-md border border-gray-200 hover:bg-gray-50 transition-colors cursor-pointer focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button id="relatedComboNext"
                                class="p-2 rounded-md border border-gray-200 hover:bg-gray-50 transition-colors cursor-pointer focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div id="relatedComboCarousel"
                        class="grid grid-flow-col auto-cols-[85%] sm:auto-cols-[45%] lg:auto-cols-[31%] gap-5 overflow-x-auto snap-x snap-mandatory pb-4 no-scrollbar scroll-smooth">
                        @foreach ($relatedCombos as $related)
                            <div class="snap-start">
                                <x-combo-card :combo="$related" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </section>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // ── State ──────────────────────────────────────────────────────────────
                const box = document.getElementById('comboDetailBox');
                const combo = JSON.parse(box?.dataset.combo || '{}');
                const maxQty = Math.max(1, Number(combo.stock || 1));

                // ── DOM refs ───────────────────────────────────────────────────────────
                const qtyDisplay = document.getElementById('comboQtyDisplay');
                const qtyMinus = document.getElementById('comboQtyMinus');
                const qtyPlus = document.getElementById('comboQtyPlus');
                const addToCartBtn = document.getElementById('comboAddToCartBtn');
                const buyNowBtn = document.getElementById('comboBuyNowBtn');

                // ── Helpers ────────────────────────────────────────────────────────────
                function getQty() {
                    return Math.max(1, Math.min(maxQty, parseInt(qtyDisplay?.textContent?.trim() || '1', 10) || 1));
                }

                function setQty(val) {
                    if (!qtyDisplay) return;
                    qtyDisplay.textContent = Math.max(1, Math.min(maxQty, val));
                }

                // ── Qty controls ───────────────────────────────────────────────────────
                qtyMinus?.addEventListener('click', () => setQty(getQty() - 1));
                qtyPlus?.addEventListener('click', () => setQty(getQty() + 1));

                // ── Add to Cart ────────────────────────────────────────────────────────
                addToCartBtn?.addEventListener('click', async () => {
                    const qty = getQty();
                    await window.Cart?.addCombo(combo.id, qty, addToCartBtn);
                });

                // ── Buy Now ────────────────────────────────────────────────────────────
                // CheckoutManager.js fully supports combo_id in the bionic_buy_now payload.
                buyNowBtn?.addEventListener('click', () => {
                    const qty = getQty();
                    const imageEl = document.getElementById('comboMainImage');

                    sessionStorage.setItem('bionic_buy_now', JSON.stringify({
                        combo_id: combo.id,
                        quantity: qty,
                        combo_name_snapshot: combo.title,
                        unit_price: combo.final_price,
                        image_url: imageEl?.src ?? combo.image_url ?? '',
                    }));

                    window.location.href = '/checkout?buyNow=1';
                });

                // ── Related combos carousel ────────────────────────────────────────────
                const carousel = document.getElementById('relatedComboCarousel');
                document.getElementById('relatedComboPrev')?.addEventListener('click', () => {
                    carousel?.scrollBy({
                        left: -carousel.offsetWidth * 0.85,
                        behavior: 'smooth'
                    });
                });
                document.getElementById('relatedComboNext')?.addEventListener('click', () => {
                    carousel?.scrollBy({
                        left: carousel.offsetWidth * 0.85,
                        behavior: 'smooth'
                    });
                });
            });
        </script>
    @endpush
@endsection

<!-- CART OVERLAY -->
<div id="overlay"
    class="fixed inset-0 bg-black/40 backdrop-blur-[2px] opacity-0 invisible transition-all duration-300 z-40">
</div>

<!-- CART SIDEBAR -->
<aside id="cartDrawer"
    class="fixed top-0 right-0 h-full w-full sm:w-105 bg-white shadow-2xl translate-x-full transition duration-300 ease-[cubic-bezier(.16,1,.3,1)] z-50 flex flex-col font-bengali">

    <!-- HEADER -->
    <div
        class="p-5 border-b border-slate-100 flex items-center justify-between bg-white/80 backdrop-blur sticky top-0 z-10">

        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center">
                <img src="{{ asset('favicon.png') }}" alt="Bionic" class="w-6 h-6 object-contain">
            </div>
            <div>
                <h3 class="font-bold text-lg leading-none">Your Cart</h3>
                <p class="text-xs text-slate-400">Natural shopping</p>
            </div>
        </div>

        <button onclick="Cart.close()"
            class="w-9 h-9 rounded-full group hover:bg-slate-100 transition flex items-center justify-center text-slate-500">
            <svg class="w-6 h-6 group-hover:rotate-90 duration-300 transform" viewBox="0 0 32 32"
                xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor">
                <path
                    d="m17.414 16 6.293-6.293a1 1 0 0 0-1.414-1.414L16 14.586 9.707 8.293a1 1 0 0 0-1.414 1.414L14.586 16l-6.293 6.293a1 1 0 1 0 1.414 1.414L16 17.414l6.293 6.293a1 1 0 0 0 1.414-1.414z" />
            </svg>
        </button>

    </div>

    <!-- SCROLL BODY -->
    <div id="cartItems" class="flex-1 overflow-y-auto overscroll-contain no-scrollbar">

        <!-- Renderer injects here -->

    </div>

    <!-- FOOTER -->
    <div class="border-t border-slate-100 p-5 bg-white sticky bottom-0">

        <!-- SUBTOTAL -->
        <div class="flex justify-between items-center mb-4">

            <div>
                <p class="text-sm text-slate-400">Subtotal</p>
                <p id="cartSubtotal" class="font-bold text-xl text-primary">৳0</p>
            </div>

            <button onclick="window.Cart.clear()"
                class="group flex items-center gap-1.5 text-xs font-medium text-red-400 hover:text-red-600 transition-all duration-200 cursor-pointer bg-red-50 px-2 py-1 rounded">

                <svg class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                    </path>
                </svg>

                <span class=" tracking-wider">Clear Cart</span>
            </button>

        </div>

        <!-- CTA BUTTONS -->
        <div class="flex gap-3">

            <a href="{{ route('cart.view') }}"
                class="flex-1 border border-primary text-primary font-semibold text-sm py-3 rounded-full text-center hover:bg-primary/5 transition">
                View Cart
            </a>

            <button onclick="Cart.checkout()"
                class="cursor-pointer flex-1 bg-primary text-white font-semibold text-sm py-3 rounded-full hover:opacity-90 transition active:scale-[.97]">
                Buy Now
            </button>

        </div>
    </div>

</aside>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const overlay = document.getElementById('overlay');

            overlay?.addEventListener('click', () => window.Cart.close());

            window.toggleCart = () => {
                const isOpen = !document.getElementById('cartDrawer').classList.contains('translate-x-full');
                isOpen ? window.Cart.close() : window.Cart.open();
            };

            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') window.Cart.close();
            });
        });
    </script>
@endpush

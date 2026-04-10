{{--
    Landing Page Checkout Partial
    ─────────────────────────────
    Shared Alpine.js checkout form embedded in all landing page templates.

    Required variables:
    - $landing  (LandingPage model)
    - $zones    (Collection of ShippingZone)

    The parent template must wrap this in a container and pass an `initialItems`
    JavaScript variable before including this partial.
    Example:  <script> var initialItems = [{variant_id: 5, quantity: 1}]; </script>
--}}

<div id="landingCheckout"
     x-data="landingCheckout()"
     x-init="init()"
     class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">

    <h3 class="text-xl font-bold text-gray-800 mb-6">Complete Your Order</h3>

    {{-- Customer Information --}}
    <div class="space-y-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                    Full Name <span class="text-red-400">*</span>
                </label>
                <input x-model="form.customer_name" type="text" placeholder="Your full name"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-300 focus:border-green-400 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                    Phone Number <span class="text-red-400">*</span>
                </label>
                <input x-model="form.customer_phone" type="tel" placeholder="01XXXXXXXXX"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-300 focus:border-green-400 transition-all">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                    Email <span class="text-gray-300 font-normal normal-case">(optional)</span>
                </label>
                <input x-model="form.customer_email" type="email" placeholder="you@example.com"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-300 focus:border-green-400 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                    City
                </label>
                <input x-model="form.city" type="text" placeholder="Dhaka"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-300 focus:border-green-400 transition-all">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                Delivery Address <span class="text-red-400">*</span>
            </label>
            <textarea x-model="form.address_line" rows="2" placeholder="House no., road, area..."
                      class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-300 focus:border-green-400 transition-all resize-none"></textarea>
        </div>
    </div>

    {{-- Delivery Zone --}}
    <div class="mb-6">
        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
            Delivery Zone <span class="text-red-400">*</span>
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($zones as $zone)
                <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all"
                       :class="form.zone_id == {{ $zone->id }}
                           ? 'border-green-600 bg-green-50'
                           : 'border-gray-100 hover:border-green-300'">
                    <input type="radio" name="zone_id" value="{{ $zone->id }}"
                           x-model.number="form.zone_id"
                           @change="refreshPreview()"
                           class="accent-green-700 w-4 h-4 shrink-0">
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">{{ $zone->name }}</p>
                        <p class="text-xs text-gray-400">
                            Delivery: <span class="font-bengali">&#2547;{{ number_format($zone->delivery_cost, 0) }}</span>
                            @if($zone->free_shipping_threshold)
                                &middot; Free over <span class="font-bengali">&#2547;{{ number_format($zone->free_shipping_threshold, 0) }}</span>
                            @endif
                        </p>
                    </div>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Payment Method --}}
    <div class="mb-6">
        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
            Payment Method <span class="text-red-400">*</span>
        </label>
        <div class="flex flex-col sm:flex-row gap-3">
            <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all flex-1"
                   :class="form.payment_method === 'cod'
                       ? 'border-green-600 bg-green-50'
                       : 'border-gray-100 hover:border-green-300'">
                <input type="radio" name="payment_method" value="cod"
                       x-model="form.payment_method"
                       class="accent-green-700 w-4 h-4 shrink-0">
                <div>
                    <p class="font-semibold text-gray-800 text-sm">Cash on Delivery</p>
                    <p class="text-xs text-gray-400">Pay when your order arrives</p>
                </div>
            </label>
        </div>
    </div>

    {{-- Coupon Code --}}
    <div class="mb-6">
        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
            Promo Code
        </label>
        <div class="flex items-center bg-gray-50 rounded-xl border border-gray-200 overflow-hidden focus-within:border-green-400 focus-within:ring-2 focus-within:ring-green-100 transition-all">
            <input x-model="couponInput" type="text" placeholder="Enter promo code"
                   class="bg-transparent flex-1 text-sm px-3 py-2.5 focus:outline-none text-gray-700 placeholder-gray-400 uppercase tracking-wider"
                   autocomplete="off">
            <button @click="applyCoupon()" type="button"
                    class="bg-green-800 text-white px-4 py-2.5 text-sm font-semibold hover:bg-green-900 transition-colors shrink-0">
                Apply
            </button>
        </div>
        <p x-show="couponMessage" x-text="couponMessage"
           :class="couponError ? 'text-red-500' : 'text-green-600'"
           class="text-xs mt-2 font-medium"></p>
    </div>

    {{-- Order Summary --}}
    <div class="bg-gray-50 rounded-xl p-5 mb-6 space-y-2.5">
        <div class="flex justify-between text-sm text-gray-500">
            <span>Subtotal</span>
            <span class="font-semibold text-gray-800 font-bengali" x-text="'&#2547;' + pricing.subtotal.toFixed(0)"></span>
        </div>
        <div x-show="pricing.tier_discount > 0" class="flex justify-between text-sm">
            <span class="text-gray-500">Tier Discount</span>
            <span class="font-semibold text-green-600 font-bengali" x-text="'-&#2547;' + pricing.tier_discount.toFixed(0)"></span>
        </div>
        <div x-show="pricing.coupon_discount > 0" class="flex justify-between text-sm">
            <span class="text-gray-500">Coupon (<span x-text="pricing.coupon_code"></span>)</span>
            <span class="font-semibold text-green-600 font-bengali" x-text="'-&#2547;' + pricing.coupon_discount.toFixed(0)"></span>
        </div>
        <div class="flex justify-between text-sm text-gray-500">
            <span>
                Shipping
                <template x-if="pricing.free_delivery_applied">
                    <span class="text-green-600 text-xs font-medium ml-1">FREE</span>
                </template>
            </span>
            <span class="font-semibold text-gray-800 font-bengali" x-text="'&#2547;' + pricing.shipping_cost.toFixed(0)"></span>
        </div>
        <div class="flex justify-between items-center pt-2 border-t border-gray-200">
            <span class="font-bold text-gray-800">Total</span>
            <span class="text-2xl font-bold text-green-800 font-bengali" x-text="'&#2547;' + pricing.grand_total.toFixed(0)"></span>
        </div>
    </div>

    {{-- Error Message --}}
    <div x-show="errorMessage" x-text="errorMessage"
         class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl p-3 mb-4"></div>

    {{-- Place Order Button --}}
    <button @click="placeOrder()" type="button"
            :disabled="submitting || !canSubmit()"
            class="w-full bg-green-800 text-white py-4 rounded-full font-bold text-base hover:bg-green-900 transition-all shadow-md shadow-green-100 active:scale-[.98] disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer">
        <template x-if="submitting">
            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </template>
        <span x-text="submitting ? 'Processing...' : 'Place Order'"></span>
    </button>

    {{-- Trust signals --}}
    <div class="flex justify-center gap-5 mt-4">
        <div class="flex items-center text-xs text-gray-400 gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Secure
        </div>
        <div class="flex items-center text-xs text-gray-400 gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Quality Guaranteed
        </div>
    </div>
</div>

<script>
function landingCheckout() {
    return {
        form: {
            customer_name: '',
            customer_phone: '',
            customer_email: '',
            address_line: '',
            area: '',
            city: '',
            zone_id: null,
            payment_method: 'cod',
            items: [],
            coupon_code: null,
        },

        pricing: {
            subtotal: 0,
            tier_discount: 0,
            coupon_discount: 0,
            coupon_code: null,
            shipping_cost: 0,
            grand_total: 0,
            free_delivery_applied: false,
        },

        couponInput: '',
        couponMessage: '',
        couponError: false,
        errorMessage: '',
        submitting: false,
        slug: '{{ $landing->slug }}',

        init() {
            // initialItems is set by the parent template
            if (typeof initialItems !== 'undefined') {
                this.form.items = JSON.parse(JSON.stringify(initialItems));
            }
            // Auto-select first zone if only one exists
            const zones = @json($zones->pluck('id'));
            if (zones.length === 1) {
                this.form.zone_id = zones[0];
                this.refreshPreview();
            }
        },

        /**
         * Called by parent template when items change (qty +/-, selection toggle).
         */
        updateItems(items) {
            this.form.items = items.filter(i => i.quantity > 0);
            this.refreshPreview();
        },

        async refreshPreview() {
            if (!this.form.zone_id || this.form.items.length === 0) return;

            try {
                const res = await fetch(`/api/landing/${this.slug}/preview`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        items: this.form.items,
                        zone_id: this.form.zone_id,
                        coupon_code: this.form.coupon_code,
                    }),
                });
                const json = await res.json();
                if (json.success && json.data) {
                    this.pricing = json.data;
                    this.errorMessage = '';
                }
            } catch (e) {
                console.error('Preview error:', e);
            }
        },

        applyCoupon() {
            if (!this.couponInput.trim()) return;
            this.form.coupon_code = this.couponInput.trim().toUpperCase();
            this.couponMessage = '';
            this.couponError = false;
            this.refreshPreview().then(() => {
                if (this.pricing.coupon_discount > 0) {
                    this.couponMessage = `Coupon "${this.pricing.coupon_code}" applied! You save &#2547;${this.pricing.coupon_discount.toFixed(0)}`;
                    this.couponError = false;
                } else if (this.pricing.coupon_code) {
                    this.couponMessage = 'Coupon applied but no discount on current items.';
                    this.couponError = true;
                } else {
                    this.couponMessage = 'Invalid or expired coupon code.';
                    this.couponError = true;
                    this.form.coupon_code = null;
                }
            });
        },

        canSubmit() {
            return this.form.customer_name.trim() !== ''
                && this.form.customer_phone.trim() !== ''
                && this.form.address_line.trim() !== ''
                && this.form.zone_id
                && this.form.items.length > 0;
        },

        async placeOrder() {
            if (!this.canSubmit() || this.submitting) return;
            this.submitting = true;
            this.errorMessage = '';

            try {
                const res = await fetch(`/api/landing/${this.slug}/checkout`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.form),
                });

                const json = await res.json();

                if (json.success && json.data?.redirect_url) {
                    window.location.href = json.data.redirect_url;
                    return;
                }

                this.errorMessage = json.message || 'Something went wrong. Please try again.';
            } catch (e) {
                this.errorMessage = 'Network error. Please check your connection and try again.';
            } finally {
                this.submitting = false;
            }
        },
    };
}
</script>

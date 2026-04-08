export default class CheckoutManager {
    constructor() {
        // ── State ──────────────────────────────────────────────
        this.zones = [];
        this.selectedZone = null;
        this.coupon = null; // { id, code, discount }
        this.submitting = false;
        this.previewData = null; // server pricing response

        // ── DOM: Form fields ───────────────────────────────────
        this.form = document.getElementById("checkoutForm");
        this.nameInput = document.getElementById("co_name");
        this.phoneInput = document.getElementById("co_phone");
        this.emailInput = document.getElementById("co_email");
        this.addressInput = document.getElementById("co_address");
        this.cityInput = document.getElementById("co_city");
        this.notesInput = document.getElementById("co_notes");

        // ── DOM: Shipping ──────────────────────────────────────
        this.zonesContainer = document.getElementById("shippingZones");
        this.zonesModule = document.getElementById("zonesModule");
        this.zonesLoader = document.getElementById("zonesLoader");

        // ── DOM: Coupon ────────────────────────────────────────
        this.couponInput = document.getElementById("co_coupon");
        this.couponBtn = document.getElementById("co_couponBtn");
        this.couponFeedback = document.getElementById("co_couponFeedback");

        // ── DOM: Summary ───────────────────────────────────────
        this.itemsList = document.getElementById("coItemsList");
        this.subtotalEl = document.getElementById("coSubtotal");
        this.discountRowEl = document.getElementById("coDiscountRow");
        this.discountEl = document.getElementById("coDiscount");
        this.shippingEl = document.getElementById("coShipping");
        this.totalEl = document.getElementById("coTotal");
        this.itemCountEl = document.getElementById("coItemCount");

        // ── DOM: Submit ────────────────────────────────────────
        this.placeOrderBtn = document.getElementById("placeOrderBtn");
        this.placeOrderLabel = document.getElementById("placeOrderLabel");

        this.init();
    }

    async init() {
        await this.waitForCart();

        if (!window.Cart?.state?.items?.length) {
            window.location.href = "/cart";
            return;
        }

        this.renderItems();
        await this.loadZones();
        this.loadCarriedCoupon();
        this.bindEvents();

        // Fetch server-authoritative pricing
        await this.fetchPreview();
    }

    // ── Cart ────────────────────────────────────────────────────

    waitForCart() {
        return new Promise((resolve) => {
            if (window.Cart?.initialized) return resolve();
            window.addEventListener("cart:updated", resolve, { once: true });
            setTimeout(resolve, 3000); // fallback
        });
    }

    renderItems() {
        const items = window.Cart.state.items;

        if (this.itemCountEl) {
            this.itemCountEl.textContent = `${items.length} item${items.length !== 1 ? "s" : ""}`;
        }

        if (!this.itemsList) return;
        this.itemsList.innerHTML = items
            .map((i) => {
                const isCombo = !!i.combo_name_snapshot;
                const name = isCombo
                    ? i.combo_name_snapshot
                    : i.product_name_snapshot;
                const variant = isCombo ? "Bundle" : i.variant_title_snapshot;
                const lineTotal = (i.unit_price * i.quantity).toFixed(2);
                const imageUrl = i.image_url || "/images/placeholder.png";

                return `<div class="flex items-center gap-3 py-3 border-b border-gray-100 last:border-0">
                <div class="w-12 h-12 rounded-lg bg-gray-50 overflow-hidden shrink-0 border border-gray-100">
                    <img src="${imageUrl}" alt="${name}" class="w-full h-full object-cover">
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate font-bengali">${name}</p>
                    <p class="text-xs text-gray-400">${variant ?? ""} × ${i.quantity}</p>
                </div>
                <p class="text-sm font-bold text-gray-800 font-bengali shrink-0">৳${lineTotal}</p>
            </div>`;
            })
            .join("");
    }

    /**
     * Render totals from server preview data (single source of truth).
     * Falls back to cart subtotal only if preview hasn't loaded yet.
     */
    renderTotals() {
        if (this.previewData) {
            const p = this.previewData;
            const discount = (p.tier_discount ?? 0) + (p.coupon_discount ?? 0);

            if (this.subtotalEl)
                this.subtotalEl.textContent = "৳" + p.subtotal.toFixed(2);

            if (discount > 0) {
                if (this.discountRowEl)
                    this.discountRowEl.classList.remove("hidden");
                if (this.discountEl)
                    this.discountEl.textContent = "−৳" + discount.toFixed(2);
            } else {
                if (this.discountRowEl)
                    this.discountRowEl.classList.add("hidden");
            }

            if (this.shippingEl) {
                this.shippingEl.textContent = !this.selectedZone
                    ? "Select zone"
                    : p.shipping_cost === 0
                      ? "Free"
                      : "৳" + p.shipping_cost.toFixed(2);
            }

            if (this.totalEl) {
                this.totalEl.textContent = !this.selectedZone
                    ? "—"
                    : "৳" + p.grand_total.toFixed(2);
            }
        } else {
            // Fallback before first preview loads
            const subtotal = window.Cart?.state?.subtotal ?? 0;
            if (this.subtotalEl)
                this.subtotalEl.textContent = "৳" + subtotal.toFixed(2);
            if (this.shippingEl) this.shippingEl.textContent = "Select zone";
            if (this.totalEl) this.totalEl.textContent = "—";
            if (this.discountRowEl) this.discountRowEl.classList.add("hidden");
        }
    }

    /**
     * Fetch authoritative pricing from backend preview endpoint.
     * Called on init, zone change, and coupon change.
     */
    async fetchPreview() {
        const items = (window.Cart?.state?.items ?? []).map((i) => ({
            ...(i.combo_id ? { combo_id: i.combo_id } : {}),
            ...(i.variant_id ? { variant_id: i.variant_id } : {}),
            quantity: i.quantity,
        }));

        if (!items.length) return;

        const payload = {
            items,
            coupon_code: this.coupon?.code ?? null,
            zone_id: this.selectedZone?.id ?? null,
        };

        try {
            const res = await fetch("/api/v1/checkout/preview", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN":
                        document.querySelector('meta[name="csrf-token"]')
                            ?.content ?? "",
                    "X-Session-Token": window.Cart?.token ?? "",
                },
                body: JSON.stringify(payload),
            });

            const json = await res.json();
            if (res.ok && json.data) {
                this.previewData = json.data;
            }
        } catch {
            // Preview failed — keep showing fallback totals
        }

        this.renderTotals();
    }

    // ── Shipping Zones ──────────────────────────────────────────

    async loadZones() {
        try {
            const res = await fetch("/api/v1/shipping-zones", {
                headers: { Accept: "application/json" },
            });
            const json = await res.json();
            this.zones = json.data || [];
            this.renderZones();
        } catch {
            if (this.zonesContainer) {
                this.zonesContainer.innerHTML = `<p class="text-sm text-red-500">Failed to load delivery zones. Please refresh.</p>`;
            }
        } finally {
            this.zonesLoader?.classList.add("hidden");
        }
    }

    renderZones() {
        if (!this.zonesContainer || !this.zones.length) return;

        this.zonesContainer.innerHTML = this.zones
            .map((zone) => {
                const freeNote = zone.free_shipping_threshold
                    ? `<span class="text-emerald-600 font-semibold">Free over ৳${zone.free_shipping_threshold}</span>`
                    : "";
                const days = zone.estimated_days
                    ? `${zone.estimated_days} day${zone.estimated_days > 1 ? "s" : ""}`
                    : "";

                return `<label class="zoneOption flex items-start gap-3 p-4 rounded-xl border-2 border-gray-100 cursor-pointer hover:border-green-300 transition-all has-checked:border-green-600 has-checked:bg-green-50">
                <input type="radio" name="zone_id" value="${zone.id}"
                    class="mt-0.5 accent-green-700 shrink-0" data-zone='${JSON.stringify(zone)}'>
                <div class="flex-1 min-w-0 font-bengali">
                    <p class="font-bold text-gray-800 text-sm">${zone.name}</p>
                    <div class="flex flex-wrap items-center gap-2 mt-0.5">
                        <span class="text-xs text-gray-500">৳${zone.base_charge} base charge</span>
                        ${freeNote ? `<span class="text-[10px] text-gray-300">·</span>${freeNote.replace('class="', 'class="text-xs ')}` : ""}
                        ${days ? `<span class="text-[10px] text-gray-300">·</span><span class="text-xs text-gray-500">${days}</span>` : ""}
                    </div>
                </div>
            </label>`;
            })
            .join("");

        // Bind zone selection — re-fetch preview on change
        this.zonesContainer
            .querySelectorAll('input[name="zone_id"]')
            .forEach((radio) => {
                radio.addEventListener("change", () => {
                    this.selectedZone = JSON.parse(radio.dataset.zone);
                    this.fetchPreview();
                });
            });
    }

    // ── Coupon ──────────────────────────────────────────────────

    loadCarriedCoupon() {
        try {
            const stored = sessionStorage.getItem("bionic_coupon");
            if (!stored) return;

            const coupon = JSON.parse(stored);
            this.coupon = coupon;

            if (this.couponInput) {
                this.couponInput.value = coupon.code;
                this.couponInput.disabled = true;
            }
            if (this.couponBtn) {
                this.couponBtn.textContent = "Remove";
                this.couponBtn.onclick = () => this._removeCoupon();
            }
            this._setCouponFeedback(`"${coupon.code}" applied`, "success");
            // Preview will be fetched by init() after this
        } catch {
            sessionStorage.removeItem("bionic_coupon");
        }
    }

    async applyCoupon() {
        const code = this.couponInput?.value?.trim()?.toUpperCase();
        if (!code) return;

        this._setCouponLoading(true);
        this._setCouponFeedback("", "");

        // Use preview endpoint to validate coupon in context of actual cart
        const items = (window.Cart?.state?.items ?? []).map((i) => ({
            ...(i.combo_id ? { combo_id: i.combo_id } : {}),
            ...(i.variant_id ? { variant_id: i.variant_id } : {}),
            quantity: i.quantity,
        }));

        try {
            const res = await fetch("/api/v1/checkout/preview", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN":
                        document.querySelector('meta[name="csrf-token"]')
                            ?.content ?? "",
                    "X-Session-Token": window.Cart?.token ?? "",
                },
                body: JSON.stringify({
                    items,
                    coupon_code: code,
                    zone_id: this.selectedZone?.id ?? null,
                }),
            });

            const json = await res.json();
            if (!res.ok) throw new Error(json.message || "Invalid coupon");

            const discount = json.data.coupon_discount;
            this.coupon = { code, discount };
            this.previewData = json.data;
            sessionStorage.setItem(
                "bionic_coupon",
                JSON.stringify(this.coupon),
            );

            this._setCouponFeedback(
                `"${code}" applied — ৳${discount.toFixed(2)} off`,
                "success",
            );
            if (this.couponInput) this.couponInput.disabled = true;
            if (this.couponBtn) {
                this.couponBtn.textContent = "Remove";
                this.couponBtn.onclick = () => this._removeCoupon();
            }
            this.renderTotals();
        } catch (e) {
            this.coupon = null;
            this._setCouponFeedback(e.message || "Invalid coupon", "error");
        } finally {
            this._setCouponLoading(false);
        }
    }

    _removeCoupon() {
        this.coupon = null;
        sessionStorage.removeItem("bionic_coupon");
        if (this.couponInput) {
            this.couponInput.value = "";
            this.couponInput.disabled = false;
        }
        if (this.couponBtn) {
            this.couponBtn.textContent = "Apply";
            this.couponBtn.onclick = () => this.applyCoupon();
        }
        this._setCouponFeedback("", "");
        this.fetchPreview();
    }

    _setCouponFeedback(msg, type) {
        if (!this.couponFeedback) return;
        this.couponFeedback.textContent = msg;
        this.couponFeedback.className =
            "text-xs mt-2 font-medium " +
            (type === "success"
                ? "text-green-600"
                : type === "error"
                  ? "text-red-500"
                  : "hidden");
    }

    _setCouponLoading(loading) {
        if (!this.couponBtn) return;
        this.couponBtn.disabled = loading;
        this.couponBtn.textContent = loading
            ? "Checking…"
            : this.coupon
              ? "Remove"
              : "Apply";
    }

    // ── Submit ──────────────────────────────────────────────────

    bindEvents() {
        this.form?.addEventListener("submit", (e) => {
            e.preventDefault();
            this.submit();
        });

        this.placeOrderBtn?.addEventListener("click", (e) => {
            e.preventDefault();
            this.submit();
        });
        this.couponBtn?.addEventListener("click", () => {
            if (this.coupon) this._removeCoupon();
            else this.applyCoupon();
        });
        this.couponInput?.addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                this.applyCoupon();
            }
        });
    }

    async submit() {
        if (this.submitting) return;

        const validation = this._validateForm();
        if (!validation.valid) {
            window.flash?.(validation.message, "error");
            validation.field?.focus();
            return;
        }

        this.submitting = true;
        this._setSubmitting(true);

        const items = window.Cart.state.items.map((i) => ({
            ...(i.combo_id ? { combo_id: i.combo_id } : {}),
            ...(i.variant_id ? { variant_id: i.variant_id } : {}),
            quantity: i.quantity,
        }));

        const payload = {
            customer_name: this.nameInput.value.trim(),
            customer_phone: this.phoneInput.value.trim(),
            customer_email: this.emailInput?.value?.trim() || null,
            address_line: this.addressInput.value.trim(),
            city: this.cityInput.value.trim(),
            zone_id: this.selectedZone.id,
            payment_method:
                document.querySelector('input[name="payment_method"]:checked')
                    ?.value ?? "cod",
            notes: this.notesInput?.value?.trim() || null,
            coupon_code: this.coupon?.code ?? null,
            checkout_token: window.Cart.token,
            items,
        };

        try {
            const res = await fetch("/api/v1/checkout", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN":
                        document.querySelector('meta[name="csrf-token"]')
                            ?.content ?? "",
                    "X-Session-Token": window.Cart.token,
                },
                body: JSON.stringify(payload),
            });

            const json = await res.json();
            if (!res.ok) throw new Error(json.message || "Order failed");

            // Clean up sessionStorage, then redirect
            sessionStorage.removeItem("bionic_coupon");
            window.flash?.("Order placed successfully!", "success", 3000);
            window.location.href = json.data.redirect_url;
        } catch (e) {
            window.flash?.(
                e.message || "Could not place order. Please try again.",
                "error",
            );
            this.submitting = false;
            this._setSubmitting(false);
        }
    }

    _validateForm() {
        const fields = [
            { el: this.nameInput, msg: "Please enter your full name." },
            { el: this.phoneInput, msg: "Please enter your phone number." },
            {
                el: this.addressInput,
                msg: "Please enter your delivery address.",
            },
            { el: this.cityInput, msg: "Please enter your city." },
        ];

        for (const { el, msg } of fields) {
            if (!el?.value?.trim())
                return { valid: false, message: msg, field: el };
        }

        if (!this.selectedZone) {
            // window.flash?.("Please select a delivery zone.", "error");
            zonesModule?.scrollIntoView({
                behavior: "smooth",
                block: "center",
            });
            zonesModule.classList.add("ring-2", "ring-red-500");
            setTimeout(() => {
                zonesModule.classList.remove("ring-2", "ring-red-500");
            }, 2000);
            return {
                valid: false,
                message: "Please select a delivery zone.",
                field: null,
            };
        }

        const paymentMethod = document.querySelector(
            'input[name="payment_method"]:checked',
        );
        if (!paymentMethod) {
            return {
                valid: false,
                message: "Please select a payment method.",
                field: null,
            };
        }

        if (!window.Cart?.state?.items?.length) {
            return {
                valid: false,
                message: "Your cart is empty.",
                field: null,
            };
        }

        return { valid: true };
    }

    _setSubmitting(loading) {
        if (!this.placeOrderBtn) return;
        this.placeOrderBtn.disabled = loading;
        if (this.placeOrderLabel) {
            this.placeOrderLabel.textContent = loading
                ? "Placing order…"
                : "Place Order";
        }
        this.placeOrderBtn.classList.toggle("opacity-70", loading);
    }
}

export default class CartPageRenderer {
    constructor() {
        // Page-specific containers
        this.itemsContainer = document.getElementById("pageCartItems");
        this.itemCountEl = document.getElementById("pageCartCount");
        this.subtotalEl = document.getElementById("pageSubtotal");
        this.discountRowEl = document.getElementById("pageDiscountRow");
        this.discountAmountEl = document.getElementById("pageDiscountAmount");
        this.totalEl = document.getElementById("pageTotal");
        this.checkoutBtn = document.getElementById("pageCheckoutBtn");
        this.couponInput = document.getElementById("couponInput");
        this.couponBtn = document.getElementById("couponApplyBtn");
        this.couponFeedback = document.getElementById("couponFeedback");
        this.emptyCta = document.getElementById("pageEmptyCta");
        this.summaryBox = document.getElementById("pageSummaryBox");

        // Coupon state
        this.coupon = null; // { id, code, discount }

        // Bind events
        window.addEventListener("cart:updated", () => this.render());
        this.couponBtn?.addEventListener("click", () => this.applyCoupon());
        this.couponInput?.addEventListener("keydown", (e) => {
            if (e.key === "Enter") this.applyCoupon();
        });
        this.checkoutBtn?.addEventListener("click", () => this.checkout());

        // Initial render (Cart may already be loaded)
        this.render();
    }

    render() {
        if (!window.Cart) return;

        const items = window.Cart.state.items || [];
        const subtotal = window.Cart.state.subtotal || 0;

        // Update item count badge
        if (this.itemCountEl) {
            this.itemCountEl.textContent = `${items.length} item${items.length !== 1 ? "s" : ""}`;
        }

        if (!items.length) {
            this._renderEmpty();
            return;
        }

        this._renderItems(items);
        this._renderTotals(subtotal);
    }

    _renderEmpty() {
        if (this.itemsContainer) {
            // Generate 3 skeleton items using Array.from and join them into a single string
            const skeletonItems = Array.from({ length: 3 })
                .map(
                    () => `
                            <div class="flex items-start gap-5 p-2">
                                <div class="skeleton-line w-24 h-24 rounded-xl shrink-0"></div>
                                <div class="flex-1 space-y-3 pt-1">
                                    <div class="skeleton-line h-4 w-3/5 rounded-md"></div>
                                    <div class="skeleton-line h-3 w-2/5 rounded-md"></div>
                                    <div class="skeleton-line h-4 w-1/4 rounded-md mt-4"></div>
                                </div>
                            </div>
                        `,
                )
                .join("");

            // Inject the final HTML
            this.itemsContainer.innerHTML = `
            <div id="cartSkeleton" class="divide-y divide-gray-50">
                ${skeletonItems}
            </div>
        `;
        }

        // Hide summary, show only empty state
        if (this.summaryBox)
            this.summaryBox.classList.add("opacity-50", "pointer-events-none");
        if (this.subtotalEl) this.subtotalEl.textContent = "৳0";
        if (this.totalEl) this.totalEl.textContent = "৳0";
        if (this.discountRowEl) this.discountRowEl.classList.add("hidden");
    }

    _renderItems(items) {
        if (this.summaryBox)
            this.summaryBox.classList.remove(
                "opacity-50",
                "pointer-events-none",
            );

        this.itemsContainer.innerHTML = items
            .map((item) => this._row(item))
            .join("");
        this._bindItemEvents();
    }

    /**
     * Tier nudge — always visible, updates reactively as quantity changes.
     * Active tier  → green  "saving ৳X per unit"
     * Next tier    → amber  "add N more to unlock X% off"
     */
    _tierHtml(i) {
        if (!i.tiers || !i.tiers.length) return "";

        if (i.tier_saving) {
            return `<span class="inline-flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">
                        <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Bulk deal active — saving ৳${i.tier_saving} per unit
                    </span>`;
        }

        const nextTier = i.tiers.find((t) => t.qty > i.quantity);
        if (!nextTier) return "";

        const need = nextTier.qty - i.quantity;
        const reward =
            nextTier.type === "percentage"
                ? `${nextTier.value}% off`
                : `৳${nextTier.value} off/unit`;

        return `<span class="inline-flex items-center gap-1.5 bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full">
                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    Add ${need} more to unlock ${reward}
                </span>`;
    }

    _row(i) {
        const isCombo = !!i.combo_name_snapshot;
        const displayName = isCombo
            ? i.combo_name_snapshot
            : i.product_name_snapshot;
        const displayVariant = isCombo
            ? "Bundle Offer"
            : i.variant_title_snapshot;
        const imageUrl = i.image_url || "/images/placeholder.png";
        const lineTotal = (i.unit_price * i.quantity).toFixed(2);

        const tierNudge = !isCombo
            ? `<div class="mt-2">${this._tierHtml(i)}</div>`
            : "";

        const priceSection =
            !isCombo && i.tier_saving && i.original_unit_price
                ? `<div class="text-right">
                   <p class="text-xs text-gray-400 line-through leading-none">৳${i.original_unit_price} × ${i.quantity}</p>
                   <p class="text-xs text-emerald-600 font-semibold leading-none mt-0.5">৳${i.unit_price} × ${i.quantity}</p>
                   <p class="text-base font-bold text-gray-900 font-bengali mt-0.5">৳${lineTotal}</p>
               </div>`
                : `<div class="text-right">
                   <p class="text-xs text-gray-400">৳${i.unit_price} × ${i.quantity}</p>
                   <p class="text-base font-bold text-gray-900 font-bengali">৳${lineTotal}</p>
               </div>`;

        return `
        <div class="pageCartRow flex items-start gap-5 py-5 border-b border-gray-100 last:border-0 last:pb-0 group" data-item-id="${i.id}">
            <!-- Image -->
            <div class="w-20 h-20 rounded-xl bg-gray-50 overflow-hidden shrink-0 border border-gray-100">
                <img src="${imageUrl}" alt="${displayName}" class="w-full h-full object-cover">
            </div>

            <!-- Info -->
            <div class="flex-1 min-w-0">
                <div class="flex justify-between items-start gap-2">
                    <div class="min-w-0 flex-1">
                        <h3 class="font-bold text-gray-800 text-base leading-tight truncate font-bengali">${displayName}</h3>
                        <p class="text-gray-400 text-sm mt-0.5 font-bengali">${displayVariant ?? ""}</p>
                        ${tierNudge}
                    </div>
                    <button class="pageRemoveBtn shrink-0 w-8 h-8 flex items-center justify-center rounded-full text-gray-300 hover:text-red-500 hover:bg-red-50 transition-all" title="Remove">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>

                <div class="flex items-center justify-between mt-3">
                    <!-- Qty control -->
                    <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm">
                        <button class="pageMinus w-9 h-9 flex items-center justify-center text-gray-400 hover:bg-gray-50 hover:text-gray-700 transition-colors font-bold text-lg border-r border-gray-200">−</button>
                        <div class="w-10 h-9 flex items-center justify-center text-sm font-bold text-gray-800 select-none">${i.quantity}</div>
                        <button class="pagePlus w-9 h-9 flex items-center justify-center text-gray-400 hover:bg-gray-50 hover:text-gray-700 transition-colors font-bold text-lg border-l border-gray-200">+</button>
                    </div>

                    <!-- Price -->
                    ${priceSection}
                </div>
            </div>
        </div>`;
    }

    _bindItemEvents() {
        this.itemsContainer.querySelectorAll(".pageCartRow").forEach((row) => {
            const itemId = row.dataset.itemId;

            row.querySelector(".pagePlus").onclick = () => {
                const item = this._getItem(itemId);
                if (item) window.Cart.update(itemId, item.quantity + 1);
            };

            row.querySelector(".pageMinus").onclick = () => {
                const item = this._getItem(itemId);
                if (!item) return;
                if (item.quantity <= 1) {
                    window.Cart.remove(itemId);
                } else {
                    window.Cart.update(itemId, item.quantity - 1);
                }
            };

            row.querySelector(".pageRemoveBtn").onclick = () => {
                window.Cart.remove(itemId);
            };
        });
    }

    _renderTotals(subtotal) {
        const discount = this.coupon?.discount ?? 0;
        const total = Math.max(0, subtotal - discount);

        if (this.subtotalEl)
            this.subtotalEl.textContent = "৳" + subtotal.toFixed(2);

        if (discount > 0) {
            if (this.discountRowEl)
                this.discountRowEl.classList.remove("hidden");
            if (this.discountAmountEl)
                this.discountAmountEl.textContent = "− ৳" + discount.toFixed(2);
        } else {
            if (this.discountRowEl) this.discountRowEl.classList.add("hidden");
        }

        if (this.totalEl) this.totalEl.textContent = "৳" + total.toFixed(2);
    }

    async applyCoupon() {
        const code = this.couponInput?.value?.trim();
        if (!code) return;

        const subtotal = window.Cart?.state?.subtotal ?? 0;

        this._setCouponLoading(true);
        this._setCouponFeedback("", "");

        try {
            const res = await fetch("/api/v1/coupon/validate", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN":
                        document.querySelector('meta[name="csrf-token"]')
                            ?.content ?? "",
                    "X-Session-Token": window.Cart?.token ?? "",
                },
                body: JSON.stringify({ code, order_amount: subtotal }),
            });

            const json = await res.json();

            if (!res.ok) throw new Error(json.message || "Invalid coupon");

            this.coupon = {
                id: json.data.coupon_id,
                code,
                discount: json.data.discount,
            };

            this._setCouponFeedback(
                `✓ "${code}" applied — ৳${json.data.discount.toFixed(2)} off`,
                "success",
            );
            this._renderTotals(subtotal);

            // Lock input after successful apply
            if (this.couponInput) this.couponInput.disabled = true;
            if (this.couponBtn) {
                this.couponBtn.textContent = "Remove";
                this.couponBtn.onclick = () => this._removeCoupon();
            }
        } catch (e) {
            this.coupon = null;
            this._setCouponFeedback(e.message || "Coupon invalid", "error");
        } finally {
            this._setCouponLoading(false);
        }
    }

    _removeCoupon() {
        this.coupon = null;
        if (this.couponInput) {
            this.couponInput.value = "";
            this.couponInput.disabled = false;
        }
        if (this.couponBtn) {
            this.couponBtn.textContent = "Apply";
            this.couponBtn.onclick = () => this.applyCoupon();
        }
        this._setCouponFeedback("", "");
        this._renderTotals(window.Cart?.state?.subtotal ?? 0);
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

    async checkout() {
        if (this.checkoutBtn) {
            this.checkoutBtn.disabled = true;
            this.checkoutBtn.textContent = "Processing…";
        }

        try {
            const payload = {};
            if (this.coupon?.id) payload.coupon_id = this.coupon.id;

            const res = await fetch("/api/v1/checkout", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-Session-Token": window.Cart?.token ?? "",
                    "X-CSRF-TOKEN":
                        document.querySelector('meta[name="csrf-token"]')
                            ?.content ?? "",
                },
                body: JSON.stringify(payload),
            });

            const json = await res.json();
            if (!res.ok) throw new Error(json.message || "Checkout failed");
            window.location.href = json.data.redirect_url;
        } catch (e) {
            window.flash?.(e.message || "Checkout failed", "error");
            if (this.checkoutBtn) {
                this.checkoutBtn.disabled = false;
                this.checkoutBtn.textContent = "Proceed to Checkout";
            }
        }
    }

    _getItem(id) {
        return window.Cart?.state?.items?.find((x) => x.id == id);
    }
}

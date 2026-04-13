export default class CartRenderer {
    constructor() {
        this.container = document.getElementById("cartItems");
        this.subtotalBox = document.getElementById("cartSubtotal");
        window.addEventListener("cart:updated", () => this.render());
    }

    render() {
        const items = window.Cart.state.items || [];
        if (!items.length) {
            this.container.innerHTML = `
        <div class="flex flex-col items-center justify-center py-20 px-6 text-center">
            <div class="relative mb-6">
                <div class="absolute inset-0 bg-primary/10 rounded-full blur-2xl transform scale-150"></div>
                <svg class="relative w-24 h-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z">
                    </path>
                </svg>
                <div class="absolute -bottom-1 -right-1 bg-white p-1 rounded-full shadow-sm">
                    <svg class="w-6 h-6 text-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>

            <h3 class="text-lg font-bold text-gray-900 mb-2 font-bengali">আপনার ব্যাগটি খালি!</h3>
            <p class="text-sm text-gray-500 mb-8 max-w-50 font-bengali">
                সেরা সব অফার লুফে নিতে আমাদের শপে ভিজিট করুন
            </p>

            <button onclick="window.Cart.close()" 
                class="inline-flex items-center justify-center px-8 py-3 bg-primary text-white text-sm font-bold rounded-full hover:bg-primary-dark transition-all shadow-lg shadow-primary/20 transform hover:-translate-y-0.5 active:scale-95 font-bengali">
                শপিং শুরু করুন
            </button>
        </div>
    `;
            this.subtotalBox.innerText = "৳0";
            return;
        }

        this.container.innerHTML = items.map((i) => this.row(i)).join("");
        this.subtotalBox.innerText = "৳" + window.Cart.state.subtotal;
        this.bind();
    }

    /**
     * Returns the tier nudge HTML for a cart item.
     * - If a tier is already active  → green "saving ৳X/unit" badge
     * - If next tier is reachable    → amber "add N more to save X" nudge
     * - No tiers at all              → empty string
     */
    tierHtml(i, compact = true) {
        if (!i.tiers || !i.tiers.length) return "";

        // Tier currently applied
        if (i.tier_saving) {
            return compact
                ? `<span class="font-bengali inline-flex items-center gap-1 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none whitespace-nowrap">
                       <svg class="w-2.5 h-2.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                       Saving ৳${i.tier_saving}/unit
                   </span>`
                : `<span class="font-bengali inline-flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">
                       <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                       Bulk deal active — saving ৳${i.tier_saving} per unit
                   </span>`;
        }

        // Find the next tier the customer hasn't unlocked yet
        const nextTier = i.tiers.find((t) => t.qty > i.quantity);
        if (!nextTier) return "";

        const need = nextTier.qty - i.quantity;
        const reward =
            nextTier.type === "percentage"
                ? `${nextTier.value}% off`
                : `৳${nextTier.value} off/unit`;

        return compact
            ? `<span class="font-bengali inline-flex items-center gap-1 bg-amber-50 border border-amber-200 text-amber-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none whitespace-nowrap">
                   <svg class="w-2.5 h-2.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                   Add ${need} more → ${reward}
               </span>`
            : `<span class="font-bengali inline-flex items-center gap-1.5 bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full">
                   <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                   Add ${need} more to unlock ${reward}
               </span>`;
    }

    row(i) {
        const isCombo = !!i.combo_name_snapshot;
        const displayName = isCombo
            ? i.combo_name_snapshot
            : i.product_name_snapshot;
        const displayVariant = isCombo
            ? "Bundle Offer"
            : i.variant_title_snapshot;
        const imageUrl =
            i.image_url ||
            (isCombo
                ? "assets/combo-products/combo.jpg"
                : "/images/product-placeholder.png");

        const tierNudge = !isCombo
            ? `<div class="mt-1">${this.tierHtml(i, true)}</div>`
            : "";

        // Strikethrough original price when tier discount is active
        const priceHtml =
            !isCombo && i.tier_saving && i.original_unit_price
                ? `<p class="text-[10px] text-gray-400 line-through leading-none">৳${i.original_unit_price} x ${i.quantity}</p>
               <p class="text-[10px] text-emerald-600 font-semibold leading-none">৳${i.unit_price} x ${i.quantity}</p>
               <p class="text-sm font-bold text-primary font-bengali">৳${(i.unit_price * i.quantity).toFixed(2)}</p>`
                : `<p class="text-[10px] text-gray-400 font-medium">৳${i.unit_price} x ${i.quantity}</p>
               <p class="text-sm font-bold text-primary font-bengali">৳${(i.unit_price * i.quantity).toFixed(2)}</p>`;

        return `
    <div class="cartRow group flex items-start gap-4 border-b border-gray-100 p-4 transition-all hover:bg-gray-50/50" data-item-id="${i.id}">
        <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg border border-gray-100 bg-gray-50">
            <img src="${imageUrl}" alt="${displayName}" class="h-full w-full object-cover">
        </div>

        <div class="flex flex-1 flex-col gap-1">
            <div class="flex justify-between items-start">
                <div class="min-w-0 flex-1 pr-1">
                    <h4 class="text-sm font-bold text-gray-900 line-clamp-1 font-bengali">${displayName}</h4>
                    <p class="text-[11px] text-gray-500 font-bengali">${displayVariant ?? ""}</p>
                    ${tierNudge}
                </div>
                <button class="remove text-gray-400 hover:text-red-500 transition-colors p-1 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="mt-2 flex items-center justify-between">
                <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-white">
                    <button class="minus w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-100 transition-colors border-r border-gray-200 cursor-pointer">-</button>
                    <div class="w-8 h-7 flex items-center justify-center text-xs font-bold text-gray-800">${i.quantity}</div>
                    <button class="plus w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-100 transition-colors border-l border-gray-200 cursor-pointer">+</button>
                </div>

                <div class="text-right">
                    ${priceHtml}
                </div>
            </div>
        </div>
    </div>`;
    }

    bind() {
        this.container.querySelectorAll(".cartRow").forEach((row) => {
            const itemId = row.dataset.itemId;

            row.querySelector(".plus").onclick = () => {
                const item = this.getItem(itemId);
                window.Cart.update(itemId, parseInt(item.quantity) + 1);
            };

            row.querySelector(".minus").onclick = () => {
                const item = this.getItem(itemId);
                if (item.quantity <= 1) {
                    window.Cart.remove(itemId);
                    return;
                }
                window.Cart.update(itemId, parseInt(item.quantity) - 1);
            };

            row.querySelector(".remove").onclick = () => {
                window.Cart.remove(itemId);
            };
        });
    }

    getItem(id) {
        return window.Cart.state.items.find((x) => x.id == id);
    }
}

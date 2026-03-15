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

    row(i) {
        // Determine name and title based on type
        console.log("Rendering item:", i);
        const displayName = i.combo_id ? i.combo_name_snapshot : i.product_name_snapshot;
        const displayVariant = i.combo_id ? "Bundle Offer" : i.variant_title_snapshot;

        return `
    <div class="cartRow group flex items-start gap-4 border-b border-gray-100 p-4 transition-all hover:bg-gray-50/50" data-item-id="${i.id}">
        <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg border border-gray-100 bg-gray-50">
            <img src="${i.image_url}" alt="${displayName}" class="h-full w-full object-cover">
        </div>

        <div class="flex flex-1 flex-col gap-1">
            <div class="flex justify-between items-start">
                <div>
                    <h4 class="text-sm font-bold text-gray-900 line-clamp-1 font-bengali">${displayName}</h4>
                    <p class="text-[11px] text-gray-500 font-bengali">${displayVariant}</p>
                </div>
                <button class="remove text-gray-400 hover:text-red-500 transition-colors p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="mt-2 flex items-center justify-between">
                <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-white">
                    <button class="minus w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-100 transition-colors border-r border-gray-200">-</button>
                    <div class="w-8 h-7 flex items-center justify-center text-xs font-bold text-gray-800">${i.quantity}</div>
                    <button class="plus w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-100 transition-colors border-l border-gray-200">+</button>
                </div>

                <div class="text-right">
                    <p class="text-[10px] text-gray-400 font-medium">৳${i.unit_price} x ${i.quantity}</p>
                    <p class="text-sm font-bold text-primary font-bengali">৳${(i.unit_price * i.quantity).toFixed(2)}</p>
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
                window.Cart.update(itemId, item.quantity + 1);
            };

            row.querySelector(".minus").onclick = () => {
                const item = this.getItem(itemId);
                if (item.quantity <= 1) {
                    window.Cart.remove(itemId);
                    return;
                }
                window.Cart.update(itemId, item.quantity - 1);
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
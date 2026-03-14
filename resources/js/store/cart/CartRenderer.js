class CartRenderer {
    constructor() {
        this.container = document.getElementById("cartItems");
        this.totalBox = document.getElementById("cartSubtotal");

        this.bindEvents();
    }

    bindEvents() {
        window.addEventListener("cart:updated", () => {
            this.render();
        });

        window.addEventListener("cart:opened", () => {
            this.render();
        });
    }

    render() {
        const items = window.Cart.state.items || [];

        if (!items.length) {
            this.renderEmpty();
            return;
        }

        this.container.innerHTML = items.map((i) => this.card(i)).join("");

        this.renderSubtotal();

        this.bindControls();
    }

    renderEmpty() {
        this.container.innerHTML = `
            <div class="h-full flex flex-col items-center justify-center text-center py-20 animate-fade">
                <div class="text-5xl mb-4">🛒</div>
                <h3 class="font-semibold text-lg">Your cart is empty</h3>
                <p class="text-sm text-slate-400 mt-1">Add some natural goodness 🌿</p>
            </div>
        `;

        this.totalBox.innerHTML = "৳0";
    }

    renderSubtotal() {
        this.totalBox.innerHTML = "৳" + window.Cart.state.subtotal;
    }

    card(item) {
        const v = item.variant;

        return `
        <div 
            data-variant="${v.id}"
            class="cart-row group flex gap-3 p-4 border-b border-slate-100 hover:bg-slate-50 transition">

            <img 
                src="${v.product.thumbnail}" 
                class="w-16 h-16 rounded-xl object-cover">

            <div class="flex-1 min-w-0">

                <div class="flex justify-between">

                    <div class="font-semibold text-sm leading-tight line-clamp-2">
                        ${v.product.name}
                    </div>

                    <button class="removeItem text-slate-400 hover:text-red-500 transition">
                        ✕
                    </button>

                </div>

                <div class="text-xs text-slate-400 mt-1">
                    ${v.title}
                </div>

                <div class="flex items-center justify-between mt-3">

                    <div class="flex items-center border rounded-full overflow-hidden">

                        <button class="qtyMinus px-3 py-1 hover:bg-slate-100">−</button>

                        <div class="px-3 text-sm">${item.quantity}</div>

                        <button class="qtyPlus px-3 py-1 hover:bg-slate-100">+</button>

                    </div>

                    <div class="font-bold text-primary text-sm">
                        ৳${item.line_total}
                    </div>

                </div>

            </div>

        </div>
        `;
    }

    bindControls() {
        this.container.querySelectorAll(".cart-row").forEach((row) => {
            const variant = row.dataset.variant;

            const minus = row.querySelector(".qtyMinus");
            const plus = row.querySelector(".qtyPlus");
            const remove = row.querySelector(".removeItem");

            minus.onclick = async () => {
                const item = this.getItem(variant);

                if (!item) return;

                const next = item.quantity - 1;

                if (next <= 0) {
                    row.classList.add("opacity-0", "scale-95");

                    setTimeout(() => {
                        window.Cart.remove(variant);
                    }, 200);

                    return;
                }

                window.Cart.update(variant, next);
            };

            plus.onclick = () => {
                const item = this.getItem(variant);
                if (!item) return;

                window.Cart.update(variant, item.quantity + 1);
            };

            remove.onclick = () => {
                row.classList.add("opacity-0", "scale-95");

                setTimeout(() => {
                    window.Cart.remove(variant);
                }, 200);
            };
        });
    }

    getItem(variantId) {
        return window.Cart.state.items.find((i) => i.variant.id == variantId);
    }
}

window.CartUI = new CartRenderer();

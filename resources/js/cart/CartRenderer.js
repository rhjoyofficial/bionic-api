export default class CartRenderer {
    constructor() {
        this.container = document.getElementById("cartItems");
        this.subtotalBox = document.getElementById("cartSubtotal");

        window.addEventListener("cart:updated", () => this.render());
    }

    render() {
        const items = window.Cart.state.items || [];
        // console.log("Rendering cart with items:", items);
        if (!items.length) {
            this.container.innerHTML = "<p class='p-6'>Cart empty</p>";
            this.subtotalBox.innerText = "৳0";
            return;
        }

        this.container.innerHTML = items.map((i) => this.row(i)).join("");

        this.subtotalBox.innerText = "৳" + window.Cart.state.subtotal;

        this.bind();
    }

    row(i) {
        return `
    <div class="cartRow group relative flex items-start gap-4 border-b border-gray-100 p-4 transition-all hover:bg-gray-50/50" data-variant="${i.variant_id}">
        
        <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg border border-gray-100 bg-gray-50">
            <img src="${i.image_url}" alt="${i.name}" class="h-full w-full object-cover">
        </div>

        <div class="flex flex-1 flex-col gap-1">
            <div class="flex justify-between items-start">
                <div>
                    <h4 class="text-sm font-bold text-gray-900 line-clamp-1 font-bengali">${i.name}</h4>
                    <p class="text-[11px] text-gray-500 font-bengali">${i.variant_title}</p>
                </div>
                
                <button class="remove text-gray-400 hover:text-red-500 transition-colors p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="mt-2 flex items-center justify-between">
                <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-white">
                    <button class="minus w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors border-r border-gray-200">-</button>
                    <div class="w-8 h-7 flex items-center justify-center text-xs font-bold text-gray-800">${i.quantity}</div>
                    <button class="plus w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors border-l border-gray-200">+</button>
                </div>

                <div class="text-right">
                    <p class="text-[10px] text-gray-400 font-medium">৳${i.unit_price} x ${i.quantity}</p>
                    <p class="text-sm font-bold text-primary font-bengali">৳${i.subtotal}</p>
                </div>
            </div>
        </div>
    </div>
    `;
    }

    bind() {
        this.container.querySelectorAll(".cartRow").forEach((row) => {
            const id = row.dataset.variant;

            row.querySelector(".plus").onclick = () => {
                const item = this.getItem(id);
                window.Cart.update(id, item.quantity + 1);
            };

            row.querySelector(".minus").onclick = () => {
                const item = this.getItem(id);

                if (item.quantity <= 1) {
                    window.Cart.remove(id);
                    return;
                }

                window.Cart.update(id, item.quantity - 1);
            };

            row.querySelector(".remove").onclick = () => {
                window.Cart.remove(id);
            };
        });
    }

    getItem(id) {
        return window.Cart.state.items.find((x) => x.variant_id == id);
    }
}

window.CartUI = new CartRenderer();

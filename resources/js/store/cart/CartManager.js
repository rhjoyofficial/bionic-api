class CartManager {
    constructor() {
        this.state = {
            items: [],
            subtotal: 0,
            totalQty: 0,
            loading: false,
        };

        this.queue = [];
        this.lockedButtons = new Set();
        this.sessionToken = this.ensureToken();
        this.sidebar = document.getElementById("cartSidebar");
        this.overlay = document.getElementById("overlay");
        this.badge = document.getElementById("cartCount");

        this.init();
    }

    /* ---------------- INIT ---------------- */

    async init() {
        await this.refresh();

        this.autoRefresh();
        this.bindOverlay();

        window.dispatchEvent(new Event("cart:ready"));
    }

    /* ---------------- API ---------------- */

    async api(url, data = {}, method = "POST") {
        try {
            const res = await fetch(`/api/v1/cart${url}`, {
                method,
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-Session-Token": this.getToken(),
                },
                body: method === "GET" ? null : JSON.stringify(data),
            });

            const json = await res.json();

            if (!res.ok) throw json;

            return json;
        } catch (e) {
            this.flash("Network error", "error");

            throw e;
        }
    }

    /* ---------------- STATE ---------------- */
    ensureToken() {
        let token = localStorage.getItem("bionic_cart_token");

        if (!token) {
            token = crypto.randomUUID();
            localStorage.setItem("bionic_cart_token", token);
        }

        return token;
    }

    getToken() {
        return this.sessionToken;
    }

    setState(cart) {
        this.state.items = cart.items || [];
        this.state.subtotal = cart.subtotal || 0;
        this.state.totalQty = cart.total_qty || 0;

        this.updateBadge();

        window.dispatchEvent(new Event("cart:updated"));
    }

    updateBadge() {
        if (!this.badge) return;

        this.badge.innerText = this.state.totalQty;

        this.badge.classList.add("scale-125");

        setTimeout(() => this.badge.classList.remove("scale-125"), 200);
    }

    /* ---------------- FLASH ---------------- */

    flash(message, type = "success") {
        if (typeof window.showFlash === "function") {
            window.showFlash(message, type);
        }
    }

    /* ---------------- REFRESH ---------------- */

    async refresh() {
        try {
            const res = await fetch(`/api/v1/cart`, {
                headers: {
                    "X-Session-Token": this.getToken(),
                },
            });
            const json = await res.json();

            this.setState(json.data);
        } catch {
            console.log("Cart refresh failed");
        }
    }

    autoRefresh() {
        setInterval(() => {
            if (this.sidebar?.classList.contains("translate-x-full")) {
                this.refresh();
            }
        }, 25000);
    }

    /* ---------------- ADD ---------------- */

    async add(variantId, qty = 1, button = null) {
        if (button) {
            if (this.lockedButtons.has(button)) return;

            this.lockedButtons.add(button);
            button.classList.add("opacity-50", "pointer-events-none");
        }

        this.open();

        try {
            const res = await this.api("/add", {
                variant_id: variantId,
                quantity: qty,
            });

            this.setState(res.data);

            this.flash("Added to cart");

            window.dispatchEvent(new Event("cart:itemAdded"));
        } catch (e) {
            this.flash(e.message || "Failed", "error");
        } finally {
            if (button) {
                button.classList.remove("opacity-50", "pointer-events-none");
                this.lockedButtons.delete(button);
            }
        }
    }

    /* ---------------- UPDATE ---------------- */

    async update(variantId, qty) {
        try {
            const res = await this.api("/update", {
                variant_id: variantId,
                quantity: qty,
            });

            this.setState(res.data);
        } catch (e) {
            this.flash("Stock limit reached", "error");

            await this.refresh();
        }
    }

    /* ---------------- REMOVE ---------------- */

    async remove(variantId) {
        try {
            const res = await this.api("/remove", {
                variant_id: variantId,
            });

            this.setState(res.data);

            this.flash("Item removed");

            window.dispatchEvent(new Event("cart:itemRemoved"));
        } catch {
            this.flash("Remove failed", "error");
        }
    }

    /* ---------------- CLEAR ---------------- */

    async clear() {
        try {
            await this.api("/clear", {}, "DELETE");

            await this.refresh();

            this.flash("Cart cleared");
        } catch {
            this.flash("Clear failed", "error");
        }
    }

    /* ---------------- CHECKOUT ---------------- */

    async checkout() {
        try {
            const res = await fetch(`/api/v1/checkout`, {
                method: "POST",
            });

            const json = await res.json();

            if (!res.ok) throw json;

            window.location.href = json.data.redirect_url;
        } catch {
            this.flash("Checkout failed", "error");
        }
    }

    /* ---------------- SIDEBAR ---------------- */

    open() {
        this.sidebar.classList.remove("translate-x-full");

        this.overlay.classList.remove("pointer-events-none");
        this.overlay.classList.remove("opacity-0");

        document.body.classList.add("overflow-hidden");

        window.dispatchEvent(new Event("cart:opened"));
    }

    close() {
        this.sidebar.classList.add("translate-x-full");

        this.overlay.classList.add("opacity-0");

        setTimeout(() => {
            this.overlay.classList.add("pointer-events-none");
        }, 300);

        document.body.classList.remove("overflow-hidden");

        window.dispatchEvent(new Event("cart:closed"));
    }

    bindOverlay() {
        this.overlay?.addEventListener("click", () => this.close());

        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape") this.close();
        });
    }
}

window.Cart = new CartManager();

export default class CartManager {
    constructor() {
        this.state = {
            items: [],
            subtotal: 0,
            totalQty: 0,
        };

        this.pending = false;
        this.lockedButtons = new Set();
        this.initialized = false;

        this.token = this.ensureToken();

        this.sidebar = document.getElementById("cartDrawer");
        this.badge = document.getElementById("cartCount");

        this.init();
    }

    ensureToken() {
        // Helper to read cookie
        const getCookie = (name) => {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(";").shift();
            return null;
        };

        // Helper to set cookie
        const setCookie = (name, value, days = 30) => {
            const date = new Date();
            date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
            document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/;SameSite=Lax`;
        };

        const cookieName = "bionic_cart_token";
        let cartToken = getCookie(cookieName);

        // If no cookie, check localStorage or generate a new one
        if (!cartToken) {
            cartToken = localStorage.getItem(cookieName) || crypto.randomUUID();
            // Sync it to both places
            setCookie(cookieName, cartToken);
            localStorage.setItem(cookieName, cartToken);
        }

        return cartToken;
    }

    async api(url, data = {}, method = "POST") {
        const res = await fetch(`/api/v1/cart${url}`, {
            method,
            headers: this._getHeaders(),
            body: method === "GET" ? null : JSON.stringify(data),
        });

        const json = await res.json();

        if (!res.ok) throw json;

        return json;
    }

    async init() {
        await this.refresh();
    }

    async refresh() {
        try {
            const res = await fetch(`/api/v1/cart`, {
                headers: this._getHeaders(),
            });
            const json = await res.json();
            this.setState(json.data);
        } catch {
            this.setState({
                items: [],
                subtotal: 0,
                totalQty: 0,
            });
        } finally {
            this.initialized = true;
        }
    }

    setState(payload) {
        // console.log("Cart updated:", payload);
        // console.log("All Items:", payload.items);
        // console.log("Subtotal:", payload.totals.subtotal);
        // console.log("Total Quantity:", payload.totals.total_qty);
        if (payload.prices_updated && typeof flash === "function") {
            window.flash?.(
                "Price Alert",
                "warning",
                5000,
                "Prices in your cart have been updated to reflect current rates.",
            );
        }

        this.state = {
            items: payload.items || [],
            subtotal: payload.totals.subtotal || 0,
            totalQty: payload.totals.total_qty || 0,
        };

        if (this.badge) {
            this.badge.innerText = this.state.totalQty;
        }

        window.dispatchEvent(new Event("cart:updated"));
    }

    async add(variantId, qty = 1, button = null) {
        return this._perfomCartAction(
            "/add",
            { variant_id: variantId, quantity: qty },
            button,
        );
    }

    async addCombo(comboId, qty = 1, button = null) {
        return this._perfomCartAction(
            "/add-combo",
            { combo_id: comboId, quantity: qty },
            button,
        );
    }

    _getHeaders() {
        const headers = {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-Session-Token": this.token,
        };

        // Only add CSRF if available (Blade context)
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrf) headers["X-CSRF-TOKEN"] = csrf;
        // console.log("Request Headers:", headers);

        return headers;
    }

    async _perfomCartAction(endpoint, data, button) {
        if (this.pending) return;
        this.pending = true;

        if (button) {
            this.lockedButtons.add(button);
            button.classList.add("opacity-50", "pointer-events-none");
        }

        try {
            const res = await this.api(endpoint, data);
            this.setState(res.data);
            if (typeof flash === "function" && !res.data.prices_updated)
                window.flash?.("Added to cart");
            this.open();
        } catch (e) {
            if (typeof flash === "function")
                window.flash?.(e.message || "Action failed", "error");
        } finally {
            this.pending = false;
            if (button) {
                button.classList.remove("opacity-50", "pointer-events-none");
                this.lockedButtons.delete(button);
            }
        }
    }

    async update(cartItemId, qty) {
        try {
            const res = await this.api("/update", {
                cart_item_id: cartItemId,
                quantity: qty,
            });
            this.setState(res.data);
        } catch (e) {
            if (typeof flash === "function")
                window.flash?.(e.message || "Update failed", "error");
        }
    }

    async remove(cartItemId) {
        try {
            const res = await this.api("/remove", { cart_item_id: cartItemId });
            this.setState(res.data);
        } catch (e) {
            if (typeof flash === "function")
                window.flash?.(e.message || "Remove failed", "error");
        }
    }

    async clear() {
        try {
            await this.api("/clear", {}, "DELETE");
            await this.refresh();
            window.flash?.('Cart cleared', 'success')
        } catch {
            window.flash?.("Clear failed", "error");
        }
    }

    /**
    * Navigate to the checkout page.
    * The actual order submission is handled by CheckoutManager on /checkout.
    */
    checkout() {
        window.location.href = "/checkout";
    }

    open() {
        const overlay = document.getElementById("overlay");
        this.sidebar?.classList.remove("translate-x-full");
        overlay?.classList.remove("opacity-0", "invisible");
        overlay?.classList.add("opacity-100");
        document.body.classList.add("overflow-hidden");
    }

    close() {
        const overlay = document.getElementById("overlay");
        this.sidebar?.classList.add("translate-x-full");
        overlay?.classList.remove("opacity-100");
        overlay?.classList.add("opacity-0", "invisible");
        document.body.classList.remove("overflow-hidden");
    }
}

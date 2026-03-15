export default class CartManager {
    constructor() {
        this.state = {
            items: [],
            subtotal: 0,
            totalQty: 0,
        };

        this.pending = false;
        this.lockedButtons = new Set();

        this.token = this.ensureToken();

        this.sidebar = document.getElementById("cartDrawer");
        this.badge = document.getElementById("cartCount");

        this.init();
    }

    ensureToken() {
        let cartToken = localStorage.getItem("bionic_cart_token");

        if (!cartToken) {
            cartToken = crypto.randomUUID();
            localStorage.setItem("bionic_cart_token", cartToken);
        }

        return cartToken;
    }

    async api(url, data = {}, method = "POST") {
        const res = await fetch(`/api/v1/cart${url}`, {
            method,
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-Session-Token": this.token,
            },
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
                headers: {
                    "X-Session-Token": this.token,
                },
            });
            const json = await res.json();
            this.setState(json.data);
        } catch {
            this.setState({
                items: [],
                subtotal: 0,
                totalQty: 0,
            });
        }
    }

    setState(payload) {
        // console.log("Cart updated:", payload);
        // console.log("All Items:", payload.items);
        // console.log("Subtotal:", payload.totals.subtotal);
        // console.log("Total Quantity:", payload.totals.total_qty);
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
        if (this.pending) return;

        this.pending = true;

        if (button) {
            if (this.lockedButtons.has(button)) return;

            this.lockedButtons.add(button);
            button.classList.add("opacity-50", "pointer-events-none");
        }

        try {
            const res = await this.api("/add", {
                variant_id: variantId,
                quantity: qty,
            });

            this.setState(res.data);

            if (typeof flash === "function") {
                flash("Added to cart");
            }

            this.open();
        } catch (e) {
            if (typeof flash === "function") {
                flash(e.message || "Cart failed", "error");
            }
        } finally {
            this.pending = false;

            if (button) {
                button.classList.remove("opacity-50", "pointer-events-none");
                this.lockedButtons.delete(button);
            }
        }
    }

    async update(variantId, qty) {
        try {
            const res = await this.api("/update", {
                variant_id: variantId,
                quantity: qty,
            });

            this.setState(res.data);

            if (typeof flash === "function") {
                flash("Quantity updated");
            }
        } catch (e) {
            if (typeof flash === "function") {
                flash(e.message || "Update failed", "error");
            }
        }
    }

    async remove(variantId) {
        try {
            const res = await this.api("/remove", {
                variant_id: variantId,
            });

            this.setState(res.data);

            if (typeof flash === "function") {
                flash("Item removed from cart");
            }
        } catch (e) {
            if (typeof flash === "function") {
                flash(e.message || "Remove failed", "error");
            }
        }
    }

    async clear() {
        try {
            await this.api("/clear", {}, "DELETE");
            await this.refresh(); this.flash("Cart cleared");
        }
        catch {
            this.flash("Clear failed", "error");
        }
    }

    async checkout() {
        try {
            const res = await fetch("/api/v1/checkout", { method: "POST", });
            const json = await res.json();
            if (!res.ok)
                throw json; window.location.href = json.data.redirect_url;
        }
        catch {
            this.flash("Checkout failed", "error");
        }
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

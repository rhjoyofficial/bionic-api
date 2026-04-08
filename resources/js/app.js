import "./bootstrap";
import "./flash";
import "./search-suggestion";
import "./filter/categoryFilter";
import VideoManager from "./managers/video-manager";

/* ===========================
   AUTH
=========================== */
import AuthManager from "./auth/AuthManager";

/* ===========================
   CART SYSTEM
=========================== */
import CartManager from "./cart/CartManager";
import CartRenderer from "./cart/CartRenderer";
import CartPageRenderer from "./cart/CartPageRenderer";
import CheckoutManager from "./managers/CheckoutManager";
import bindAddToCart from "./cart/AddToCartBinder";
import initProductCards from "./cart/product-card";

/* ===========================
   Detect page context
=========================== */
const isAuthPage = () => {
    const path = window.location.pathname;
    return (
        path === "/login" ||
        path === "/register" ||
        path.startsWith("/forgot-password") ||
        path.startsWith("/password/reset")
    );
};

/* ===========================
   DOM READY
=========================== */
document.addEventListener("DOMContentLoaded", () => {

    /* Flash button triggers */
    document.querySelectorAll("[data-flash]").forEach((button) => {
        button.addEventListener("click", function (e) {
            if (!this.dataset.flash) return;
            const message     = this.dataset.flashMessage     || "Operation successful!";
            const type        = this.dataset.flashType        || "success";
            const duration    = parseInt(this.dataset.flashDuration) || 5000;
            const description = this.dataset.flashDescription || "";
            window.flash?.(message, type, duration, description);
            if (this.tagName === "BUTTON" && (!this.type || this.type === "button")) {
                e.preventDefault();
            }
        });
    });

    /* Laravel validation flash */
    const errorBag = document.querySelector(".alert-danger");
    if (errorBag) {
        const errorText = errorBag.textContent.trim();
        if (errorText) {
            window.flash?.("Please fix the errors below", "error", 8000, errorText);
        }
    }

    /* Video Manager */
    new VideoManager({
        selector: "[data-video]",
        autoPauseOthers: true,
        pauseWhenOutOfView: true,
    });

    /* ===========================
       AUTH PAGES
    ============================ */
    if (isAuthPage()) {
        // Auth pages only need the auth manager — no cart booting
        new AuthManager();
        return; // stop here, skip all cart/checkout initialisation
    }

    /* ===========================
       CART BOOT (global — every non-auth page)
    ============================ */
    window.Cart   = new CartManager();
    window.CartUI = new CartRenderer();  // sidebar drawer

    bindAddToCart();
    initProductCards();

    /* ===========================
       PAGE-SPECIFIC BOOTS
    ============================ */

    // /cart page
    if (document.getElementById("pageCartItems")) {
        window.CartPage = new CartPageRenderer();
    }

    // /checkout page
    if (document.getElementById("checkoutForm")) {
        window.Checkout = new CheckoutManager();
    }
});

/* Global Flash helpers */
window.triggerFlash = function (message, type = "success", duration = 5000, description = "") {
    return window.flash?.(message, type, duration, description);
};

window.clearFlash = function () {
    window.flashSystem?.clear?.();
};

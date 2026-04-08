import "./bootstrap";
import "./flash";
import "./search-suggestion";
import "./filter/categoryFilter";
import VideoManager from "./managers/video-manager";

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
   AUTH
=========================== */
import AuthManager from "./auth/AuthManager";

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
            const message =
                this.dataset.flashMessage || "Operation successful!";
            const type = this.dataset.flashType || "success";
            const duration = parseInt(this.dataset.flashDuration) || 5000;
            const description = this.dataset.flashDescription || "";
            window.flash?.(message, type, duration, description);
            if (
                this.tagName === "BUTTON" &&
                (!this.type || this.type === "button")
            ) {
                e.preventDefault();
            }
        });
    });

    /* Laravel validation flash */
    const errorBag = document.querySelector(".alert-danger");
    if (errorBag) {
        const errorText = errorBag.textContent.trim();
        if (errorText) {
            window.flash?.(
                "Please fix the errors below",
                "error",
                8000,
                errorText,
            );
        }
    }

    /* Video Manager */
    new VideoManager({
        selector: "[data-video]",
        autoPauseOthers: true,
        pauseWhenOutOfView: true,
    });

    /* ===========================
       AUTH MANAGER (every page)
    ============================ */
    // Always boot AuthManager so the logout button in the header (present on
    // every authenticated page) has its click handler wired up.  On auth pages
    // we stop here before initialising the cart.
    window.Auth = new AuthManager();

    if (isAuthPage()) {
        return; // stop here, skip cart/checkout initialisation
    }

    /* ===========================
       CART BOOT (global — every non-auth page)
    ============================ */
    window.Cart = new CartManager();
    window.CartUI = new CartRenderer(); // sidebar drawer

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
window.triggerFlash = function (
    message,
    type = "success",
    duration = 5000,
    description = "",
) {
    return window.flash?.(message, type, duration, description);
};

window.clearFlash = function () {
    window.flashSystem?.clear?.();
};

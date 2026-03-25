import "./bootstrap";
import "./flash";
import "./search-suggestion";
import "./filter/categoryFilter";
import VideoManager from "./managers/video-manager";

/* ===========================
   CART SYSTEM (IMPORTANT)
=========================== */

import CartManager from "./cart/CartManager";
import CartRenderer from "./cart/CartRenderer";
import bindAddToCart from "./cart/AddToCartBinder";
import initProductCards from "./cart/product-card";

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
       CART BOOT (VERY IMPORTANT)
    ============================ */

    window.Cart = new CartManager();
    window.CartUI = new CartRenderer();

    bindAddToCart();
    initProductCards();
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

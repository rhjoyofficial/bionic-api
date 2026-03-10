import "./bootstrap";
import "./flash";
import VideoManager from "./managers/video-manager";
// Auto-attach flash to buttons with data-flash attribute
document.addEventListener("DOMContentLoaded", function () {
    //  Flash button triggers
    document.querySelectorAll("[data-flash]").forEach((button) => {
        button.addEventListener("click", function (e) {
            if (!this.dataset.flash) return;

            const message =
                this.dataset.flashMessage || "Operation successful!";
            const type = this.dataset.flashType || "success";
            const duration = parseInt(this.dataset.flashDuration) || 5000;
            const description = this.dataset.flashDescription || "";

            if (typeof window.flash === "function") {
                window.flash(message, type, duration, description);
            }

            // Prevent default if it's a test button without form
            if (
                this.tagName === "BUTTON" &&
                (!this.type || this.type === "button")
            ) {
                e.preventDefault();
            }
        });
    });

    //  Auto-flash on form errors (Laravel validation)
    const errorBag = document.querySelector(".alert-danger");
    if (errorBag) {
        const errorText = errorBag.textContent.trim();
        if (errorText && typeof window.flash === "function") {
            window.flash(
                "Please fix the errors below",
                "error",
                8000,
                errorText,
            );
        }
    }
    // Video Manager
    new VideoManager({
        selector: "[data-video]",
        autoPauseOthers: true,
        pauseWhenOutOfView: true,
    });
});

//  Helper function to trigger flash from anywhere
window.triggerFlash = function (
    message,
    type = "success",
    duration = 5000,
    description = "",
) {
    if (typeof window.flash === "function") {
        return window.flash(message, type, duration, description);
    }
    console.error("Flash system not loaded");
    return null;
};

//  Clear all flash messages
window.clearFlash = function () {
    if (window.flashSystem && typeof window.flashSystem.clear === "function") {
        window.flashSystem.clear();
    }
};

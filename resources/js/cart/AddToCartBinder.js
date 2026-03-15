export default function bindAddToCart() {
    document.addEventListener("click", function (e) {
        const btn = e.target.closest(".addToCartBtn");
        if (!btn) return;

        const variantId = btn.dataset.variant;

        if (!variantId) {
            window.flash?.("Variant missing", "error");
            return;
        }

        window.Cart?.add(variantId, 1, btn);
    });
}

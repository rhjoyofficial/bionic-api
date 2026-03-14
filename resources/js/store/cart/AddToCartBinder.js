document.addEventListener("click", function (e) {
    const btn = e.target.closest(".addToCartBtn");
    if (!btn) return;

    const card = btn.closest(".product-card");
    if (!card) return;

    const select = card.querySelector(".variantSelect");

    if (!select) {
        showFlash("Variant missing", "error");
        return;
    }

    const variantId = select.value;

    Cart.add(variantId, 1, btn);
});

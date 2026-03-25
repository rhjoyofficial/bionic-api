export default function initProductCards() {
    document.querySelectorAll(".product-card").forEach((card) => {
        const variants = JSON.parse(card.dataset.variants || "[]");
        if (!variants.length) return;

        // Element Selectors
        const capsules = card.querySelectorAll(".variant-capsule");
        const price = card.querySelector(".finalPrice");
        const old = card.querySelector(".oldPrice");
        const badge = card.querySelector(".discountBadge");
        const tier = card.querySelector(".tierPreview");
        const addBtn = card.querySelector(".addToCartBtn");
        const contactBtn = card.querySelector(".contactBtn");

        function render(v) {
            // 1. Update Prices
            if (price) {
                price.innerText = "৳" + v.final_price;
            }

            if (old) {
                if (v.discount_percent) {
                    old.innerText = "৳" + v.price;
                    old.classList.remove("hidden");
                } else {
                    old.classList.add("hidden");
                }
            }

            // 2. Update Discount Badge
            if (badge) {
                if (v.discount_percent) {
                    badge.innerText = "-" + v.discount_percent + "%";
                    badge.classList.remove("hidden");
                } else {
                    badge.classList.add("hidden");
                }
            }

            // 3. Update Stock Status / Buttons
            if (v.available_stock <= 0) {
                addBtn?.classList.add("hidden");
                contactBtn?.classList.remove("hidden");
            } else {
                addBtn?.classList.remove("hidden");
                contactBtn?.classList.add("hidden");
            }

            // 4. Update Tier Pricing Preview
            if (tier) {
                if (v.tiers?.length) {
                    tier.innerHTML = v.tiers
                        .map((t) => {
                            const val =
                                t.type === "percentage"
                                    ? `${t.value}%`
                                    : `৳${t.value}`;
                            return `
                                <div class="bg-white/80 backdrop-blur-md border border-primary/20 text-primary px-2 py-1 rounded-md shadow-sm">
                                    <p class="text-[9px] font-bold uppercase tracking-tight leading-none">Buy ${t.qty}+</p>
                                    <p class="text-[11px] font-black leading-tight mt-1 text-primary">Save ${val}</p>
                                </div>`;
                        })
                        .join("");
                } else {
                    tier.innerHTML = "";
                }
            }

            // 5. Update Active Capsule UI
            capsules.forEach((btn) => {
                if (btn.dataset.variantId == v.id) {
                    // Active State
                    btn.classList.add(
                        "border-primary",
                        "bg-primary/10",
                        "text-primary",
                    );
                    btn.classList.remove(
                        "border-gray-200",
                        "bg-gray-50",
                        "text-gray-600",
                    );
                } else {
                    // Inactive State
                    btn.classList.remove(
                        "border-primary",
                        "bg-primary/10",
                        "text-primary",
                    );
                    btn.classList.add(
                        "border-gray-200",
                        "bg-gray-50",
                        "text-gray-600",
                    );
                }
            });

            // 6. Update Add to Cart Data
            if (addBtn) {
                addBtn.dataset.variant = v.id;
            }
        }

        // --- INITIALIZATION ---

        // Bind Click Events to Capsules
        capsules.forEach((btn) => {
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                const selectedVariant = variants.find(
                    (x) => x.id == btn.dataset.variantId,
                );
                if (selectedVariant) render(selectedVariant);
            });
        });

        // Initial render with the first variant
        render(variants[0]);
    });
}

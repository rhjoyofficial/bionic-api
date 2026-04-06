export default function initProductCards() {
    document.querySelectorAll(".product-card").forEach((card) => {
        const variants = JSON.parse(card.dataset.variants || "[]");
        if (!variants.length) return;

        const select = card.querySelector(".variantSelect");
        const price = card.querySelector(".finalPrice");
        const old = card.querySelector(".oldPrice");
        const badge = card.querySelector(".discountBadge");
        const tier = card.querySelector(".tierPreview");
        const addBtn = card.querySelector(".addToCartBtn");
        const contactBtn = card.querySelector(".contactBtn");

        function render(v) {
            // 1. Update Prices (only if elements exist - Single Variant Mode)
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

            // 2. Update Discount Badge (always exists)
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

            // 5. Update the Data Attribute for the Add to Cart logic
            if (addBtn) {
                addBtn.dataset.variant = v.id;
            }
        }

        // --- INITIALIZATION ---

        let initial = variants[0];

        // If a select dropdown exists, sync with its current value
        if (select) {
            const found = variants.find((x) => x.id == select.value);
            if (found) initial = found;

            // Listen for changes
            select.addEventListener("change", (e) => {
                const selectedVariant = variants.find(
                    (x) => x.id == e.target.value,
                );
                if (selectedVariant) render(selectedVariant);
            });
        }

        // Run the first render
        render(initial);
    });
}

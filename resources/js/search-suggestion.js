const MIN_QUERY_LENGTH = 2;
const SUGGESTION_LIMIT = 6;

const escapeHtml = (value = "") =>
    value
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#39;");

const debounce = (fn, delay = 250) => {
    let timer = null;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
};

const renderSuggestions = (container, query, products = [], categories = []) => {
    if (!container) return;
    const term = query.trim();
    if (term.length < MIN_QUERY_LENGTH) {
        container.innerHTML = "";
        container.classList.add("hidden");
        return;
    }

    if (!products.length && !categories.length) {
        container.innerHTML = `<div class="px-4 py-3 text-sm text-gray-500">No suggestions found for “${escapeHtml(term)}”.</div>`;
        container.classList.remove("hidden");
        return;
    }

    const categoryMarkup = categories.length
        ? `
            <p class="px-4 pt-3 pb-1 text-[11px] uppercase tracking-wide text-gray-400">Categories</p>
            ${categories
                .map(
                    (cat) => `
                <a href="${cat.url}" class="block px-4 py-2 text-sm hover:bg-gray-50">
                    ${escapeHtml(cat.name)}
                </a>`,
                )
                .join("")}
        `
        : "";

    const productMarkup = products.length
        ? `
            <p class="px-4 pt-3 pb-1 text-[11px] uppercase tracking-wide text-gray-400">Products</p>
            ${products
                .map(
                    (product) => `
                <a href="/product/${encodeURIComponent(product.slug)}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50">
                    <img src="${escapeHtml(product.image_url || "")}" alt="${escapeHtml(product.name)}" class="w-10 h-10 object-cover rounded-md border border-gray-100">
                    <div class="min-w-0">
                        <p class="text-sm text-gray-800 truncate">${escapeHtml(product.name)}</p>
                        <p class="text-xs text-gray-500 truncate">${escapeHtml(product.category_name)}</p>
                    </div>
                </a>`,
                )
                .join("")}
        `
        : "";

    container.innerHTML = categoryMarkup + productMarkup;
    container.classList.remove("hidden");
};

const initSearchWidget = (inputId, buttonId, suggestionsId) => {
    const input = document.getElementById(inputId);
    const button = document.getElementById(buttonId);
    const suggestions = document.getElementById(suggestionsId);

    if (!input || !button || !suggestions) return;

    const categories = Array.from(
        document.querySelectorAll("#categoriesDropdown a"),
    ).map((link) => {
        return {
            url: link.getAttribute("href") || "#",
            name: link.textContent?.trim() || "",
        };
    });

    const runSearch = () => {
        const term = input.value.trim();
        const params = new URLSearchParams();
        if (term) params.set("q", term);
        window.location.href = `/products?${params.toString()}`;
    };

    const fetchSuggestions = debounce(async () => {
        const term = input.value.trim();
        if (term.length < MIN_QUERY_LENGTH) {
            renderSuggestions(suggestions, "");
            return;
        }

        const matchedCategories = categories
            .filter((cat) =>
                cat.name.toLowerCase().includes(term.toLowerCase()),
            )
            .slice(0, 3);

        try {
            const res = await fetch(
                `/api/v1/products/search?q=${encodeURIComponent(term)}`,
                { headers: { Accept: "application/json" } },
            );
            const payload = await res.json();
            const products = (payload?.data || [])
                .filter((item) => item?.category?.slug)
                .slice(0, SUGGESTION_LIMIT)
                .map((item) => ({
                    slug: item.slug,
                    name: item.name,
                    image_url: item.image_url,
                    category_name: item.category.name || "Product",
                }));

            renderSuggestions(suggestions, term, products, matchedCategories);
        } catch {
            renderSuggestions(suggestions, term, [], matchedCategories);
        }
    }, 200);

    input.addEventListener("input", fetchSuggestions);
    input.addEventListener("focus", fetchSuggestions);

    input.addEventListener("keydown", (event) => {
        if (event.key === "Enter") {
            event.preventDefault();
            runSearch();
        }
    });

    button.addEventListener("click", runSearch);

    document.addEventListener("click", (event) => {
        if (!suggestions.contains(event.target) && event.target !== input) {
            suggestions.classList.add("hidden");
        }
    });
};

document.addEventListener("DOMContentLoaded", () => {
    initSearchWidget("searchInput", "searchButton", "searchSuggestions");
    initSearchWidget(
        "searchInputMobile",
        "searchButtonMobile",
        "searchSuggestionsMobile",
    );
});

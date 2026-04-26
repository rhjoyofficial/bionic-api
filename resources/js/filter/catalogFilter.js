/**
 * CatalogFilter — vanilla JS filter + sort manager for /products and /category/* pages.
 *
 * Strategy: URL-based server-side filtering.
 * On any filter change the class builds a new query string and navigates to it.
 * This gives us:
 *  ✅ Working pagination (withQueryString preserves filters across pages)
 *  ✅ SEO-friendly URLs
 *  ✅ Browser back/forward works correctly
 *  ✅ Zero dependency on Alpine or any JS framework
 */
class CatalogFilter {
    constructor() {
        this.params = new URLSearchParams(window.location.search);

        this.els = {
            categoryInputs: document.querySelectorAll('[data-filter-category]'),
            sortSelect:     document.getElementById('sortSelect'),
            priceMin:       document.getElementById('priceMin'),
            priceMax:       document.getElementById('priceMax'),
            priceMinDisplay: document.getElementById('priceMinDisplay'),
            priceMaxDisplay: document.getElementById('priceMaxDisplay'),
            rangeHighlight: document.getElementById('rangeHighlight'),
            applyPrice:     document.getElementById('applyPrice'),
            inStockFilter:  document.getElementById('inStockFilter'),
            clearFilters:   document.getElementById('clearFilters'),
            filterBadge:    document.getElementById('filterBadge'),
            mobileOpenBtn:  document.getElementById('openFilters'),
            mobileDrawer:   document.getElementById('filterDrawer'),
            mobileOverlay:  document.getElementById('filterOverlay'),
            activeTagsBox:  document.getElementById('activeFilterTags'),
        };

        this.syncUIFromURL();
        this.bindEvents();
    }

    // ── Event Binding ──────────────────────────────────────────────────────

    bindEvents() {
        // Category radios
        this.els.categoryInputs.forEach(input => {
            input.addEventListener('change', () => this.applyFilters());
        });

        // Sort
        this.els.sortSelect?.addEventListener('change', () => this.applyFilters());

        // Price range — live display updates; navigation on explicit Apply click
        this.els.priceMin?.addEventListener('input', () => {
            this.clampPriceHandles();
            this.updatePriceDisplay();
            this.updateRangeHighlight();
        });
        this.els.priceMax?.addEventListener('input', () => {
            this.clampPriceHandles();
            this.updatePriceDisplay();
            this.updateRangeHighlight();
        });
        this.els.applyPrice?.addEventListener('click', () => this.applyFilters());

        // In Stock
        this.els.inStockFilter?.addEventListener('change', () => this.applyFilters());

        // Clear all
        this.els.clearFilters?.addEventListener('click', () => this.clearAll());

        // Mobile drawer open/close
        this.els.mobileOpenBtn?.addEventListener('click', () => this.openDrawer());
        this.els.mobileOverlay?.addEventListener('click', () => this.closeDrawer());

        // Mobile price sliders — live display update
        document.querySelectorAll('[data-mobile-price]').forEach(el => {
            el.addEventListener('input', () => this.updateMobilePriceDisplay());
        });
    }

    // ── Core: Build URL and Navigate ──────────────────────────────────────

    applyFilters() {
        const newParams = new URLSearchParams();

        // Preserve existing search query
        const q = this.params.get('q');
        if (q) newParams.set('q', q);

        // Category
        const checkedCat = document.querySelector('[data-filter-category]:checked');
        const catSlug = checkedCat?.dataset.filterCategory;
        if (catSlug && catSlug !== 'all') {
            newParams.set('category', catSlug);
        }

        // Sort
        const sort = this.els.sortSelect?.value;
        if (sort && sort !== 'latest') {
            newParams.set('sort', sort);
        }

        // Price range — only add if user has moved sliders away from defaults
        const priceMin = parseInt(this.els.priceMin?.value || 0);
        const priceMax = parseInt(this.els.priceMax?.value || 0);
        const absMin   = parseInt(this.els.priceMin?.min  || 0);
        const absMax   = parseInt(this.els.priceMax?.max  || 99999);

        if (priceMin > absMin) newParams.set('price_min', priceMin);
        if (priceMax < absMax) newParams.set('price_max', priceMax);

        // In Stock
        if (this.els.inStockFilter?.checked) {
            newParams.set('in_stock', '1');
        }

        const qs = newParams.toString();
        window.location.href = window.location.pathname + (qs ? '?' + qs : '');
    }

    clearAll() {
        // Preserve ?q= if present (search query should survive a clear)
        const newParams = new URLSearchParams();
        const q = this.params.get('q');
        if (q) newParams.set('q', q);

        const qs = newParams.toString();
        window.location.href = window.location.pathname + (qs ? '?' + qs : '');
    }

    // ── Sync UI from current URL on page load ─────────────────────────────

    syncUIFromURL() {
        const p = this.params;

        // Category — check the radio matching the current ?category= slug
        const activeCat = p.get('category') || 'all';
        const catInput = document.querySelector(`[data-filter-category="${activeCat}"]`);
        if (catInput) catInput.checked = true;

        // Sort
        if (this.els.sortSelect && p.get('sort')) {
            this.els.sortSelect.value = p.get('sort');
        }

        // Price
        if (this.els.priceMin && p.get('price_min')) {
            this.els.priceMin.value = p.get('price_min');
        }
        if (this.els.priceMax && p.get('price_max')) {
            this.els.priceMax.value = p.get('price_max');
        }

        // In Stock
        if (this.els.inStockFilter) {
            this.els.inStockFilter.checked = p.has('in_stock');
        }

        // Refresh derived UI
        this.updatePriceDisplay();
        this.updateRangeHighlight();
        this.updateFilterBadge();
        this.renderActiveTags();
    }

    // ── Price Slider Helpers ───────────────────────────────────────────────

    clampPriceHandles() {
        const minEl = this.els.priceMin;
        const maxEl = this.els.priceMax;
        if (!minEl || !maxEl) return;

        const minVal = parseInt(minEl.value);
        const maxVal = parseInt(maxEl.value);

        // Prevent the two thumbs from crossing
        if (minVal >= maxVal) {
            // Push the other thumb just one step away
            const step = parseInt(minEl.step) || 1;
            if (document.activeElement === minEl) {
                minEl.value = maxVal - step;
            } else {
                maxEl.value = minVal + step;
            }
        }
    }

    updatePriceDisplay() {
        const min = parseInt(this.els.priceMin?.value || 0);
        const max = parseInt(this.els.priceMax?.value || 0);

        if (this.els.priceMinDisplay) {
            this.els.priceMinDisplay.textContent = '৳' + min.toLocaleString('en-BD');
        }
        if (this.els.priceMaxDisplay) {
            this.els.priceMaxDisplay.textContent = '৳' + max.toLocaleString('en-BD');
        }
    }

    updateRangeHighlight() {
        const minEl = this.els.priceMin;
        const maxEl = this.els.priceMax;
        const hl    = this.els.rangeHighlight;
        if (!minEl || !maxEl || !hl) return;

        const absMin   = parseInt(minEl.min);
        const absMax   = parseInt(minEl.max);
        const range    = absMax - absMin;

        if (range <= 0) return;

        const leftPct  = ((parseInt(minEl.value) - absMin) / range) * 100;
        const rightPct = 100 - ((parseInt(maxEl.value) - absMin) / range) * 100;

        hl.style.left  = leftPct  + '%';
        hl.style.right = rightPct + '%';
    }

    // ── Active Filter Badge & Tags ─────────────────────────────────────────

    updateFilterBadge() {
        const p = this.params;
        const filterKeys = ['category', 'price_min', 'price_max', 'in_stock', 'sort'];
        const count = filterKeys.filter(k => p.has(k)).length;

        if (this.els.filterBadge) {
            this.els.filterBadge.textContent = count;
            this.els.filterBadge.classList.toggle('hidden', count === 0);
        }
    }

    renderActiveTags() {
        const box = this.els.activeTagsBox;
        if (!box) return;

        const p = this.params;
        const tags = [];

        if (p.get('category')) {
            tags.push({ key: 'category', label: 'Category: ' + p.get('category') });
        }
        if (p.get('sort') && p.get('sort') !== 'latest') {
            const sortLabels = { price_asc: 'Price ↑', price_desc: 'Price ↓', latest: 'Newest' };
            tags.push({ key: 'sort', label: 'Sort: ' + (sortLabels[p.get('sort')] || p.get('sort')) });
        }
        if (p.get('price_min') || p.get('price_max')) {
            const min = p.get('price_min') ? '৳' + parseInt(p.get('price_min')).toLocaleString('en-BD') : '';
            const max = p.get('price_max') ? '৳' + parseInt(p.get('price_max')).toLocaleString('en-BD') : '';
            const label = min && max ? `Price: ${min} – ${max}` : min ? `Min: ${min}` : `Max: ${max}`;
            tags.push({ key: 'price', label });
        }
        if (p.has('in_stock')) {
            tags.push({ key: 'in_stock', label: 'In Stock Only' });
        }

        box.innerHTML = '';

        if (tags.length === 0) {
            box.classList.add('hidden');
            return;
        }

        box.classList.remove('hidden');

        tags.forEach(tag => {
            const pill = document.createElement('button');
            pill.className = 'inline-flex items-center gap-1.5 bg-primary/10 text-primary text-xs font-medium px-3 py-1.5 rounded-full hover:bg-primary/20 transition-colors cursor-pointer';
            pill.innerHTML = `<span>${tag.label}</span><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>`;
            pill.addEventListener('click', () => this.removeFilter(tag.key));
            box.appendChild(pill);
        });
    }

    removeFilter(key) {
        const newParams = new URLSearchParams(window.location.search);
        if (key === 'price') {
            newParams.delete('price_min');
            newParams.delete('price_max');
        } else {
            newParams.delete(key);
        }
        const qs = newParams.toString();
        window.location.href = window.location.pathname + (qs ? '?' + qs : '');
    }

    // ── Mobile Drawer ─────────────────────────────────────────────────────

    openDrawer() {
        this.els.mobileDrawer?.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        this.updateMobilePriceDisplay(); // sync display on open
        requestAnimationFrame(() => {
            this.els.mobileDrawer?.querySelector('aside')?.classList.add('translate-x-0');
            this.els.mobileDrawer?.querySelector('aside')?.classList.remove('-translate-x-full');
        });
    }

    closeDrawer() {
        const aside = this.els.mobileDrawer?.querySelector('aside');
        aside?.classList.remove('translate-x-0');
        aside?.classList.add('-translate-x-full');
        setTimeout(() => {
            this.els.mobileDrawer?.classList.add('hidden');
            document.body.style.overflow = '';
        }, 300);
    }

    /**
     * Read state from mobile drawer inputs and navigate.
     * Called by the "Apply Filters" button inside the mobile drawer.
     */
    applyMobileFilters() {
        const newParams = new URLSearchParams();

        // Preserve search query
        const q = this.params.get('q');
        if (q) newParams.set('q', q);

        // Category from mobile radios
        const checkedCat = document.querySelector('[name="catalogCategoryMobile"]:checked');
        const catSlug = checkedCat?.dataset.filterCategory;
        if (catSlug && catSlug !== 'all') newParams.set('category', catSlug);

        // Sort from mobile select
        const mobileSort = document.querySelector('[data-mobile-sort]')?.value;
        if (mobileSort && mobileSort !== 'latest') newParams.set('sort', mobileSort);

        // Price from mobile sliders
        const mobileMinEl = document.querySelector('[data-mobile-price="min"]');
        const mobileMaxEl = document.querySelector('[data-mobile-price="max"]');
        if (mobileMinEl && mobileMaxEl) {
            const mMin = parseInt(mobileMinEl.value);
            const mMax = parseInt(mobileMaxEl.value);
            const absMin = parseInt(mobileMinEl.min);
            const absMax = parseInt(mobileMaxEl.max);
            if (mMin > absMin) newParams.set('price_min', mMin);
            if (mMax < absMax) newParams.set('price_max', mMax);
        }

        // In stock from mobile checkbox
        const mobileInStock = document.querySelector('[data-filter-instock-mobile]');
        if (mobileInStock?.checked) newParams.set('in_stock', '1');

        const qs = newParams.toString();
        window.location.href = window.location.pathname + (qs ? '?' + qs : '');
    }

    updateMobilePriceDisplay() {
        const minEl    = document.querySelector('[data-mobile-price="min"]');
        const maxEl    = document.querySelector('[data-mobile-price="max"]');
        const minDisp  = document.getElementById('priceMinDisplayMobile');
        const maxDisp  = document.getElementById('priceMaxDisplayMobile');
        const hl       = document.getElementById('rangeHighlightMobile');

        if (!minEl || !maxEl) return;

        // Clamp
        if (parseInt(minEl.value) >= parseInt(maxEl.value)) {
            const step = parseInt(minEl.step) || 50;
            if (document.activeElement === minEl) {
                minEl.value = parseInt(maxEl.value) - step;
            } else {
                maxEl.value = parseInt(minEl.value) + step;
            }
        }

        if (minDisp) minDisp.textContent = '৳' + parseInt(minEl.value).toLocaleString('en-BD');
        if (maxDisp) maxDisp.textContent = '৳' + parseInt(maxEl.value).toLocaleString('en-BD');

        // Highlight
        if (hl) {
            const absMin = parseInt(minEl.min);
            const absMax = parseInt(minEl.max);
            const range  = absMax - absMin;
            if (range > 0) {
                hl.style.left  = ((parseInt(minEl.value) - absMin) / range * 100) + '%';
                hl.style.right = (100 - (parseInt(maxEl.value) - absMin) / range * 100) + '%';
            }
        }
    }
}

export default CatalogFilter;

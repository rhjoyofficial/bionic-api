function comboForm(comboId) {
    return {
        comboId,
        loading: comboId !== null,
        saving: false,
        errors: {},

        form: {
            title: '', slug: '', description: '',
            pricing_mode: 'auto', manual_price: '',
            discount_type: '', discount_value: '',
            is_active: true, is_featured: false,
        },

        // Image
        imageFile: null,
        imagePreview: null,

        // Components
        items: [],              // [{variant_id, quantity, product_name, variant_title, unit_price, product_thumbnail}]
        variantSearch: '',
        variantResults: [],
        variantSearching: false,

        // Computed
        autoPrice: 0,

        get finalPrice() {
            const base = this.form.pricing_mode === 'manual'
                ? parseFloat(this.form.manual_price ?? 0) || 0
                : this.autoPrice;

            if (!this.form.discount_type || !this.form.discount_value) return base;

            if (this.form.discount_type === 'percentage') {
                return Math.max(0, Math.round((base - base * this.form.discount_value / 100) * 100) / 100);
            }
            return Math.max(0, base - parseFloat(this.form.discount_value));
        },

        async init() {
            if (this.comboId) await this.loadCombo();
        },

        async loadCombo() {
            this.loading = true;
            try {
                const r = await fetch(`/api/v1/admin/combos/${this.comboId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await r.json();
                const c = data.data;
                if (!c) return;

                this.form = {
                    title:          c.title,
                    slug:           c.slug,
                    description:    c.description ?? '',
                    pricing_mode:   c.pricing_mode,
                    manual_price:   c.manual_price ?? '',
                    discount_type:  c.discount_type ?? '',
                    discount_value: c.discount_value ?? '',
                    is_active:      c.is_active,
                    is_featured:    c.is_featured,
                };

                this.imagePreview = c.image ?? null;

                this.items = (c.items ?? []).map(item => ({
                    variant_id:        item.variant?.id,
                    quantity:          item.quantity,
                    product_name:      item.variant?.product?.name ?? '—',
                    variant_title:     item.variant?.title ?? '—',
                    unit_price:        item.variant?.final_price ?? item.variant?.price ?? 0,
                    product_thumbnail: item.variant?.product?.thumbnail ?? null,
                }));

                this.computeAutoPrice();
            } catch (e) {
                console.error('Failed to load combo', e);
            } finally {
                this.loading = false;
            }
        },

        // ── Image ────────────────────────────────────────────────────────
        onImageChange(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.imageFile = file;
            const reader = new FileReader();
            reader.onload = e => { this.imagePreview = e.target.result; };
            reader.readAsDataURL(file);
        },

        // ── Slug ─────────────────────────────────────────────────────────
        autoSlug() {
            if (this.comboId) return; // don't auto-change slug on edit
            this.form.slug = this.form.title
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-');
        },

        // ── Variant search ────────────────────────────────────────────────
        async searchVariants() {
            if (!this.variantSearch.trim()) { this.variantResults = []; return; }
            this.variantSearching = true;
            try {
                const r = await fetch(`/api/v1/admin/products?q=${encodeURIComponent(this.variantSearch)}&per_page=8`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await r.json();
                this.variantResults = (data.data ?? []).filter(p => (p.variants ?? []).length > 0);
            } catch (e) {
                console.error(e);
            } finally {
                this.variantSearching = false;
            }
        },

        addItem(product, variant) {
            if (this.isAdded(variant.id)) return;
            this.items.push({
                variant_id:        variant.id,
                quantity:          1,
                product_name:      product.name,
                variant_title:     variant.title,
                unit_price:        parseFloat(variant.final_price ?? variant.price ?? 0),
                product_thumbnail: product.thumbnail ?? null,
            });
            this.computeAutoPrice();
        },

        removeItem(idx) {
            this.items.splice(idx, 1);
            this.computeAutoPrice();
        },

        isAdded(variantId) {
            return this.items.some(i => i.variant_id === variantId);
        },

        computeAutoPrice() {
            this.autoPrice = this.items.reduce(
                (sum, item) => sum + (parseFloat(item.unit_price) * parseInt(item.quantity || 1)),
                0
            );
        },

        // ── Submit ────────────────────────────────────────────────────────
        async submit() {
            this.saving = true;
            this.errors = {};
            try {
                const fd = new FormData();

                // Core fields
                fd.append('title',         this.form.title);
                fd.append('slug',          this.form.slug ?? '');
                fd.append('description',   this.form.description ?? '');
                fd.append('pricing_mode',  this.form.pricing_mode);
                fd.append('is_active',     this.form.is_active ? '1' : '0');
                fd.append('is_featured',   this.form.is_featured ? '1' : '0');

                if (this.form.pricing_mode === 'manual' && this.form.manual_price !== '') {
                    fd.append('manual_price', this.form.manual_price);
                }
                if (this.form.discount_type) {
                    fd.append('discount_type',  this.form.discount_type);
                    fd.append('discount_value', this.form.discount_value ?? 0);
                }

                // Items
                this.items.forEach((item, i) => {
                    fd.append(`items[${i}][variant_id]`, item.variant_id);
                    fd.append(`items[${i}][quantity]`,   item.quantity);
                });

                // Image
                if (this.imageFile) fd.append('image', this.imageFile);

                // Method spoofing for update
                const url    = this.comboId ? `/api/v1/admin/combos/${this.comboId}` : '/api/v1/admin/combos';
                const method = 'POST';
                if (this.comboId) fd.append('_method', 'PUT');

                const r = await fetch(url, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: fd,
                });

                const data = await r.json();

                if (r.status === 422) {
                    this.errors = data.errors ?? {};
                    return;
                }
                if (!r.ok) {
                    this.errors._global = data.message ?? 'An error occurred';
                    return;
                }

                // Redirect to index on success
                window.location.href = '/admin/combos';
            } finally {
                this.saving = false;
            }
        },
    };
}

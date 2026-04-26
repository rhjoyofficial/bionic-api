/**
 * landing-checkout.js
 * Vanilla JS engine for landing page checkout sections.
 * No framework dependencies — include with a plain <script> tag.
 *
 * Modes (auto-detected):
 *   product  — single-variant page (radios + one global qty stepper)
 *   sales    — multi-item picker (per-item qty, checkbox selection)
 *
 * Cart mode (sales only):
 *   Add data-lp-cart-mode="1" to [data-lp-checkout].
 *   Qty starts at 0 for all items. Qty controls are always visible.
 *   Incrementing from 0 auto-selects the item.
 *   Decrementing to 0 auto-deselects the item.
 *
 * Data attributes:
 *   [data-lp-checkout]          Root. Carries data-lp-slug, data-lp-cart-mode.
 *   [data-lp-variant]           Variant radio (product mode). Carries data-price, data-tier-prices.
 *   [data-lp-variant-label]     Label wrapper around a variant radio.
 *   [data-lp-checkmark]         Active indicator inside a variant label.
 *   [data-lp-qty-dec]           Decrease-qty button. Value = itemKey (sales) or empty (product).
 *   [data-lp-qty-inc]           Increase-qty button. Value = itemKey (sales) or empty (product).
 *   [data-lp-qty-display]       Qty number span. Value = itemKey (sales) or empty (product).
 *   [data-lp-item-card]         Item card (sales). Carries data-item-key, data-variant-id,
 *                               data-combo-id, data-price, data-tier-prices, data-preselected,
 *                               data-item-label, data-active-class.
 *   [data-lp-item-check]        Checkbox indicator div inside a card. Carries data-active-class.
 *   [data-lp-qty-control]       Qty stepper wrapper inside a card.
 *   [data-lp-zone]              Zone radio input.
 *   [data-lp-zone-label]        Zone label wrapper. Carries data-active-class.
 *   [data-lp-zone-note]         "Select zone" placeholder (hidden after zone is picked).
 *   [data-lp-display="key"]     Live display spans. Keys: subtotal, shipping, total, tier-discount.
 *   [data-lp-display-row="key"] Row toggled by script. Key: tier-discount.
 *   [data-lp-selected-container] Wrapper for selected-items list.
 *   [data-lp-selected-list]     Inner list updated dynamically.
 *   [data-lp-no-items]          Warning shown when no items selected.
 *   [data-lp-error]             Error message div.
 *   [data-lp-submit]            Submit button.
 *   [data-lp-submit-label]      Text inside submit button.
 *   [data-lp-submit-spinner]    Spinner inside submit button.
 *   [data-lp-success-modal]     Success modal (display:none initially).
 */
(function () {
  'use strict';

  // ---------------------------------------------------------------------------
  // Constructor
  // ---------------------------------------------------------------------------

  function LandingCheckout(root) {
    this.root       = root;
    this.slug       = root.dataset.lpSlug;
    this.cartMode   = root.dataset.lpCartMode === '1';
    this.mode       = 'product';
    this.zoneId     = null;
    this.submitting = false;
    this._debounce  = null;

    this.selectedVariantId = null;
    this.variantPrices     = {};
    this.quantities        = {};
    this.selectedItems     = {};
  }

  // ---------------------------------------------------------------------------
  // Init
  // ---------------------------------------------------------------------------

  LandingCheckout.prototype.init = function () {
    var hasCards = this.root.querySelector('[data-lp-item-card]');
    this.mode = hasCards ? 'sales' : 'product';

    if (this.mode === 'product') this._initProductMode();
    else                         this._initSalesMode();

    this._autoSelectZone();
    this._bindEvents();
    this._updateLiveTotal();
    this._debouncedPreview();
  };

  LandingCheckout.prototype._initProductMode = function () {
    var self = this;
    this.root.querySelectorAll('[data-lp-variant]').forEach(function (radio) {
      var id = parseInt(radio.value, 10);
      self.variantPrices[id] = {
        price:      parseFloat(radio.dataset.price || 0),
        tierPrices: JSON.parse(radio.dataset.tierPrices || '[]'),
      };
      self.quantities[id] = 1;
      if (radio.checked) self.selectedVariantId = id;
    });

    if (!this.selectedVariantId) {
      var first = this.root.querySelector('[data-lp-variant]');
      if (first) {
        first.checked = true;
        this.selectedVariantId = parseInt(first.value, 10);
      }
    }
    this._updateVariantHighlights();
  };

  LandingCheckout.prototype._initSalesMode = function () {
    var self     = this;
    var cartMode = this.cartMode;

    this.root.querySelectorAll('[data-lp-item-card]').forEach(function (card) {
      var key       = card.dataset.itemKey;
      var variantId = parseInt(card.dataset.variantId, 10) || null;
      var comboId   = parseInt(card.dataset.comboId,   10) || null;
      var price     = parseFloat(card.dataset.price || 0);
      var tiers     = JSON.parse(card.dataset.tierPrices || '[]');

      if (variantId) self.variantPrices[variantId] = { price: price, tierPrices: tiers };

      if (cartMode) {
        // Qty starts at 0; show qty controls immediately
        if (card.dataset.preselected === '1') {
          self.quantities[key] = 1;
          self.selectedItems[key] = { variantId: variantId, comboId: comboId, qty: 1 };
          self._setCardSelected(card, true);
        } else {
          self.quantities[key] = 0;
          // Ensure qty control is visible
          var ctrl = card.querySelector('[data-lp-qty-control]');
          if (ctrl) ctrl.style.display = '';
        }
      } else {
        self.quantities[key] = 1;
        if (card.dataset.preselected === '1') {
          self.selectedItems[key] = { variantId: variantId, comboId: comboId, qty: 1 };
          self._setCardSelected(card, true);
        }
      }
    });

    this._updateNoItemsWarning();
    this._updateSelectedList();
  };

  LandingCheckout.prototype._autoSelectZone = function () {
    var zones = this.root.querySelectorAll('[data-lp-zone]');
    if (zones.length === 1) {
      zones[0].checked = true;
      this.zoneId = parseInt(zones[0].value, 10);
      this._updateZoneHighlights(zones[0]);
    }
  };

  // ---------------------------------------------------------------------------
  // Event binding
  // ---------------------------------------------------------------------------

  LandingCheckout.prototype._bindEvents = function () {
    var self = this;

    this.root.addEventListener('change', function (e) {
      if (e.target.matches('[data-lp-variant]')) self._onVariantChange(e.target);
      if (e.target.matches('[data-lp-zone]'))    self._onZoneChange(e.target);
    });

    this.root.addEventListener('click', function (e) {
      var inc = e.target.closest('[data-lp-qty-inc]');
      var dec = e.target.closest('[data-lp-qty-dec]');
      if (inc) { e.stopPropagation(); self._changeQty(inc.dataset.lpQtyInc || '', +1); return; }
      if (dec) { e.stopPropagation(); self._changeQty(dec.dataset.lpQtyDec || '', -1); return; }

      var card = e.target.closest('[data-lp-item-card]');
      if (card) { self._toggleItem(card); return; }

      if (e.target.closest('[data-lp-submit]')) self._placeOrder();
    });
  };

  // ---------------------------------------------------------------------------
  // Variant selection (product mode)
  // ---------------------------------------------------------------------------

  LandingCheckout.prototype._onVariantChange = function (radio) {
    this.selectedVariantId = parseInt(radio.value, 10);
    this._updateVariantHighlights();
    this._updateLiveTotal();
    this._debouncedPreview();
  };

  LandingCheckout.prototype._updateVariantHighlights = function () {
    var self = this;
    this.root.querySelectorAll('[data-lp-variant-label]').forEach(function (label) {
      var radio    = label.querySelector('[data-lp-variant]');
      if (!radio) return;
      var isActive = parseInt(radio.value, 10) === self.selectedVariantId;

      var activeClasses   = (label.dataset.activeClass || 'border-pink-500 bg-pink-50').split(' ');
      var inactiveClasses = 'border-gray-100 bg-white'.split(' ');

      activeClasses.forEach(function (c)   { label.classList.toggle(c, isActive);  });
      inactiveClasses.forEach(function (c) { label.classList.toggle(c, !isActive); });

      var mark = label.querySelector('[data-lp-checkmark]');
      if (mark) mark.style.display = isActive ? 'flex' : 'none';
    });
  };

  // ---------------------------------------------------------------------------
  // Item card toggle (sales mode)
  // ---------------------------------------------------------------------------

  LandingCheckout.prototype._toggleItem = function (card) {
    var key       = card.dataset.itemKey;
    var variantId = parseInt(card.dataset.variantId, 10) || null;
    var comboId   = parseInt(card.dataset.comboId,   10) || null;

    if (this.selectedItems[key]) {
      // Deselect
      delete this.selectedItems[key];
      if (this.cartMode) {
        this.quantities[key] = 0;
        var d = this.root.querySelector('[data-lp-qty-display="' + key + '"]');
        if (d) d.textContent = '0';
      }
      this._setCardSelected(card, false);
    } else {
      // Select — restore saved qty (min 1)
      var qty = this.quantities[key] || 0;
      if (qty === 0) qty = 1;
      this.quantities[key] = qty;
      this.selectedItems[key] = { variantId: variantId, comboId: comboId, qty: qty };
      this._setCardSelected(card, true);
      var d2 = this.root.querySelector('[data-lp-qty-display="' + key + '"]');
      if (d2) d2.textContent = qty;
    }

    this._updateNoItemsWarning();
    this._updateSelectedList();
    this._updateLiveTotal();
    this._debouncedPreview();
  };

  LandingCheckout.prototype._setCardSelected = function (card, selected) {
    var activeClasses   = (card.dataset.activeClass || 'border-red-400 ring-2 ring-red-100').split(' ');
    var inactiveClasses = 'border-gray-100'.split(' ');

    activeClasses.forEach(function (c)   { card.classList.toggle(c, selected);  });
    inactiveClasses.forEach(function (c) { card.classList.toggle(c, !selected); });

    var check = card.querySelector('[data-lp-item-check]');
    if (check) {
      var checkActive   = (check.dataset.activeClass || 'bg-red-600 border-red-600').split(' ');
      var checkInactive = 'border-gray-300 bg-white'.split(' ');
      checkActive.forEach(function (c)   { check.classList.toggle(c, selected);  });
      checkInactive.forEach(function (c) { check.classList.toggle(c, !selected); });
      var svg = check.querySelector('svg');
      if (svg) svg.style.display = selected ? '' : 'none';
    }

    // In cart mode, qty control is always visible — skip toggling visibility
    if (!this.cartMode) {
      var qtyCtrl = card.querySelector('[data-lp-qty-control]');
      if (qtyCtrl) qtyCtrl.style.display = selected ? '' : 'none';
    }
  };

  // ---------------------------------------------------------------------------
  // Qty change
  // ---------------------------------------------------------------------------

  LandingCheckout.prototype._changeQty = function (key, delta) {
    if (this.mode === 'product') {
      var id = this.selectedVariantId;
      if (!id) return;
      this.quantities[id] = Math.max(1, (this.quantities[id] || 1) + delta);
      var display = this.root.querySelector('[data-lp-qty-display]');
      if (display) display.textContent = this.quantities[id];

    } else if (this.cartMode) {
      var curQty = this.quantities[key] || 0;
      var newQty = Math.max(0, curQty + delta);
      this.quantities[key] = newQty;

      var dispEl = this.root.querySelector('[data-lp-qty-display="' + key + '"]');
      if (dispEl) dispEl.textContent = newQty;

      var card = this.root.querySelector('[data-lp-item-card][data-item-key="' + key + '"]');

      if (newQty === 0 && this.selectedItems[key]) {
        // Auto-deselect when qty reaches 0
        delete this.selectedItems[key];
        if (card) this._setCardSelected(card, false);
      } else if (newQty > 0 && !this.selectedItems[key]) {
        // Auto-select when qty goes from 0 to 1
        var vId = card ? (parseInt(card.dataset.variantId, 10) || null) : null;
        var cId = card ? (parseInt(card.dataset.comboId,   10) || null) : null;
        this.selectedItems[key] = { variantId: vId, comboId: cId, qty: newQty };
        if (card) this._setCardSelected(card, true);
      } else if (this.selectedItems[key]) {
        this.selectedItems[key].qty = newQty;
      }

      this._updateNoItemsWarning();
      this._updateSelectedList();

    } else {
      // Sales mode, non-cart: only update if item is selected
      if (!this.selectedItems[key]) return;
      var nq = Math.max(1, (this.quantities[key] || 1) + delta);
      this.quantities[key] = nq;
      this.selectedItems[key].qty = nq;
      var d = this.root.querySelector('[data-lp-qty-display="' + key + '"]');
      if (d) d.textContent = nq;
      this._updateSelectedList();
    }

    this._updateLiveTotal();
    this._debouncedPreview();
  };

  // ---------------------------------------------------------------------------
  // Zone selection
  // ---------------------------------------------------------------------------

  LandingCheckout.prototype._onZoneChange = function (radio) {
    this.zoneId = parseInt(radio.value, 10) || null;
    this._updateZoneHighlights(radio);
    this._debouncedPreview();
  };

  LandingCheckout.prototype._updateZoneHighlights = function (activeRadio) {
    this.root.querySelectorAll('[data-lp-zone-label]').forEach(function (label) {
      var radio    = label.querySelector('[data-lp-zone]');
      if (!radio) return;
      var isActive = radio === activeRadio;

      var activeClasses   = (label.dataset.activeClass || 'border-pink-500 bg-pink-50').split(' ');
      var inactiveClasses = 'border-gray-200 bg-white'.split(' ');

      activeClasses.forEach(function (c)   { label.classList.toggle(c, isActive);  });
      inactiveClasses.forEach(function (c) { label.classList.toggle(c, !isActive); });
    });
  };

  // ---------------------------------------------------------------------------
  // Tier-aware effective unit price (client-side)
  // ---------------------------------------------------------------------------

  LandingCheckout.prototype._effectivePrice = function (variantId, qty) {
    var data = this.variantPrices[variantId];
    if (!data) return 0;
    if (!data.tierPrices || !data.tierPrices.length) return data.price;
    var sorted = data.tierPrices.slice().sort(function (a, b) { return b.min_qty - a.min_qty; });
    for (var i = 0; i < sorted.length; i++) {
      if (qty >= sorted[i].min_qty) return sorted[i].price;
    }
    return data.price;
  };

  // ---------------------------------------------------------------------------
  // Live client-side total (immediate, no API call)
  // ---------------------------------------------------------------------------

  LandingCheckout.prototype._updateLiveTotal = function () {
    var self     = this;
    var subtotal = 0;

    if (this.mode === 'product') {
      var id = this.selectedVariantId;
      if (id) {
        var qty = this.quantities[id] || 1;
        subtotal = this._effectivePrice(id, qty) * qty;
      }
    } else {
      Object.keys(this.selectedItems).forEach(function (key) {
        var s = self.selectedItems[key];
        if (s.variantId) {
          subtotal += self._effectivePrice(s.variantId, s.qty) * s.qty;
        } else {
          // Combo or unknown: use raw card price
          var card = self.root.querySelector('[data-lp-item-card][data-item-key="' + key + '"]');
          var p    = card ? parseFloat(card.dataset.price || 0) : 0;
          subtotal += p * s.qty;
        }
      });
    }

    if (subtotal > 0) {
      this._setText('subtotal', '৳' + subtotal.toFixed(0));
      // Show subtotal as total when zone not yet chosen
      if (!this.zoneId) this._setText('total', '৳' + subtotal.toFixed(0));
    } else {
      this._setText('subtotal', '—');
      if (!this.zoneId) this._setText('total', '—');
    }
  };

  // ---------------------------------------------------------------------------
  // Build items payload for API
  // ---------------------------------------------------------------------------

  LandingCheckout.prototype._buildItems = function () {
    if (this.mode === 'product') {
      if (!this.selectedVariantId) return [];
      return [{ variant_id: this.selectedVariantId, quantity: this.quantities[this.selectedVariantId] || 1 }];
    }

    return Object.keys(this.selectedItems).map(function (key) {
      var s    = this.selectedItems[key];
      var item = { quantity: s.qty || 1 };
      if (s.variantId) item.variant_id = s.variantId;
      if (s.comboId)   item.combo_id   = s.comboId;
      return item;
    }, this);
  };

  // ---------------------------------------------------------------------------
  // Preview API (debounced)
  // ---------------------------------------------------------------------------

  LandingCheckout.prototype._debouncedPreview = function () {
    var self = this;
    clearTimeout(this._debounce);
    this._debounce = setTimeout(function () { self._fetchPreview(); }, 400);
  };

  LandingCheckout.prototype._fetchPreview = function () {
    var self  = this;
    var items = this._buildItems();

    if (!items.length) {
      this._updateLiveTotal();
      return;
    }

    var body = { items: items };
    if (this.zoneId) body.zone_id = this.zoneId;

    fetch('/api/v1/landing/' + this.slug + '/preview', {
      method:  'POST',
      headers: this._headers(),
      body:    JSON.stringify(body),
    })
      .then(function (r) { return r.json(); })
      .then(function (json) {
        if (json.success && json.data) self._renderSummary(json.data);
        else self._updateLiveTotal();
      })
      .catch(function () { self._updateLiveTotal(); });
  };

  LandingCheckout.prototype._renderSummary = function (data) {
    var sub  = parseFloat(data.subtotal      || 0);
    var ship = parseFloat(data.shipping_cost || 0);
    var tot  = parseFloat(data.grand_total   || 0);
    var tier = parseFloat(data.tier_discount || 0);

    this._setText('subtotal', sub > 0 ? '৳' + sub.toFixed(0) : '—');
    this._setText('tier-discount', '-৳' + tier.toFixed(0));

    var note   = this.root.querySelector('[data-lp-zone-note]');
    var shipEl = this.root.querySelector('[data-lp-display="shipping"]');

    if (this.zoneId) {
      if (note)   note.style.display   = 'none';
      if (shipEl) shipEl.style.display = '';
      this._setText('shipping', ship > 0 ? '৳' + ship.toFixed(0) : 'ফ্রি!');
      this._setText('total', tot > 0 ? '৳' + tot.toFixed(0) : '—');
    } else {
      if (note)   note.style.display   = '';
      if (shipEl) shipEl.style.display = 'none';
      var clientTotal = sub - tier;
      this._setText('total', clientTotal > 0 ? '৳' + clientTotal.toFixed(0) : '—');
    }

    var tierRow = this.root.querySelector('[data-lp-display-row="tier-discount"]');
    if (tierRow) tierRow.style.display = tier > 0 ? '' : 'none';
  };

  LandingCheckout.prototype._setText = function (key, value) {
    var el = this.root.querySelector('[data-lp-display="' + key + '"]');
    if (el) el.textContent = value;
  };

  // ---------------------------------------------------------------------------
  // Selected items list (sales mode)
  // ---------------------------------------------------------------------------

  LandingCheckout.prototype._updateSelectedList = function () {
    var list = this.root.querySelector('[data-lp-selected-list]');
    if (!list) return;

    var container = this.root.querySelector('[data-lp-selected-container]');
    var count     = Object.keys(this.selectedItems).length;
    if (container) container.style.display = count > 0 ? '' : 'none';

    list.innerHTML = '';
    var self = this;

    Object.keys(this.selectedItems).forEach(function (key) {
      var s    = self.selectedItems[key];
      var card = self.root.querySelector('[data-lp-item-card][data-item-key="' + key + '"]');
      var lbl  = card ? (card.dataset.itemLabel || key) : key;

      var unitPrice = s.variantId
        ? self._effectivePrice(s.variantId, s.qty)
        : (card ? parseFloat(card.dataset.price || 0) : 0);
      var lineTotal = unitPrice * s.qty;

      var row = document.createElement('div');
      row.className = 'flex justify-between items-center text-sm border-b border-gray-100 pb-1.5 last:border-0 last:pb-0';
      row.innerHTML = '<span class="text-gray-700 truncate max-w-[60%]">' + lbl + ' <span class="text-gray-400">×' + s.qty + '</span></span>'
                    + '<span class="font-bold text-gray-900 shrink-0">৳' + lineTotal.toFixed(0) + '</span>';
      list.appendChild(row);
    });
  };

  LandingCheckout.prototype._updateNoItemsWarning = function () {
    var warn = this.root.querySelector('[data-lp-no-items]');
    if (warn) warn.style.display = Object.keys(this.selectedItems).length === 0 ? '' : 'none';
  };

  // ---------------------------------------------------------------------------
  // Order submission
  // ---------------------------------------------------------------------------

  LandingCheckout.prototype._placeOrder = function () {
    if (this.submitting) return;

    var items   = this._buildItems();
    var name    = this._val('[name="customer_name"]');
    var phone   = this._val('[name="customer_phone"]');
    var address = this._val('[name="address_line"]');
    var city    = this._val('[name="city"]') || 'N/A';

    if (!items.length)  return this._showError('পণ্য নির্বাচন করুন');
    if (!this.zoneId)   return this._showError('ডেলিভারি এলাকা নির্বাচন করুন');
    if (!name)          return this._showError('নাম লিখুন');
    if (!phone)         return this._showError('মোবাইল নম্বর লিখুন');
    if (!address)       return this._showError('ঠিকানা লিখুন');

    this.submitting = true;
    this._setLoading(true);
    this._showError('');

    var self = this;
    fetch('/api/v1/landing/' + this.slug + '/checkout', {
      method:  'POST',
      headers: this._headers(),
      body: JSON.stringify({
        customer_name:  name,
        customer_phone: phone,
        address_line:   address,
        city:           city,
        zone_id:        this.zoneId,
        payment_method: 'cod',
        items:          items,
      }),
    })
      .then(function (r) { return r.json(); })
      .then(function (json) {
        if (json.success) {
          if (json.data && json.data.redirect_url) {
            window.location.href = json.data.redirect_url;
          } else {
            self._showSuccessModal();
          }
          return;
        }
        self._showError(json.message || 'কিছু একটা সমস্যা হয়েছে। আবার চেষ্টা করুন।');
      })
      .catch(function () {
        self._showError('নেটওয়ার্ক সমস্যা। ইন্টারনেট সংযোগ চেক করে আবার চেষ্টা করুন।');
      })
      .finally(function () {
        self.submitting = false;
        self._setLoading(false);
      });
  };

  // ---------------------------------------------------------------------------
  // UI helpers
  // ---------------------------------------------------------------------------

  LandingCheckout.prototype._setLoading = function (on) {
    var btn     = this.root.querySelector('[data-lp-submit]');
    var label   = this.root.querySelector('[data-lp-submit-label]');
    var spinner = this.root.querySelector('[data-lp-submit-spinner]');
    if (btn)     btn.disabled          = on;
    if (label)   label.style.display   = on ? 'none' : '';
    if (spinner) spinner.style.display = on ? '' : 'none';
  };

  LandingCheckout.prototype._showError = function (msg) {
    var el = this.root.querySelector('[data-lp-error]');
    if (!el) return;
    el.textContent   = msg;
    el.style.display = msg ? '' : 'none';
  };

  LandingCheckout.prototype._showSuccessModal = function () {
    var modal = this.root.querySelector('[data-lp-success-modal]');
    if (!modal) return;
    modal.style.opacity    = '0';
    modal.style.display    = 'flex';
    modal.style.transition = 'opacity 0.2s ease';
    requestAnimationFrame(function () { modal.style.opacity = '1'; });
  };

  LandingCheckout.prototype._val = function (sel) {
    var el = this.root.querySelector(sel);
    return el ? el.value.trim() : '';
  };

  LandingCheckout.prototype._headers = function () {
    var csrf = document.querySelector('meta[name="csrf-token"]');
    return {
      'Content-Type': 'application/json',
      'Accept':       'application/json',
      'X-CSRF-TOKEN': csrf ? csrf.content : '',
    };
  };

  // ---------------------------------------------------------------------------
  // Auto-init
  // ---------------------------------------------------------------------------

  document.addEventListener('DOMContentLoaded', function () {
    var root = document.querySelector('[data-lp-checkout]');
    if (!root) return;
    var checkout = new LandingCheckout(root);
    checkout.init();
  });

})();

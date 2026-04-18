# Admin Panel Deep Bug Audit (Laravel Production Readiness)

Scope covered:

- Admin Dashboard
- Admin Orders module
- Admin Products module
- Related services/listeners directly impacting those modules

---

### [Admin Order Creation Accepts Invalid Payment Method `online`]

- Location: `app/Domains/Order/Controllers/AdminOrderController.php` (`store`), `resources/views/admin/orders/create.blade.php` (payment selector), `database/migrations/2026_02_27_153902_create_orders_table.php` (orders schema)

- Type: Bug

- Severity: Critical

- Problem:
  Admin order creation validates and submits `payment_method=online`, but the database only allows `cod` or `sslcommerz`.

- Impact:
  Creating an order with “Online Payment” can fail at insert time with SQL enum errors, breaking a core admin money flow.

- Suggested Fix:
  From admin panel only can create COD order, or Paid pertically.

---

### [Order Status Change to Confirmed Can Trigger Runtime Fatal Error]

- Location: `app/Listeners/CreateCourierShipmentListener.php` (`handle`), `app/Domains/Courier/Services/ShipmentService.php` (available methods), `bootstrap/app.php` (event discovery)

- Type: Bug

- Severity: Critical

- Problem:
  Listener calls `$this->shipmentService->create(...)`, but the service exposes `createShipment(...)` (no `create` method exists). This listener is auto-discovered and executed on status events.

- Impact:
  Confirming orders can throw runtime errors and interrupt status-change flow in admin orders management.

- Suggested Fix:
  On click confirm button order status will change, only then can Courier & Shipments part work. Courier part will work when order is confirmed.

---

### [Product Update Allows Cross-Product Variant IDs, Enabling Accidental Variant Deletion]

- Location: `app/Domains/Product/Requests/UpdateProductRequest.php` (`rules`), `app/Domains/Product/Services/ProductService.php` (`update`)

- Type: Bug / Data inconsistency

- Severity: Critical

- Problem:
  Validation only checks `variants.*.id` exists globally, not that it belongs to the product being edited. In update logic, foreign IDs are added to keep-list even when no row is updated for current product; then `whereNotIn(...)->delete()` can remove real variants from the product.

- Impact:
  Admin product edits can silently delete valid variants, corrupt catalog and pricing data.

- Suggested Fix:
  Enforce per-product ownership validation for variant IDs and only add IDs to retain-list when update actually affects this product.
- I want: When I update product, I want to update variant price, stock, weight, images, and other fields. But I don't want to update variant id. Because if I update variant id, it will create new variant.

---

### [SKU Uniqueness Not Validated on Product Variant Updates]

- Location: `app/Domains/Product/Requests/UpdateProductRequest.php` (`rules`), `database/migrations/2026_02_27_153731_create_product_variants_table.php` (`sku` unique index)

- Type: Bug

- Severity: High

- Problem:
  Update validation for `variants.*.sku` has no uniqueness rule, while DB enforces unique SKU.

- Impact:
  Duplicate SKU edits pass request validation but fail at DB layer, returning server errors in admin product save flow.

- Suggested Fix:
  Add unique validation with ignore-by-variant-ID semantics for update payloads.

---

### [Tier Price Validation Errors Are Returned as 500 Instead of 422]

- Location: `app/Domains/Product/Controllers/ProductTierPriceController.php` (`store`, `destroy`, `handleError`)

- Type: Inconsistency / Bug

- Severity: Medium

- Problem:
  Controller catches all exceptions (including request validation failures) and always returns HTTP 500.

- Impact:
  Admin UI receives incorrect error class for user-input mistakes, causing misleading failure handling and unstable UX.

- Suggested Fix:
  Let validation exceptions bubble (422), or branch error handling by exception type.

---

### [Dashboard Revenue KPIs Sum All Orders Regardless of Payment/Final Status]

- Location: `app/Domains/Admin/Services/DashboardStatsService.php` (`kpiCards`)

- Type: Inconsistency (data accuracy)

- Severity: High

- Problem:
  Revenue metrics sum `grand_total` for all orders by date, without filtering out unpaid/failed/cancelled states.

- Impact:
  Dashboard can overstate revenue and mislead business decisions and operational reporting.

- Suggested Fix:
  Restrict revenue metrics to business-valid states (at minimum `payment_status=paid`, and optionally exclude cancelled/returned based on accounting policy).

---

### [Admin Order Payment Method Choices Are Inconsistent Across Modules]

- Location: `resources/views/admin/orders/create.blade.php` (uses `online`), `resources/views/admin/orders/index.blade.php` (filters `sslcommerz`), `app/Domains/Order/Controllers/AdminOrderController.php` (`store` validation)

- Type: UI ↔ backend mismatch

- Severity: High

- Problem:
  Admin create form submits `online`, while order listing/filtering and schema use `sslcommerz`.

- Impact:
  Operational reporting/filtering becomes inconsistent and order creation can break for non-COD cases.

- Suggested Fix:
  Standardize one canonical value across create form, validation, persistence, and filtering.

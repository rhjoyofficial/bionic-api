# Bionic — Full Checkout Flow Audit Report

**Date:** 2026-04-08  
**Branch:** `rhjoyOfficial`  
**Auditor:** Claude Code (claude-sonnet-4-6)  
**Scope:** Browse Product → Add to Cart → Cart → Coupon → Login/Register → Cart Merge → Checkout → Shipping → Payment → Order → Success → Notification

---

## Summary Table

| Step | File(s) | Status | Critical Issues |
|------|---------|--------|-----------------|
| 1. Browse Products | `HomeController`, `ProductPageController` | ⚠️ Warning | No limit on category products query |
| 2. Product Detail | `product.blade.php` | ⚠️ Warning | Buy-now race condition (async add + sync redirect) |
| 3. Add to Cart | `AddToCartBinder.js`, `CartManager.js` | ⚠️ Warning | `CartManager.flash()` does not exist — dead call |
| 4. Cart Page | `PublicCartController`, `cart.blade.php`, `CartPageRenderer.js` | ⚠️ Warning | `flash()` wrong argument order in controller |
| 5. Coupon (Cart) | `CouponValidationService` | ⚠️ Warning | `lockForUpdate()` called outside a DB transaction |
| 6. Login / Register | `WebAuthController`, `AuthManager.js` | ⚠️ Warning | Merge failure kills login; no `intended()` redirect; remember-me dead |
| 7. Cart Merge | `CartMergeService` | ✅ Pass | Minor: no merge-specific logging |
| 8. Checkout | `checkout.blade.php`, `CheckoutManager.js`, `CheckoutController` | 🔴 Bug | `zonesModule` ReferenceError blocks order placement |
| 9. Shipping | `PublicShippingZoneController`, `ShippingCalculator` | ⚠️ Warning | Zones ordered newest-first; no caching |
| 10. Payment | `CheckoutController` | ⚠️ Warning | SSLCommerz selectable in UI but creates broken order |
| 11. Order | `OrderService` | ⚠️ Warning | `isSameCheckoutAttempt()` comparing null field; combo N+1 |
| 12. Order Success | `order-success.blade.php` | ⚠️ Warning | Combo items show blank variant; dead sessionStorage cleanup key |
| 13. Notifications | Listeners, Jobs | ⚠️ Warning | `Mail::raw()` placeholder; no failed-job handling |

---

## Step 1 — Browse Products

**Files:** `app/Domains/Store/Controllers/HomeController.php`, `ProductPageController.php`

### Issues

#### ⚠️ No limit on category products query
`categoryProductsRaw` in `HomeController` loads all products for a category with no `->limit()`. On a large catalog this is an unbounded DB read on every homepage load.

**Suggestion:** Add `->limit(20)` (or a configurable value) to the category products query. Use pagination or cursor loading for the frontend if more products are needed.

#### ⚠️ Combos limited but products are not
Combos are limited to 12 but the category products section has no limit. This inconsistency means the homepage query time grows linearly with catalog size.

---

## Step 2 — Product Detail Page

**Files:** `resources/views/store/product.blade.php`

### Issues

#### ⚠️ Buy-now race condition
`buyNowBtn` calls `window.Cart?.add(variantId, qty)` (async) and then immediately redirects to `/checkout` without waiting for the cart API response to complete.

```js
// Current behaviour — redirect is NOT awaiting the add() call
window.Cart?.add(variantId, qty);
window.location.href = '/checkout';
```

If the `add()` API call is slow or fails, the user lands on checkout with an empty cart and is redirected back to `/cart`.

**Suggestion:** `await window.Cart?.add(variantId, qty)` before the redirect, or chain `.then(() => window.location.href = '/checkout')`.

#### ⚠️ Inline `<script>` in Blade `@push`
Product variant logic is in an inline `@push('scripts')` block. This mixes server-rendered data with JS logic, making it hard to unit test or tree-shake.

**Suggestion:** Pass variant data as a `data-` attribute on the product container and move all logic to a dedicated `ProductPageManager.js`.

---

## Step 3 — Add to Cart

**Files:** `resources/js/cart/AddToCartBinder.js`, `resources/js/cart/CartManager.js`

### Issues

#### 🔴 `CartManager.flash()` does not exist
`CartManager.clear()` calls `this.flash(...)` which is not a method on `CartManager`. This throws an uncaught TypeError whenever `clear()` is called.

```js
// CartManager.js — clear() method
this.flash('Cart cleared', 'success'); // flash() is not defined on this class
```

**Suggestion:** Replace with `window.flash?.('Cart cleared', 'success')`.

#### ℹ️ `AddToCartBinder.js` hardcodes `quantity: 1`
The global delegated click handler always sends `quantity: 1`. The product page overrides this by calling `window.Cart?.add(variantId, qty)` directly, so this only affects catalog/listing-page add-to-cart buttons.

**Suggestion:** Read a `data-quantity` attribute from the button if present, with a fallback of `1`.

---

## Step 4 — Cart Page

**Files:** `app/Domains/Cart/Controllers/PublicCartController.php`, `resources/views/store/cart.blade.php`, `resources/js/cart/CartPageRenderer.js`

### Issues

#### 🔴 Wrong argument order in `flash()` call
`PublicCartController::view()` calls:

```php
flash('info', 'Prices have been updated to reflect current values.');
```

The `flash()` helper signature is `flash(message, type)`. The arguments are reversed — the flash will render the string `'info'` as the message and `'Prices...'` as the (invalid) type.

**Suggestion:**
```php
flash('Prices have been updated to reflect current values.', 'info');
```

#### ℹ️ Coupon stored in `sessionStorage` as `bionic_coupon`
Coupon is persisted in sessionStorage at the cart page and carried to checkout. This is the correct design. No issue — just noting the data flow for reference.

---

## Step 5 — Coupon Validation (Cart Page)

**File:** `app/Domains/Coupon/Services/CouponValidationService.php`

### Issues

#### ⚠️ `lockForUpdate()` called outside a DB transaction
`CouponValidationService::validate()` uses `->lockForUpdate()` to prevent race conditions on coupon usage counts. However, the cart-page coupon validation API endpoint (`POST /api/v1/coupon/validate`) is **not wrapped in a DB transaction**. `lockForUpdate()` has no effect outside a transaction and the query runs without a lock.

This only affects the cart-page pre-validation endpoint — the actual order creation path in `OrderService` correctly validates inside a transaction with `withLock: true`. The risk is that the cart-page validation shows "coupon valid" but by the time the order is placed the coupon limit has been hit.

**Suggestion:** Either remove `lockForUpdate()` from the standalone validation (it's serving a read-only check) and only lock during order creation, or wrap the validation endpoint in `DB::transaction()`.

#### ℹ️ Auth not enforced at coupon validation endpoint (by design)
Coupon auth enforcement is deferred to order creation (`OrderService::create()` throws if `coupon_code && !$user`). The cart-page validation allows guests to *see* discount amounts. This is an intentional UX decision that was confirmed during the auth refactor.

---

## Step 6 — Login / Register

**Files:** `app/Domains/Auth/Controllers/WebAuthController.php`, `resources/js/auth/AuthManager.js`, `resources/views/auth/login.blade.php`

### What Works
- Login: `AuthService::authenticate()` → `session()->regenerate()` → cart merge → `X-CSRF-TOKEN` response header ✓
- Register: DB transaction → user create → role assign → Sanctum token → cart merge → commit → `Auth::login()` → session regenerate ✓
- JS: reads `bionic_cart_token` from cookie, sends as `session_token` ✓
- `_refreshCsrfMeta()` updates `<meta>` CSRF tag from response header before redirect ✓
- Logout: `Auth::guard('web')->logout()` → `session()->invalidate()` → `regenerateToken()` ✓

### Issues

#### 🔴 Cart merge failure kills a valid login
`mergeService->merge()` is called directly inside `WebAuthController::login()` without its own try/catch. If merge throws (e.g., a guest cart item is now out of stock), the outer catch returns a 500 error. The user's credentials are valid but they cannot log in.

**Suggestion:**
```php
// In login() — wrap merge in its own try/catch
try {
    if ($request->filled('session_token')) {
        $this->mergeService->merge($request->session_token, $result['user']->id);
    }
} catch (Exception $mergeEx) {
    Log::warning('Cart merge failed on login: ' . $mergeEx->getMessage(), [
        'user_id'       => $result['user']->id,
        'session_token' => $request->session_token,
    ]);
    // Continue — login succeeds, guest cart is abandoned
}
```

Apply the same pattern to `register()`.

#### ⚠️ No `intended()` redirect after login
After successful login, `AuthManager.js` always redirects to `/`. If the user was at `/checkout` and clicked "Login", they lose their checkout context.

**Suggestion:** Before redirecting to `/login`, store the current URL: `session()->put('url.intended', url()->current())`. After login, redirect to `redirect()->intended('/')`. Pass this as a `redirect` query param or use Laravel's built-in intended URL mechanism.

#### ⚠️ "Remember me" checkbox is dead UI
`login.blade.php` renders a "Remember me" checkbox (`name="remember"`), but `AuthManager.js` never reads `.checked` and never includes `remember: true` in the POST body. `Auth::login()` in `AuthService` would also need the flag passed.

**Suggestion:** Either wire it up:
```js
remember: form.querySelector('[name="remember"]')?.checked ?? false,
```
Or remove the checkbox from the view until it is fully implemented.

---

## Step 7 — Cart Merge

**File:** `app/Domains/Cart/Services/CartMergeService.php`

### What Works
- Entire merge in `DB::transaction()` ✓
- Both carts' reserved stock released before merge (prevents double-reservation) ✓
- Stock validated against combined quantity before updating ✓
- Price snapshot refreshed with tier pricing on merge ✓
- Guest cart deleted after successful merge ✓
- `syncCartPrices()` called at end ✓

### Issues

#### ⚠️ Stale `bionic_cart_token` cookie persists after merge
`AuthManager.js` clears `localStorage.removeItem("bionic_cart_token")` after login/register, but does **not** clear the cookie. `bionic_cart_token` is a JS-readable cookie (httpOnly=false). On the next page load, `CartManager.ensureToken()` reads the cookie first, picks up the now-deleted session token, and `CartController` finds no cart for it — creating a new empty guest cart.

**Suggestion — option A (preferred):** Expire the cookie in the login/register response:
```php
// In WebAuthController::login() and register()
cookie()->queue(cookie()->forget('bionic_cart_token'));
```

**Suggestion — option B:** Clear the cookie in JS after merge:
```js
document.cookie = 'bionic_cart_token=; Max-Age=0; path=/; SameSite=Lax';
```

#### ⚠️ No merge-specific logging
When merge throws, the generic login/register catch logs the error but has no context about which session token or user was involved.

**Suggestion:** Add structured logging at the start of `merge()`:
```php
Log::info('CartMerge: started', ['session_token' => $sessionToken, 'user_id' => $userId]);
```

#### ℹ️ Combo price silently refreshed on merge
When a combo item is merged, `unit_price_snapshot` is updated to the current `$item->combo->final_price`. If the combo had a sale price when the item was added that has since expired, the updated price is reflected silently. This is intentional (price refresh on merge) but could confuse users expecting to pay the price they saw.

**Suggestion:** Consider showing a "some prices have been updated" toast after merge, similar to the existing cart-page price sync message.

---

## Step 8 — Checkout

**Files:** `resources/views/store/checkout.blade.php`, `resources/js/managers/CheckoutManager.js`, `app/Domains/Order/Controllers/CheckoutController.php`

### What Works
- `waitForCart()` properly awaits `cart:updated` event with 3s timeout fallback ✓
- Redirects to `/cart` if no items ✓
- `loadCarriedCoupon()` reads `bionic_coupon` from sessionStorage and pre-fills ✓
- `fetchPreview()` called on init, zone change, and coupon change ✓
- `checkout_token: window.Cart.token` sent for idempotency ✓
- Controller resolves auth user at boundary, passes to service ✓
- `resolveCheckoutCart()` correctly prioritises user cart for auth users ✓

### Issues

#### 🔴 `zonesModule` ReferenceError crashes order placement
In `CheckoutManager._validateForm()`, when no shipping zone is selected:

```js
// Lines 482–488 in CheckoutManager.js
zonesModule?.scrollIntoView({ behavior: 'smooth', block: 'center' });
zonesModule.classList.add('ring-2', 'ring-red-500'); // ReferenceError: zonesModule is not defined
```

`zonesModule` is not declared in this scope — it should be `this.zonesModule`. ES modules run in strict mode. This throws an uncaught `ReferenceError`, the `_validateForm()` call crashes, and the user **cannot place any order** if they skip zone selection.

**Suggestion:**
```js
this.zonesModule?.scrollIntoView({ behavior: 'smooth', block: 'center' });
this.zonesModule?.classList.add('ring-2', 'ring-red-500');
setTimeout(() => {
    this.zonesModule?.classList.remove('ring-2', 'ring-red-500');
}, 2000);
```

#### ⚠️ No auto-fill for authenticated users
Auth users' name, phone, and email are stored in their profile, but checkout form fields (`co_name`, `co_phone`, `co_email`) are never pre-populated. Users must retype their details on every order.

**Suggestion:** In the Blade view, inject user data for authenticated users:
```blade
value="{{ auth()->user()?->name ?? '' }}"
value="{{ auth()->user()?->phone ?? '' }}"
value="{{ auth()->user()?->email ?? '' }}"
```

#### ⚠️ SSLCommerz payment option should be disabled
The SSLCommerz radio button is functional (not `disabled`). A user who selects it will have an order created in the DB but then be redirected to the order-failed page. The order is in a permanent `unpaid` state.

**Suggestion:** Add `disabled` attribute to the SSLCommerz radio input until the gateway is implemented:
```blade
<input type="radio" name="payment_method" value="sslcommerz" disabled ...>
```

Or add client-side validation in `CheckoutManager._validateForm()` to reject this method with a message.

#### ℹ️ `fetchPreview()` uses frontend cart state, not server cart
The preview sends `window.Cart.state.items` (client-side). If cart state is stale (price changed server-side), the preview total shown to the user differs from the amount actually charged. The final order creation always re-calculates server-side so the order itself is correct, but the UI can mislead.

**Suggestion:** Display a "Final total confirmed at order placement" note near the total, or refresh the cart from the server when the checkout page loads.

---

## Step 9 — Shipping

**Files:** `app/Domains/Shipping/Controllers/PublicShippingZoneController.php`, `app/Domains/Shipping/Services/ShippingCalculator.php`

### What Works
- `/api/v1/shipping-zones` returns only active zones, no auth required ✓
- `ShippingCalculator::calculate()` applies free-shipping threshold correctly ✓
- JS zone selection triggers `fetchPreview()` re-calculation ✓

### Issues

#### ⚠️ Zones ordered by `created_at DESC` (newest first)
`->latest()` means the most recently created zone appears first in the list. Customers see zones in creation-date order, not logical order (e.g., cheapest first or alphabetical).

**Suggestion:** Add a `sort_order` integer column to `shipping_zones` table and order by `sort_order ASC`. Alternatively, order by `base_charge ASC` for a sensible default.

#### ⚠️ No caching on shipping zones
Zones are fetched on every checkout page load with no HTTP cache headers or application-level cache. Zones rarely change.

**Suggestion:**
```php
$zones = Cache::remember('shipping_zones_active', 300, fn() =>
    ShippingZone::where('is_active', true)->orderBy('sort_order')->get()
);
```
Clear this cache key whenever a zone is created/updated/deleted.

#### ℹ️ "Free over ৳X" label rendered via string replacement
In `CheckoutManager.renderZones()`, the free-shipping note is built via `freeNote.replace('class="', 'class="text-xs ')`. This is brittle — if the span template changes, the replacement silently fails.

**Suggestion:** Build the template string directly without post-processing:
```js
const freeNote = zone.free_shipping_threshold
    ? `<span class="text-xs text-emerald-600 font-semibold">Free over ৳${zone.free_shipping_threshold}</span>`
    : '';
```

---

## Step 10 — Payment

**Files:** `resources/views/store/checkout.blade.php`, `app/Domains/Order/Controllers/CheckoutController.php`

### What Works
- COD fully implemented and connected ✓
- SSLCommerz clearly labelled "Coming Soon" in UI ✓
- `resolveRedirectUrl()` correctly routes COD to success, SSLCommerz to failed page ✓

### Issues

#### ⚠️ SSLCommerz is selectable and creates a broken order
The "Coming Soon" badge is cosmetic. The radio button is not disabled. If a user selects SSLCommerz and places an order:
1. The order **is created** in the DB with `payment_status: 'unpaid'`
2. The user is redirected to `order.failed?reason=payment_gateway_pending`
3. The order remains permanently unpaid with no recovery path

**Suggestion:** Disable the radio button (see Step 8), or add this check in `CheckoutRequest`:
```php
'payment_method' => ['required', Rule::in(['cod'])], // expand when SSLCommerz is ready
```

#### ⚠️ Verify `route('order.failed')` exists
`CheckoutController::resolveRedirectUrl()` uses `route('order.failed')` in the SSLCommerz stub. If this named route does not exist in `routes/web.php`, a `RouteNotFoundException` will be thrown **at order creation time** — which means the order is attempted, fails, and may leave a partial DB record depending on where the exception occurs within the transaction.

**Suggestion:** Grep for `order.failed` in `routes/web.php` and add it if missing:
```php
Route::get('/order-failed', [OrderController::class, 'failed'])->name('order.failed');
```

#### ℹ️ No IPN webhook routes planned yet
SSLCommerz requires IPN (Instant Payment Notification) callback routes, which must be excluded from CSRF verification. Planning this early avoids refactoring later.

**Suggestion:** When implementing SSLCommerz, add to `bootstrap/app.php`:
```php
$middleware->validateCsrfTokens(except: [
    'sslcommerz/ipn',
    'sslcommerz/success',
    'sslcommerz/fail',
    'sslcommerz/cancel',
]);
```

---

## Step 11 — Order Creation

**File:** `app/Domains/Order/Services/OrderService.php`

### What Works
- Coupon auth gate fires **before** DB transaction (fail-fast) ✓
- `checkout_token` idempotency guard — prevents double orders on retry ✓
- Single pricing engine (`CheckoutPricingService`) for both preview and order creation ✓
- `lockedVariants` from pricing engine used for stock reservation (`lockForUpdate` inside transaction) ✓
- Atomic `increment` with `whereColumn` guard in `recordCouponUsage()` ✓
- All `OrderCreated` listeners implement `ShouldQueue` — event dispatch inside transaction is safe ✓

### Issues

#### ⚠️ `isSameCheckoutAttempt()` compares `customer_name` directly on `Order`
`isSameCheckoutAttempt()` reads `$existing->customer_name` and `$existing->customer_phone`. These fields are stored in `order_addresses` (via `shippingAddress()` relationship), not directly on the `orders` table. Unless `Order` has a direct column or an accessor for these, the comparison is `null === 'John Doe'` → always `false` → the idempotency guard never returns an existing order.

```php
// isSameCheckoutAttempt() — may be comparing null vs actual values
$sameMeta = ($existing->customer_name ?? null) === ($incoming['customer_name'] ?? null)
    && ($existing->customer_phone ?? null) === ($incoming['customer_phone'] ?? null);
```

**Suggestion:** Either eager load shipping address with the idempotency query:
```php
$existing = Order::with('shippingAddress')->where('checkout_token', ...)->first();
// Then compare: $existing->shippingAddress?->customer_name
```
Or store `customer_name` and `customer_phone` as direct columns on the `orders` table for quick lookup.

#### ⚠️ Combo items re-fetched inside transaction (extra query)
In the stock reservation step (step 7 of `create()`), `Combo::with('items')->findOrFail($item['combo_id'])` is called for each combo item. The pricing engine already holds locked variants via `$pricing->lockedVariants`. This adds N extra queries per combo item.

**Suggestion:** Include combo data in the pricing engine's result object (e.g., `$pricing->resolvedCombos`) so stock reservation can use already-loaded data.

#### ℹ️ `order_number` has no UNIQUE index guard
`order_number` is generated with `Str::random(10)` — collision is astronomically unlikely but not impossible. If a collision occurs and `order_number` is not unique-indexed, two orders share the same number, breaking the success page route.

**Suggestion:** Add a unique index on `orders.order_number` in the migration and handle `UniqueConstraintViolationException` with a retry in `OrderService`.

---

## Step 12 — Order Success Page

**File:** `resources/views/store/order-success.blade.php`

### What Works
- Displays order number, shipping address, items, totals, COD payment notice ✓
- "My Orders" link shown only for `@auth` users ✓
- sessionStorage cleanup in `@push('scripts')` ✓

### Issues

#### ⚠️ No eager load guarantee for `$order->items` and `$order->shippingAddress`
The view accesses `$order->items` and `$order->shippingAddress`. If the controller that serves this page does not eager load these relationships, each triggers a separate N+1 query and `$order->shippingAddress` may return `null`, hiding the delivery address block.

**Suggestion:** In the order success controller:
```php
$order = Order::with(['items', 'shippingAddress'])
    ->where('order_number', $orderNumber)
    ->firstOrFail();
```

#### ⚠️ Combo items display blank variant title
The items loop renders `{{ $item->variant_title_snapshot }}` for all items. Combo items do not have a `variant_title_snapshot` — they have `combo_name_snapshot`. The cell renders blank for bundle orders on the success page.

**Suggestion:** Add a conditional in the items loop:
```blade
{{ $item->combo_name_snapshot ? 'Bundle' : $item->variant_title_snapshot }}
```

#### ⚠️ No access control on the success page
The route `order.success` uses `$order->order_number` as a public parameter. Anyone who knows (or guesses) an order number can view a customer's name, phone, and delivery address. While this is acceptable for simple COD stores, it is a privacy concern.

**Suggestion:** For logged-in users, verify the order belongs to them:
```php
if (Auth::check() && $order->user_id && $order->user_id !== Auth::id()) {
    abort(403);
}
```
For guest orders, consider a one-time `access_token` appended to the redirect URL.

#### ℹ️ "My Orders" link is hardcoded
```blade
<a href="/account/orders">My Orders</a>
```
Uses a hardcoded path instead of `route('account.orders')`. If the URL changes, this silently breaks.

**Suggestion:** Use `route('account.orders')` if the named route exists.

#### ℹ️ Dead sessionStorage cleanup key
```js
sessionStorage.removeItem('bionic_last_order');
```
This key is never set anywhere in the current checkout flow. This line is dead code.

---

## Step 13 — Notifications

**Files:** `app/Events/OrderCreated.php`, `app/Listeners/`, `app/Jobs/Send*Job.php`, `bootstrap/app.php`

### What Works
- Laravel 11 auto-discovery via `withEvents(discover: [...])` — no manual `EventServiceProvider` needed ✓
- All three `OrderCreated` listeners implement `ShouldQueue` — async, don't block checkout response ✓
- `SendSMSJob` injects `SMSService` via `handle()` ✓
- `SendOrderConfirmationEmail` only fires when `customer_email` is present ✓
- `CreateCourierShipmentListener` correctly listens to `OrderStatusChanged` (not `OrderCreated`) and only fires on `'confirmed'` status ✓

### Issues

#### ⚠️ `Mail::raw()` is a placeholder
`SendOrderConfirmationEmail` sends a plain-text email with no HTML, no branding, and only one line of content.

```php
Mail::raw("Your order {$order->order_number} has been received.", function ($message) use ($order) {
    $message->to($order->customer_email)->subject('Order Confirmation');
});
```

**Suggestion:** Create a proper `Mailable`:
```
php artisan make:mail OrderConfirmationMail --markdown=emails.order-confirmation
```
Pass the full order (with items and totals) to the mailable so the email body is informative.

#### ⚠️ No retry logic on notification jobs
`SendSMSJob` and `SendWhatsAppJob` have no `$tries`, `$timeout`, or `$backoff` properties. SMS/WhatsApp APIs are frequently flaky. A transient API error permanently loses the notification with default queue settings.

**Suggestion:** Add to each job:
```php
public int $tries = 3;
public array $backoff = [10, 30, 60]; // seconds between retries
public int $timeout = 15;

public function failed(Throwable $exception): void
{
    Log::error('SMS notification failed permanently', [
        'phone'   => $this->phone,
        'message' => $this->message,
        'error'   => $exception->getMessage(),
    ]);
}
```

#### ⚠️ `SendSMSJob` missing `InteractsWithQueue` trait
`SendSMSJob` uses `Dispatchable, Queueable, SerializesModels` but not `InteractsWithQueue`. Without it, the job cannot call `$this->fail()`, `$this->release()`, or `$this->attempts()` inside `handle()`.

**Suggestion:** Add the trait:
```php
use Illuminate\Queue\InteractsWithQueue;

class SendSMSJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels, InteractsWithQueue;
```

#### ⚠️ No admin / shop owner notification
When a new order is placed, no notification is sent to the shop owner or admin team. In a live store this is critical for order fulfilment.

**Suggestion:** Add a `NotifyAdminOnNewOrder` listener:
```php
class NotifyAdminOnNewOrder implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        // Send SMS/WhatsApp to config('bionic.admin_phone')
        // Or post to a Slack webhook
    }
}
```

#### ℹ️ `broadcastOn()` commented out in `OrderCreated`
Real-time order tracking (Pusher/Soketi) will require uncommenting and implementing `broadcastOn()`. Fine for now if broadcasting is not yet set up.

#### ℹ️ `CreateCourierShipmentListener` uses service locator pattern
```php
app(ShipmentService::class)->create($event->order);
```
Uses `app()` instead of constructor injection, which makes this listener harder to test in isolation.

**Suggestion:** Inject via constructor:
```php
public function __construct(private ShipmentService $shipmentService) {}
```

---

## Priority Fix List

### 🔴 Critical — Fix Immediately

| # | Location | Issue |
|---|----------|-------|
| 1 | `CheckoutManager.js:482` | `zonesModule` ReferenceError — no-zone orders crash silently |
| 2 | `WebAuthController::login()` | Cart merge failure returns 500, blocking valid login |
| 3 | `CheckoutController::resolveRedirectUrl()` | Verify `route('order.failed')` exists |

### ⚠️ High — Fix Before Production

| # | Location | Issue |
|---|----------|-------|
| 4 | `checkout.blade.php` | Disable SSLCommerz radio until gateway is implemented |
| 5 | `AuthManager.js` | Clear `bionic_cart_token` cookie after login (not just localStorage) |
| 6 | `OrderService::isSameCheckoutAttempt()` | `customer_name`/`customer_phone` compared from wrong model layer |
| 7 | `order-success.blade.php` | Eager load `items` + `shippingAddress` in success page controller |
| 8 | `SendSMSJob`, `SendWhatsAppJob` | Add `$tries`, `$backoff`, `$timeout`, `failed()` to notification jobs |
| 9 | `SendOrderConfirmationEmail` | Replace `Mail::raw()` with a proper `Mailable` + template |

### ℹ️ Low — Improvements

| # | Location | Issue |
|---|----------|-------|
| 10 | `HomeController` | Add `->limit()` to category products query |
| 11 | `product.blade.php` | `buyNowBtn` — await `Cart.add()` before redirect |
| 12 | `CartManager.js` | Replace `this.flash()` with `window.flash?.()` |
| 13 | `PublicCartController` | Fix `flash()` argument order |
| 14 | `PublicShippingZoneController` | Add `sort_order` column and `Cache::remember()` to zone query |
| 15 | `AuthManager.js` | Wire up or remove the "Remember me" checkbox |
| 16 | `WebAuthController` | Add `redirect()->intended('/')` post-login |
| 17 | `checkout.blade.php` | Pre-fill form fields from `Auth::user()` for logged-in users |
| 18 | `OrderService` | Add `UNIQUE` index on `orders.order_number` |
| 19 | `order-success.blade.php` | Fix combo item blank variant title; remove dead `bionic_last_order` cleanup |
| 20 | `SendSMSJob` | Add `InteractsWithQueue` trait |
| 21 | `Listeners/` | Add `NotifyAdminOnNewOrder` listener for shop owner alerts |

---

*Generated by full manual audit of the `rhjoyOfficial` branch — 2026-04-08.*

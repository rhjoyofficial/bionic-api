# Auth & Security Audit Report

**Scope:** `app/Domains/Auth/`, `app/Http/Middleware/`, `routes/api.php`, `routes/admin.php`, `routes/public.php`, `routes/web.php`
**Date:** 2026-03-27
**Branch:** `rhjoyOfficial`

---

## Critical Risks

### 1. Checkout Endpoint Has No Authentication — Anyone Can Place Orders

**File:** `routes/public.php:28`
**Impact:** `/checkout` is a public route with only throttle middleware. Any anonymous user can submit orders with arbitrary `customer_name`/`customer_phone`. No `auth:sanctum` guard, no guest-token validation. This allows automated order spam, fake orders flooding the system, and stock reservation abuse.

```php
// BEFORE
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1');
```

```php
// AFTER — require at least a session token or auth
Route::post('/checkout', [CheckoutController::class, 'store'])
    ->middleware(['throttle:10,1']);
// And in CheckoutRequest, add:
// 'session_token' => 'required_without:user_id|string|min:32',
// Or require auth:sanctum and handle guest checkout via optional guard
```

---

### 2. Session Token Takeover — Cart Hijacking via Guessable Tokens

**File:** `CartController.php:152-158`
**Impact:** `resolveCart()` accepts `X-Session-Token` header or `session_token` body param with zero validation. If an attacker guesses or brute-forces another user's session token, they gain full access to that cart (view, add, update, remove, clear items). No token format validation, no entropy requirement, no binding to IP or fingerprint.

```php
// BEFORE
private function resolveCart(Request $request)
{
    return $this->cartService->getCart(
        Auth::id(),
        $request->header('X-Session-Token') ?? $request->session_token
    );
}
```

```php
// AFTER — enforce minimum token length/format, generate server-side
private function resolveCart(Request $request)
{
    $sessionToken = $request->header('X-Session-Token') ?? $request->session_token;

    // Validate token format (UUID v4 or 64-char hex minimum)
    if ($sessionToken && !preg_match('/^[a-zA-Z0-9\-]{32,}$/', $sessionToken)) {
        throw new \InvalidArgumentException('Invalid session token format');
    }

    return $this->cartService->getCart(Auth::id(), $sessionToken);
}
```

---

### 3. Cart Merge Has No Ownership Validation — Any Session Token Accepted

**File:** `CartMergeService.php:9-52`, called from `AuthController.php:37-38, 59-60`
**Impact:** On login/register, the user passes `session_token` in the request. `merge()` blindly looks up the cart by that token and transfers all items to the authenticated user's cart, then deletes the guest cart. An attacker can steal any guest cart by supplying its session token during login. No verification that the session token belongs to the same browser/client.

```php
// BEFORE — trusts any session_token from request
if ($request->filled('session_token')) {
    $this->mergeService->merge($request->session_token, $user->id);
}
```

```php
// AFTER — bind session token to client fingerprint or validate via signed cookie
// Option 1: Use server-generated signed tokens stored in httpOnly cookie
// Option 2: At minimum, validate the token was issued to this client
```

---

### 4. Admin Blade Routes Have No Authentication

**File:** `routes/web.php:124-241`
**Impact:** All admin Blade pages (`/admin/dashboard`, `/admin/products`, `/admin/orders`, `/admin/categories`, `/admin/coupons`, `/admin/shipping`, `/admin/webhooks`) have zero middleware. Anyone can access the admin panel HTML. While actual data comes from API routes (which are protected), the admin UI itself is wide open — exposing page structure, JS bundles, and potentially cached data.

```php
// BEFORE
Route::prefix('admin')->group(function () {
    Route::get('/login', function () { ... });
    Route::get('/dashboard', function () { ... }); // NO AUTH
});
Route::prefix('admin/products')->group(function () { ... }); // NO AUTH
Route::prefix('admin/orders')->group(function () { ... });   // NO AUTH
```

```php
// AFTER — protect all admin blade routes
Route::prefix('admin')->group(function () {
    Route::get('/login', fn() => view('admin.auth.login'))->name('admin.login');
});

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:Admin'])
    ->group(function () {
        Route::get('/dashboard', fn() => view('admin.dashboard'));
        // ... all other admin routes
    });
```

---

### 5. Customer Account Pages Have No Authentication

**File:** `routes/web.php:63-88`
**Impact:** `/account/dashboard`, `/account/orders`, `/account/profile` are publicly accessible. These Blade pages likely render customer-specific data via JS/API calls, but the page templates themselves are exposed without any auth check.

```php
// BEFORE
Route::prefix('account')->group(function () {
    Route::get('/dashboard', function () { return view('account.dashboard'); });
    Route::get('/orders', function () { return view('account.orders'); });
    Route::get('/profile', function () { return view('account.profile'); });
});
```

```php
// AFTER
Route::prefix('account')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/dashboard', fn() => view('account.dashboard'));
        Route::get('/orders', fn() => view('account.orders'));
        Route::get('/orders/{order}', fn() => view('account.order-details'));
        Route::get('/profile', fn() => view('account.profile'));
    });
// Keep login/register outside the auth middleware
Route::get('/account/login', fn() => view('auth.login'))->name('login');
Route::get('/account/register', fn() => view('auth.register'))->name('register');
```

---

## High Risks

### 6. Debug Test Route Exposed in Production — Pricing Oracle

**File:** `routes/api.php:9-16`
**Impact:** `/v1/test-pricing/{variant}/{qty}` is a public, unauthenticated route that returns pricing calculations for any variant/quantity. Attackers can enumerate all product variants and tier pricing structures. This is an information leak and potential pricing oracle for competitors.

```php
// BEFORE
Route::get('/test-pricing/{variant}/{qty}', function (
    \App\Domains\Product\Models\ProductVariant $variant,
    $qty
) {
    $service = new \App\Domains\Product\Services\PricingService();
    return $service->calculate($variant, (int)$qty);
});
```

```php
// AFTER — remove entirely or gate behind admin auth
// DELETE THIS ROUTE IN PRODUCTION
// If needed for testing, move behind admin middleware:
Route::middleware(['auth:sanctum', 'role:Admin'])
    ->get('/test-pricing/{variant}/{qty}', ...);
```

---

### 7. Inconsistent Permission Middleware on Admin Routes

**File:** `routes/admin.php:13-56`
**Impact:** Only `categories` (index), `products` (index), and `orders` (index) have specific `permission:` middleware. All other admin routes (create/update/delete for categories, products, tier prices, shipping zones, coupons, orders, webhooks, product relations) only check `role:Admin`. This means any Admin user can perform destructive operations regardless of their granular permissions. The Spatie permission system is partially implemented but not enforced.

```php
// BEFORE — only view operations have permission middleware
Route::get('categories', [...])->middleware('permission:category.view');
Route::post('categories', [...]); // NO permission:category.create
Route::delete('categories/{category}', [...]); // NO permission:category.delete

Route::get('orders', [...])->middleware('permission:order.view');
Route::patch('orders/{order}/status', [...]); // NO permission:order.update
```

```php
// AFTER — apply consistent permission middleware
Route::get('categories', [...])->middleware('permission:category.view');
Route::post('categories', [...])->middleware('permission:category.create');
Route::put('categories/{category}', [...])->middleware('permission:category.update');
Route::delete('categories/{category}', [...])->middleware('permission:category.delete');

Route::get('orders', [...])->middleware('permission:order.view');
Route::get('orders/{order}', [...])->middleware('permission:order.view');
Route::patch('orders/{order}/status', [...])->middleware('permission:order.update');

// Similarly for products, coupons, shipping-zones, webhooks
```

---

### 8. Sanctum Token Has No Expiry and No Scope

**File:** `AuthService.php:47`
**Impact:** `createToken('bionic_token')` creates a token with no abilities (scopes) and no expiration. Tokens persist indefinitely in the `personal_access_tokens` table. A leaked token grants permanent access. No distinction between admin and customer tokens.

```php
// BEFORE
$token = $user->createToken('bionic_token')->plainTextToken;
```

```php
// AFTER — add expiration and abilities
$abilities = $user->hasRole('Admin')
    ? ['admin:*']
    : ['customer:*'];

$token = $user->createToken(
    'bionic_token',
    $abilities,
    now()->addDays(7) // expire in 7 days
)->plainTextToken;
```

---

### 9. User Object Returned Raw — Password Hash Potentially Exposed

**File:** `AuthService.php:49-55`, `AuthController.php:41-44, 63, 84`
**Impact:** The full `$user` Eloquent model is returned in the login/register/me responses. While `$hidden` includes `password` and `remember_token`, if anyone accidentally removes `$hidden` or adds `->makeVisible('password')`, the hash leaks. Better practice is to use an API Resource.

```php
// BEFORE
return [
    'user' => $user,
    'token' => $token,
];
```

```php
// AFTER — use a UserResource
return [
    'user' => new UserResource($user),
    'token' => $token,
];
// UserResource explicitly lists only safe fields
```

---

### 10. SecureHeaders Middleware Not Applied Globally

**File:** `app/Http/Middleware/SecureHeaders.php` + `bootstrap/app.php:18-24`
**Impact:** The `SecureHeaders` middleware exists but is never registered in `bootstrap/app.php`. It is not applied to any route group. Security headers (X-Frame-Options, HSTS, X-XSS-Protection, X-Content-Type-Options) are never sent.

```php
// BEFORE — bootstrap/app.php middleware section
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'role' => ...,
        'permission' => ...,
        'role_or_permission' => ...,
    ]);
})
```

```php
// AFTER — append SecureHeaders globally
->withMiddleware(function (Middleware $middleware): void {
    $middleware->append(\App\Http\Middleware\SecureHeaders::class);

    $middleware->alias([
        'role' => ...,
        'permission' => ...,
        'role_or_permission' => ...,
    ]);
})
```

---

## Medium Risks

### 11. Login Response Inconsistency — AuthService Returns Mixed Types

**File:** `AuthService.php:23, 32, 36, 49-55`
**Impact:** `authenticate()` returns `ApiResponse::error(...)` (a `JsonResponse`) on failure (lines 23, 32, 36) but returns a plain `array` on success (line 49). The controller checks `$result['success']` (line 55), but `ApiResponse::error()` returns a JsonResponse, not an array. Accessing `$result['success']` on a JsonResponse will throw an error or return unexpected results.

```php
// BEFORE — AuthController.php:55-56
$result = $this->authService->authenticate($request->validated(), $request->ip());
if (!$result['success']) { // $result might be JsonResponse, not array

// AFTER — always return arrays from service, let controller build response
// In AuthService, replace ApiResponse::error(...) with:
return ['success' => false, 'message' => 'Too many attempts...', 'code' => 429];
return ['success' => false, 'message' => 'Invalid credentials', 'code' => 401];
return ['success' => false, 'message' => 'Account disabled', 'code' => 403];
```

---

### 12. No CORS Configuration Visible — SPA Cross-Origin Risk

**File:** `bootstrap/app.php` (entire file)
**Impact:** For a headless API transitioning to React SPA, CORS must be properly configured. No CORS middleware is registered. Laravel's default `config/cors.php` may exist but if not tuned, the SPA on a different domain will fail, or worse — `allow_origins: ['*']` would allow any site to make authenticated API calls.

```php
// AFTER — ensure config/cors.php has restrictive settings
'allowed_origins' => ['https://yourdomain.com'],
'supports_credentials' => true,
```

---

### 13. Registration Allows Duplicate Phone After Case/Format Variation

**File:** `RegisterRequest.php:18`
**Impact:** `unique:users,phone` checks exact string match. Phone `+8801712345678` vs `8801712345678` vs `01712345678` would all pass as unique. An attacker can create multiple accounts with the same phone number by varying format.

```php
// BEFORE
'phone' => 'required|string|unique:users,phone|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
```

```php
// AFTER — normalize phone before validation
// In RegisterRequest::prepareForValidation():
protected function prepareForValidation(): void
{
    $this->merge([
        'phone' => preg_replace('/[^0-9]/', '', $this->phone),
    ]);
}
```

---

### 14. Login Has No `max` Rule on Input — Large Payload Attack

**File:** `LoginRequest.php:17`
**Impact:** `login` field has no `max` length rule. A malicious request with a very large `login` string (e.g., 1MB) would be passed to the database `WHERE email = <huge_string>` query, wasting DB resources.

```php
// BEFORE
'login' => 'required|string',
```

```php
// AFTER
'login' => 'required|string|max:255',
```

---

### 15. Rate Limiter Throttle Key Includes IP — Bypassable via Proxies

**File:** `AuthService.php:19`
**Impact:** `'login:' . $login . '|' . $ip` means each IP gets its own 5-attempt bucket. An attacker using rotating proxies/VPNs gets 5 fresh attempts per IP. For a targeted brute-force against a known phone number, this is weak.

```php
// BEFORE
$throttlekey = 'login:' . $login . '|' . $ip;
```

```php
// AFTER — also throttle by login identity alone (stricter)
$ipKey = 'login:' . $login . '|' . $ip;
$globalKey = 'login:' . $login;

if (RateLimiter::tooManyAttempts($ipKey, 5) || RateLimiter::tooManyAttempts($globalKey, 20)) {
    // Block — per-IP limit of 5, per-account limit of 20 across all IPs
}
// On failure, hit both:
RateLimiter::hit($ipKey, 60);
RateLimiter::hit($globalKey, 300);
```

---

### 16. Cart Routes Have No Auth — Authenticated User's Cart Accessible Without Token

**File:** `routes/public.php:34-44`
**Impact:** Cart routes are fully public. `resolveCart()` uses `Auth::id()` which returns `null` for unauthenticated requests and falls back to `session_token`. However, if an authenticated user's Sanctum token is included in the request, their cart is returned without verifying the cart belongs to them. More importantly, there's no auth middleware, so the `auth:sanctum` guard is optional and stateless — meaning cart operations work differently depending on whether a Bearer token is present, creating inconsistent behavior.

```php
// AFTER — split into guest and authenticated cart route groups
Route::prefix('cart')->group(function () {
    // Guest cart — requires session token
    Route::get('/', [CartController::class, 'view']);
    Route::post('/add', [CartController::class, 'add']);
    // ...
});

Route::middleware('auth:sanctum')->prefix('cart')->group(function () {
    // Auth cart — uses user ID
    Route::get('/', [CartController::class, 'viewAuth']);
    // ...
});
```

---

### 17. Empty Placeholder Files — Dead Code

**Files:**
- `app/Domains/Auth/Controllers/LoginController.php` (empty)
- `app/Domains/Auth/Controllers/RegisterController.php` (empty)
- `app/Domains/Auth/Controllers/LogoutController.php` (empty)
- `app/Domains/Auth/Controllers/ForgotPasswordController.php` (empty)

**Impact:** These 4 controllers are empty files. `AuthController` handles all auth operations. The empty files suggest an abandoned refactoring plan to split auth into separate controllers. They add confusion and should be removed or implemented.

**Recommendation:** Remove all 4 files since `AuthController` already handles login, register, logout, and me:

```bash
rm app/Domains/Auth/Controllers/LoginController.php
rm app/Domains/Auth/Controllers/RegisterController.php
rm app/Domains/Auth/Controllers/LogoutController.php
rm app/Domains/Auth/Controllers/ForgotPasswordController.php
```

Or implement `ForgotPasswordController` with actual password reset functionality, which is currently missing from the system entirely.

---

### 18. No Password Reset Flow Exists

**File:** Entire `app/Domains/Auth/` scope
**Impact:** There is no password reset endpoint, no reset token generation, no reset email sending. `ForgotPasswordController.php` is empty. Users who forget their password have no recovery path. This will force manual admin intervention or account abandonment.

**Recommendation:** Implement password reset flow:
```
POST /forgot-password  → send reset link/OTP
POST /reset-password   → verify token + set new password
```

---

## Summary

| Severity | Count | Key Themes |
|----------|-------|------------|
| Critical | 5 | Unauthenticated checkout, session token hijacking, unprotected admin/account Blade pages |
| High | 5 | Debug route exposed, inconsistent permissions, no token expiry, raw user object, unused security middleware |
| Medium | 8 | Mixed return types, no CORS, phone normalization, rate limiter bypass, empty files, no password reset |
| **Total** | **18** | |

**Top 3 Priorities:**
1. Protect admin Blade routes and account pages with `auth:sanctum` + `role:Admin` middleware (Issues #4, #5)
2. Remove debug pricing route and enforce consistent Spatie permission middleware on all admin API routes (Issues #6, #7)
3. Add token expiry, validate session token format, and register SecureHeaders middleware globally (Issues #8, #2, #10)

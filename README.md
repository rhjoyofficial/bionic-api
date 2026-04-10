# Bionic API + Storefront

A full-featured Laravel eCommerce application with:

- **Storefront (Blade + Vite JS)**
- **Public JSON API (`/api/v1`)**
- **Cart, coupon, checkout, and order pipeline**
- **Customer account area**
- **Admin section (Blade routes)**

Built with **Laravel 12**, **PHP 8.2+**, **Sanctum**, **Spatie Permission**, and **Tailwind/Vite**.

---

## Table of Contents

1. [Tech Stack](#tech-stack)
2. [Key Features](#key-features)
3. [Project Structure](#project-structure)
4. [Requirements](#requirements)
5. [Local Setup](#local-setup)
6. [Running the App](#running-the-app)
7. [Environment Notes](#environment-notes)
8. [Routes Overview](#routes-overview)
9. [Core Domain Flow](#core-domain-flow)
10. [Queue, Notifications & Events](#queue-notifications--events)
11. [Testing & Quality](#testing--quality)
12. [Deployment Notes](#deployment-notes)
13. [Troubleshooting](#troubleshooting)
14. [License](#license)

---

## Tech Stack

### Backend
- Laravel `^12.0`
- PHP `^8.2`
- Laravel Sanctum `^4.3`
- Spatie Laravel Permission `^7.2`
- Mews Purifier `^3.4`

### Frontend
- Vite `^7`
- Tailwind `^4`
- Vanilla JS modules under `resources/js`

### Infrastructure
- Queue: `database` (default in `.env.example`)
- Session: `database`
- Cache: `database`
- Mail: `log` (default local)

---

## Key Features

- Product catalog, variants, tier pricing, related products
- Guest + authenticated cart support
- Cart merge after login/register
- Coupon validation + checkout pricing preview
- Checkout with shipping zone pricing
- Order creation with snapshot data
- Customer dashboard (orders/profile/referral)
- Event-driven post-order notifications (SMS/Email/WhatsApp listeners)

---

## Project Structure

```text
app/
  Domains/
    Auth/
    Cart/
    Category/
    Coupon/
    Customer/
    Order/
    Product/
    Shipping/
    Store/
  Events/
  Listeners/
  Jobs/
resources/
  views/
    store/
    auth/
    customer/
  js/
routes/
  web.php       # Blade/storefront/admin routes
  api.php       # API entrypoint (/api/v1/*)
  public.php    # public API routes loaded by api.php
```

---

## Requirements

- PHP 8.2+
- Composer 2+
- Node.js 18+ (recommended)
- NPM 9+
- A database supported by Laravel (SQLite/MySQL/PostgreSQL)

---

## Local Setup

### 1) Clone

```bash
git clone <your-repo-url> bionic-api
cd bionic-api
```

### 2) Install dependencies

```bash
composer install
npm install
```

### 3) Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Update DB/mail/queue values in `.env` as needed.

### 4) Run migrations

```bash
php artisan migrate
```

### 5) Build frontend assets

```bash
npm run build
```

For local dev with hot reload:

```bash
npm run dev
```

---

## Running the App

### Option A: Simple (separate terminals)

```bash
php artisan serve
php artisan queue:listen --tries=1 --timeout=0
npm run dev
```

### Option B: Combined (composer script)

```bash
composer run dev
```

This starts:
- Laravel server
- Queue listener
- Log stream (pail)
- Vite dev server

---

## Environment Notes

Default `.env.example` uses:

- `SESSION_DRIVER=database`
- `QUEUE_CONNECTION=database`
- `CACHE_STORE=database`
- `MAIL_MAILER=log`

If you use database drivers, ensure migrations for session/cache/jobs tables are in place.

---

## Routes Overview

### Web Routes (`routes/web.php`)

- Storefront:
  - `/` home
  - `/product/{slug}`
  - `/cart`, `/checkout`, `/order-success/{order}`, `/order-failed`
- Auth pages:
  - `/login`, `/register`, `/forgot-password`, `/password/reset/{token}`
- Customer:
  - `/account/dashboard`
  - `/account/orders`
  - `/account/orders/{order}`
  - `/account/profile`
- Admin Blade pages under `/admin/*`

### API Routes (`/api/v1/*`)

From `routes/api.php` + `routes/public.php`:

- Auth:
  - `POST /register`
  - `POST /login`
  - `POST /logout`
  - `GET /me`
- Products:
  - `GET /products`
  - `GET /products/{slug}`
  - `GET /products/{id}/recommendations`
- Cart:
  - `GET /cart`
  - `POST /cart/add`
  - `POST /cart/add-combo`
  - `POST /cart/update`
  - `POST /cart/remove`
  - `DELETE /cart/clear`
- Checkout:
  - `POST /checkout/preview`
  - `POST /checkout`
- Coupon:
  - `POST /coupon/validate`
- Shipping:
  - `GET /shipping-zones`

---

## Core Domain Flow

### 1) Product → Cart
- Product data loads from public APIs/domain services
- Cart accepts variant/combo items
- Server validates stock and updates reserved stock

### 2) Cart → Checkout
- Checkout preview endpoint calculates authoritative totals
- Coupon and tier discounts are applied server-side
- Shipping cost is zone-based

### 3) Checkout → Order
- Order is created with immutable line snapshots
- Coupon usage is tracked
- Relevant events are dispatched

### 4) Post-order
- Success/failure pages
- Notification listeners/jobs run via queue

---

## Queue, Notifications & Events

- `OrderCreated` event triggers listeners for:
  - SMS
  - Email
  - WhatsApp
  - Referral commission creation

Make sure queue workers are running in non-local environments.

---

## Testing & Quality

Run backend tests:

```bash
php artisan test
```

Run formatter/linting (if configured in your workflow):

```bash
./vendor/bin/pint
```

Build frontend to catch JS/Vite issues:

```bash
npm run build
```

---

## Deployment Notes

- Set `APP_ENV=production` and `APP_DEBUG=false`
- Use a real queue backend/worker (database/redis + supervisor/systemd)
- Configure mail/SMS/WhatsApp credentials
- Run:
  - `php artisan config:cache`
  - `php artisan route:cache`
  - `php artisan view:cache`
- Ensure storage symlink exists:

```bash
php artisan storage:link
```

---

## Troubleshooting

### `Class ... not found` / autoload issues
```bash
composer dump-autoload
```

### Frontend not updating
```bash
npm run dev
# or
npm run build
```

### Queue jobs not processing
```bash
php artisan queue:listen
```

### Session/cart behavior inconsistent
- Verify `.env` session driver and DB table migrations
- Clear stale caches:

```bash
php artisan optimize:clear
```

---

## License

This project is open-sourced under the [MIT license](https://opensource.org/licenses/MIT).


```
bionic-api
├─ app
│  ├─ Console
│  │  └─ Commands
│  │     ├─ AbandonExpiredCarts.php
│  │     └─ ExpireCoupons.php
│  ├─ Core
│  │  ├─ BaseController.php
│  │  ├─ BaseRepository.php
│  │  └─ BaseService.php
│  ├─ Domains
│  │  ├─ ActivityLog
│  │  │  └─ Models
│  │  │     └─ ActivityLog.php
│  │  ├─ Admin
│  │  │  ├─ Controllers
│  │  │  │  ├─ AdminActivityLogController.php
│  │  │  │  ├─ AdminDashboardController.php
│  │  │  │  └─ AdminSettingsController.php
│  │  │  ├─ Models
│  │  │  │  └─ Setting.php
│  │  │  └─ Services
│  │  │     └─ DashboardStatsService.php
│  │  ├─ Auth
│  │  │  ├─ Controllers
│  │  │  │  ├─ AdminAuthController.php
│  │  │  │  ├─ AdminRoleController.php
│  │  │  │  ├─ AuthController.php
│  │  │  │  ├─ ForgotPasswordController.php
│  │  │  │  └─ WebAuthController.php
│  │  │  ├─ Requests
│  │  │  │  ├─ LoginRequest.php
│  │  │  │  └─ RegisterRequest.php
│  │  │  ├─ Resources
│  │  │  │  └─ UserResource.php
│  │  │  └─ Services
│  │  │     └─ AuthService.php
│  │  ├─ Cart
│  │  │  ├─ Controllers
│  │  │  │  ├─ CartController.php
│  │  │  │  └─ PublicCartController.php
│  │  │  ├─ Models
│  │  │  │  ├─ Cart.php
│  │  │  │  └─ CartItem.php
│  │  │  ├─ Resources
│  │  │  │  └─ CartItemResource.php
│  │  │  └─ Services
│  │  │     ├─ CartMergeService.php
│  │  │     ├─ CartPricingService.php
│  │  │     └─ CartService.php
│  │  ├─ Category
│  │  │  ├─ Controllers
│  │  │  │  ├─ AdminCategoryController.php
│  │  │  │  └─ PublicCategoryController.php
│  │  │  ├─ Models
│  │  │  │  └─ Category.php
│  │  │  ├─ Requests
│  │  │  │  ├─ StoreCategoryRequest.php
│  │  │  │  └─ UpdateCategoryRequest.php
│  │  │  ├─ Resources
│  │  │  │  └─ CategoryResource.php
│  │  │  └─ Services
│  │  │     └─ CategoryService.php
│  │  ├─ Coupon
│  │  │  ├─ Controllers
│  │  │  │  ├─ AdminCouponController.php
│  │  │  │  └─ PublicCouponController.php
│  │  │  ├─ Models
│  │  │  │  ├─ Coupon.php
│  │  │  │  └─ CouponUsage.php
│  │  │  ├─ Requests
│  │  │  │  ├─ BulkGenerateCouponRequest.php
│  │  │  │  ├─ StoreCouponRequest.php
│  │  │  │  └─ UpdateCouponRequest.php
│  │  │  ├─ Resources
│  │  │  │  └─ CouponResource.php
│  │  │  └─ Services
│  │  │     └─ CouponValidationService.php
│  │  ├─ Courier
│  │  │  ├─ Controllers
│  │  │  │  └─ AdminCourierController.php
│  │  │  ├─ Models
│  │  │  │  └─ CourierShipment.php
│  │  │  └─ Services
│  │  │     └─ ShipmentService.php
│  │  ├─ Customer
│  │  │  ├─ Controllers
│  │  │  │  ├─ AdminCustomerController.php
│  │  │  │  └─ CustomerDashboard.php
│  │  │  └─ Resources
│  │  │     └─ AdminCustomerResource.php
│  │  ├─ Intelligence
│  │  │  └─ Services
│  │  │     ├─ DynamicPricingService.php
│  │  │     ├─ FraudScoreService.php
│  │  │     ├─ InventoryPredictionService.php
│  │  │     ├─ RecommendationService.php
│  │  │     ├─ SegmentationService.php
│  │  │     └─ UpsellSuggestionService.php
│  │  ├─ Marketing
│  │  │  ├─ Models
│  │  │  │  ├─ LandingPage.php
│  │  │  │  └─ MarketingEvent.php
│  │  │  ├─ Repository
│  │  │  │  └─ LandingPageRepository.php
│  │  │  ├─ Resource
│  │  │  │  └─ LandingPageResource.php
│  │  │  └─ Services
│  │  │     ├─ GTMEventService.php
│  │  │     ├─ LandingPageService.php
│  │  │     └─ MetaConversionService.php
│  │  ├─ Notification
│  │  │  ├─ Controllers
│  │  │  │  └─ AdminNotificationController.php
│  │  │  └─ Requests
│  │  │     └─ SendNotificationRequest.php
│  │  ├─ Order
│  │  │  ├─ Actions
│  │  │  │  ├─ ConfirmOrderAction.php
│  │  │  │  ├─ CreateOrderAction.php
│  │  │  │  └─ ShipOrderAction.php
│  │  │  ├─ Controllers
│  │  │  │  ├─ AdminOrderController.php
│  │  │  │  ├─ AdminTransactionController.php
│  │  │  │  ├─ CheckoutController.php
│  │  │  │  ├─ OrderController.php
│  │  │  │  └─ OrderTrackingController.php
│  │  │  ├─ DTOs
│  │  │  │  └─ CheckoutPricingResult.php
│  │  │  ├─ Enums
│  │  │  │  └─ OrderStatus.php
│  │  │  ├─ Models
│  │  │  │  ├─ Commission.php
│  │  │  │  ├─ Order.php
│  │  │  │  ├─ OrderAddress.php
│  │  │  │  ├─ OrderItem.php
│  │  │  │  ├─ OrderNote.php
│  │  │  │  └─ OrderTransaction.php
│  │  │  ├─ Requests
│  │  │  │  ├─ CheckoutPreviewRequest.php
│  │  │  │  ├─ CheckoutRequest.php
│  │  │  │  ├─ StoreTransactionRequest.php
│  │  │  │  ├─ UpdateOrderStatusRequest.php
│  │  │  │  └─ UpdatePaymentStatusRequest.php
│  │  │  ├─ Resources
│  │  │  │  ├─ OrderResource.php
│  │  │  │  └─ TransactionResource.php
│  │  │  └─ Services
│  │  │     ├─ CheckoutPricingService.php
│  │  │     ├─ OrderEditService.php
│  │  │     ├─ OrderService.php
│  │  │     └─ OrderStatusService.php
│  │  ├─ Product
│  │  │  ├─ Controllers
│  │  │  │  ├─ AdminComboController.php
│  │  │  │  ├─ AdminProductController.php
│  │  │  │  ├─ ProductLandingController.php
│  │  │  │  ├─ ProductRecommendationController.php
│  │  │  │  ├─ ProductRelationController.php
│  │  │  │  ├─ ProductSearchController.php
│  │  │  │  ├─ ProductTierPriceController.php
│  │  │  │  └─ PublicProductController.php
│  │  │  ├─ Models
│  │  │  │  ├─ Combo.php
│  │  │  │  ├─ ComboItem.php
│  │  │  │  ├─ Product.php
│  │  │  │  ├─ ProductRelation.php
│  │  │  │  ├─ ProductTierPrice.php
│  │  │  │  └─ ProductVariant.php
│  │  │  ├─ Requests
│  │  │  │  ├─ ProductSearchRequest.php
│  │  │  │  ├─ StoreComboRequest.php
│  │  │  │  ├─ StoreProductRequest.php
│  │  │  │  ├─ UpdateComboRequest.php
│  │  │  │  └─ UpdateProductRequest.php
│  │  │  ├─ Resources
│  │  │  │  ├─ ComboResource.php
│  │  │  │  ├─ ProductLandingResource.php
│  │  │  │  ├─ ProductResource.php
│  │  │  │  ├─ ProductTierResource.php
│  │  │  │  └─ ProductVariantResource.php
│  │  │  └─ Services
│  │  │     ├─ PricingService.php
│  │  │     ├─ ProductRelationService.php
│  │  │     ├─ ProductSearchService.php
│  │  │     └─ ProductService.php
│  │  ├─ Shipping
│  │  │  ├─ Controllers
│  │  │  │  ├─ AdminShippingZoneController.php
│  │  │  │  └─ PublicShippingZoneController.php
│  │  │  ├─ Models
│  │  │  │  └─ ShippingZone.php
│  │  │  ├─ Requests
│  │  │  │  ├─ ReorderShippingZonesRequest.php
│  │  │  │  ├─ StoreShippingZoneRequest.php
│  │  │  │  └─ UpdateShippingZoneRequest.php
│  │  │  ├─ Resources
│  │  │  │  └─ ShippingZoneResource.php
│  │  │  └─ Services
│  │  │     └─ ShippingCalculator.php
│  │  ├─ Store
│  │  │  ├─ Controllers
│  │  │  │  ├─ CatalogController.php
│  │  │  │  ├─ ComboPageController.php
│  │  │  │  ├─ HomeController.php
│  │  │  │  └─ ProductPageController.php
│  │  │  └─ Models
│  │  │     └─ HeroBanner.php
│  │  └─ Webhook
│  │     ├─ Controllers
│  │     │  └─ AdminWebhookController.php
│  │     └─ Models
│  │        └─ Webhook.php
│  ├─ Events
│  │  ├─ CouponExpired.php
│  │  ├─ OrderCreated.php
│  │  └─ OrderStatusChanged.php
│  ├─ Helpers
│  │  ├─ ApiResponse.php
│  │  ├─ flash.php
│  │  └─ format.php
│  ├─ Http
│  │  ├─ Controllers
│  │  │  └─ Controller.php
│  │  └─ Middleware
│  │     ├─ EnsureUserIsAdmin.php
│  │     ├─ HandleCartSession.php
│  │     └─ SecureHeaders.php
│  ├─ Infrastructure
│  │  ├─ Courier
│  │  │  ├─ CourierInterface.php
│  │  │  ├─ CourierService.php
│  │  │  └─ Drivers
│  │  │     ├─ CarryBeeCourier.php
│  │  │     ├─ PathaoCourier.php
│  │  │     └─ SteadfastCourier.php
│  │  ├─ Notification
│  │  │  └─ Services
│  │  │     ├─ EmailService.php
│  │  │     └─ SmsService.php
│  │  ├─ SMS
│  │  │  └─ SMSService.php
│  │  ├─ Webhook
│  │  │  └─ WebhookService.php
│  │  └─ WhatsApp
│  │     └─ WhatsAppService.php
│  ├─ Jobs
│  │  ├─ SendSMSJob.php
│  │  ├─ SendWebhookJob.php
│  │  └─ SendWhatsAppJob.php
│  ├─ Listeners
│  │  ├─ CreateCourierShipmentListener.php
│  │  ├─ CreateReferralCommissionListener.php
│  │  ├─ DeactivateExpiredCoupons.php
│  │  ├─ NotifyAdminOnNewOrder.php
│  │  ├─ OrderStatusNotificationListener.php
│  │  ├─ SendOrderConfirmationEmail.php
│  │  ├─ SendOrderSMSListener.php
│  │  ├─ SendOrderStatusEmail.php
│  │  └─ SendOrderWhatsAppListener.php
│  ├─ Mail
│  │  └─ OrderConfirmationMail.php
│  ├─ Models
│  │  └─ User.php
│  ├─ Notifications
│  │  ├─ AdminBroadcastNotification.php
│  │  └─ OrderStatusPushNotification.php
│  ├─ Policies
│  │  └─ ProductPolicy.php
│  ├─ Providers
│  │  ├─ AppServiceProvider.php
│  │  └─ ViewServiceProvider.php
│  └─ View
│     └─ Components
├─ artisan
├─ bootstrap
│  ├─ app.php
│  ├─ cache
│  │  ├─ packages.php
│  │  └─ services.php
│  └─ providers.php
├─ composer.json
├─ composer.lock
├─ config
│  ├─ activitylog.php
│  ├─ app.php
│  ├─ auth.php
│  ├─ bionic.php
│  ├─ cache.php
│  ├─ courier.php
│  ├─ database.php
│  ├─ filesystems.php
│  ├─ firebase.php
│  ├─ logging.php
│  ├─ mail.php
│  ├─ permission.php
│  ├─ purifier.php
│  ├─ queue.php
│  ├─ sanctum.php
│  ├─ services.php
│  ├─ session.php
│  ├─ sms.php
│  └─ whatsapp.php
├─ database
│  ├─ factories
│  │  └─ UserFactory.php
│  ├─ migrations
│  │  ├─ 0001_01_01_000000_create_users_table.php
│  │  ├─ 0001_01_01_000001_create_cache_table.php
│  │  ├─ 0001_01_01_000002_create_jobs_table.php
│  │  ├─ 2026_02_27_145848_create_personal_access_tokens_table.php
│  │  ├─ 2026_02_27_145953_create_permission_tables.php
│  │  ├─ 2026_02_27_151202_create_categories_table.php
│  │  ├─ 2026_02_27_153707_create_products_table.php
│  │  ├─ 2026_02_27_153731_create_product_variants_table.php
│  │  ├─ 2026_02_27_153804_create_product_tier_prices_table.php
│  │  ├─ 2026_02_27_153805_create_product_relations_table.php
│  │  ├─ 2026_02_27_153806_create_combos_table.php
│  │  ├─ 2026_02_27_153807_create_combo_items_table.php
│  │  ├─ 2026_02_27_153821_create_shipping_zones_table.php
│  │  ├─ 2026_02_27_153842_create_coupons_table.php
│  │  ├─ 2026_02_27_153902_create_orders_table.php
│  │  ├─ 2026_02_27_153903_create_order_addresses_table.php
│  │  ├─ 2026_02_27_153904_create_coupon_usages_table.php
│  │  ├─ 2026_02_27_153938_create_order_items_table.php
│  │  ├─ 2026_02_27_154527_create_landing_pages_table.php
│  │  ├─ 2026_03_04_053308_create_carts_table.php
│  │  ├─ 2026_03_04_053331_create_cart_items_table.php
│  │  ├─ 2026_03_07_153023_create_device_tokens_table.php
│  │  ├─ 2026_03_07_153203_create_courier_shipments_table.php
│  │  ├─ 2026_03_07_154330_create_webhooks_table.php
│  │  ├─ 2026_03_14_074212_create_hero_banners_table.php
│  │  ├─ 2026_03_28_155636_create_order_transactions_table.php
│  │  ├─ 2026_03_28_155815_create_commissions_table.php
│  │  ├─ 2026_04_08_161603_add_sort_order_to_shipping_zones_table.php
│  │  ├─ 2026_04_08_192246_create_order_notes_table.php
│  │  ├─ 2026_04_09_000001_create_activity_log_table.php
│  │  ├─ 2026_04_09_100001_create_notifications_table.php
│  │  ├─ 2026_04_09_110001_add_gateway_ref_to_orders_table.php
│  │  ├─ 2026_04_09_120001_create_settings_table.php
│  │  └─ 2026_04_10_100001_enhance_courier_shipments_table.php
│  └─ seeders
│     ├─ CategorySeeder.php
│     ├─ ComboSeeder.php
│     ├─ CouponSeeder.php
│     ├─ DatabaseSeeder.php
│     ├─ HeroBannerSeeder.php
│     ├─ LandingPageSeeder.php
│     ├─ ProductSeeder.php
│     ├─ RoleSeeder.php
│     ├─ ShippingZoneSeeder.php
│     ├─ UserSeeder.php
│     └─ WebhookSeeder.php
├─ FLOW_AUDIT.md
├─ package-lock.json
├─ package.json
├─ phpunit.xml
├─ public
│  ├─ .htaccess
│  ├─ assets
│  │  ├─ ads
│  │  │  ├─ promo-image-1.jpg
│  │  │  ├─ promo-image-2.jpg
│  │  │  ├─ promo-image-3.jpg
│  │  │  └─ ramadan-banner.jpg
│  │  ├─ categories
│  │  │  ├─ dates.gif
│  │  │  ├─ ghee.gif
│  │  │  ├─ honey.gif
│  │  │  ├─ nuts.gif
│  │  │  ├─ oils.gif
│  │  │  └─ seeds.gif
│  │  ├─ certificates
│  │  │  ├─ bsti.png
│  │  │  ├─ gmo.png
│  │  │  ├─ gmp.jpeg
│  │  │  ├─ gmp.png
│  │  │  ├─ haccp.jpeg
│  │  │  ├─ haccp.png
│  │  │  ├─ halal-food.png
│  │  │  ├─ halal.jpeg
│  │  │  ├─ halal.png
│  │  │  ├─ iso.jpeg
│  │  │  ├─ iso.png
│  │  │  ├─ msg.png
│  │  │  ├─ premium.png
│  │  │  └─ pure.png
│  │  ├─ combo-products
│  │  │  ├─ combo.jpg
│  │  │  ├─ combo.png
│  │  │  └─ combo1.jpg
│  │  ├─ hero-products
│  │  │  ├─ beet-root.png
│  │  │  ├─ floral-honey.png
│  │  │  ├─ honey-gift.png
│  │  │  ├─ honey-jar-2.png
│  │  │  └─ honey-jar.png
│  │  ├─ images
│  │  │  ├─ bionic-logo.png
│  │  │  ├─ bionic-white-logo.png
│  │  │  ├─ certificates
│  │  │  │  ├─ bsti.png
│  │  │  │  ├─ gmo-free.png
│  │  │  │  ├─ haccp.png
│  │  │  │  ├─ halal.png
│  │  │  │  ├─ iso-22000.png
│  │  │  │  └─ no-msg.png
│  │  │  ├─ customer1.png
│  │  │  ├─ customer2.png
│  │  │  ├─ customer3.png
│  │  │  ├─ dates.png
│  │  │  ├─ honey-gift.png
│  │  │  ├─ honey-jar-2.png
│  │  │  ├─ honey-jar.png
│  │  │  ├─ offer-1.jpg
│  │  │  ├─ offer-2.jpg
│  │  │  ├─ offer-3.jpg
│  │  │  ├─ offer1.png
│  │  │  ├─ offer2.png
│  │  │  ├─ offer3.png
│  │  │  ├─ product-1.png
│  │  │  ├─ product-2.png
│  │  │  ├─ product-3.png
│  │  │  ├─ product-4.png
│  │  │  ├─ product-5.png
│  │  │  └─ product-6.png
│  │  ├─ offer
│  │  │  └─ products.gif
│  │  ├─ products
│  │  │  ├─ honey-jar.png
│  │  │  ├─ product-1.jpg
│  │  │  ├─ product-2.jpg
│  │  │  ├─ product-3.jpg
│  │  │  ├─ product-4.jpg
│  │  │  ├─ product-5.jpg
│  │  │  ├─ product-6.jpg
│  │  │  ├─ product-7.jpg
│  │  │  └─ product-8.jpg
│  │  ├─ review
│  │  │  ├─ review-1.jpeg
│  │  │  ├─ review-2.jpeg
│  │  │  └─ review-3.jpeg
│  │  └─ video
│  │     ├─ video-file.mp4
│  │     └─ video-thumbnail.png
│  ├─ favicon.ico
│  ├─ favicon.png
│  ├─ index.php
│  └─ robots.txt
├─ README.md
├─ resources
│  ├─ css
│  │  ├─ app.css
│  │  └─ flash.css
│  ├─ js
│  │  ├─ admin.js
│  │  ├─ api
│  │  │  ├─ auth.js
│  │  │  ├─ cart.js
│  │  │  ├─ client.js
│  │  │  ├─ coupon.js
│  │  │  ├─ order.js
│  │  │  └─ product.js
│  │  ├─ app.js
│  │  ├─ auth
│  │  │  └─ AuthManager.js
│  │  ├─ bootstrap.js
│  │  ├─ cart
│  │  │  ├─ AddToCartBinder.js
│  │  │  ├─ CartManager.js
│  │  │  ├─ CartPageRenderer.js
│  │  │  ├─ CartRenderer.js
│  │  │  └─ product-card.js
│  │  ├─ filter
│  │  │  └─ categoryFilter.js
│  │  ├─ flash.js
│  │  ├─ managers
│  │  │  ├─ CheckoutManager.js
│  │  │  ├─ ValidationManager.js
│  │  │  └─ video-manager.js
│  │  └─ search-suggestion.js
│  └─ views
│     ├─ admin
│     │  ├─ access-control
│     │  │  └─ index.blade.php
│     │  ├─ activity-log
│     │  │  └─ index.blade.php
│     │  ├─ auth
│     │  │  └─ login.blade.php
│     │  ├─ categories
│     │  │  └─ index.blade.php
│     │  ├─ combos
│     │  │  ├─ create.blade.php
│     │  │  ├─ edit.blade.php
│     │  │  ├─ index.blade.php
│     │  │  └─ _combo_form_script.blade.php
│     │  ├─ coupons
│     │  │  └─ index.blade.php
│     │  ├─ customers
│     │  │  ├─ index.blade.php
│     │  │  └─ show.blade.php
│     │  ├─ dashboard.blade.php
│     │  ├─ notifications
│     │  │  └─ index.blade.php
│     │  ├─ orders
│     │  │  ├─ index.blade.php
│     │  │  └─ show.blade.php
│     │  ├─ products
│     │  │  ├─ create.blade.php
│     │  │  ├─ edit.blade.php
│     │  │  └─ index.blade.php
│     │  ├─ settings
│     │  │  └─ index.blade.php
│     │  ├─ shipping
│     │  │  └─ index.blade.php
│     │  └─ transactions
│     │     └─ index.blade.php
│     ├─ auth
│     │  ├─ forgot-password.blade.php
│     │  ├─ login.blade.php
│     │  ├─ register.blade.php
│     │  └─ reset-password.blade.php
│     ├─ components
│     │  ├─ combo-card.blade.php
│     │  ├─ flash-container.blade.php
│     │  ├─ floating-object.blade.php
│     │  ├─ footer.blade.php
│     │  ├─ navbar.blade.php
│     │  ├─ page-header.blade.php
│     │  └─ product-card.blade.php
│     ├─ customer
│     │  ├─ dashboard.blade.php
│     │  ├─ order-details.blade.php
│     │  ├─ orders.blade.php
│     │  ├─ partials
│     │  │  └─ nav.blade.php
│     │  └─ profile.blade.php
│     ├─ emails
│     │  └─ order-confirmation.blade.php
│     ├─ layouts
│     │  ├─ admin.blade.php
│     │  ├─ app.blade.php
│     │  └─ guest.blade.php
│     └─ store
│        ├─ cart.blade.php
│        ├─ checkout.blade.php
│        ├─ order-failed.blade.php
│        ├─ order-success.blade.php
│        ├─ pages
│        │  ├─ combos.blade.php
│        │  ├─ home.blade.php
│        │  └─ products.blade.php
│        ├─ partials
│        │  ├─ ad-promotions.blade.php
│        │  ├─ cart-drawer.blade.php
│        │  ├─ certifications.blade.php
│        │  ├─ combo-products.blade.php
│        │  ├─ footer.blade.php
│        │  ├─ header.blade.php
│        │  ├─ hero.blade.php
│        │  ├─ product-categories.blade.php
│        │  ├─ testimonial-showcase.blade.php
│        │  ├─ trending-products.blade.php
│        │  └─ video-promotion.blade.php
│        ├─ product.blade.php
│        └─ shop.blade.php
├─ routes
│  ├─ admin.php
│  ├─ api.php
│  ├─ console.php
│  ├─ public.php
│  └─ web.php
├─ storage
│  ├─ app
│  │  ├─ private
│  │  └─ public
│  ├─ framework
│  │  ├─ cache
│  │  │  └─ data
│  │  ├─ sessions
│  │  ├─ testing
│  │  └─ views
│  └─ logs
├─ tests
│  ├─ Feature
│  │  ├─ ExampleTest.php
│  │  ├─ OrderTest.php
│  │  ├─ ProductTest.php
│  │  └─ Unit
│  │     └─ AuthServiceTest.php
│  ├─ TestCase.php
│  └─ Unit
│     ├─ ExampleTest.php
│     └─ PricingServiceTest.php
└─ vite.config.js

```


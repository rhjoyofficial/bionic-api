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
│  │  ├─ Certification
│  │  │  └─ Models
│  │  │     └─ Certification.php
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
│  │  │  ├─ Models
│  │  │  ├─ Resources
│  │  │  │  └─ AdminCustomerResource.php
│  │  │  └─ Services
│  │  ├─ Intelligence
│  │  │  └─ Services
│  │  │     ├─ DynamicPricingService.php
│  │  │     ├─ FraudScoreService.php
│  │  │     ├─ InventoryPredictionService.php
│  │  │     ├─ RecommendationService.php
│  │  │     ├─ SegmentationService.php
│  │  │     └─ UpsellSuggestionService.php
│  │  ├─ Landing
│  │  │  ├─ Controllers
│  │  │  │  ├─ AdminLandingPageController.php
│  │  │  │  ├─ LandingCheckoutController.php
│  │  │  │  └─ LandingPageController.php
│  │  │  ├─ Models
│  │  │  │  ├─ LandingPage.php
│  │  │  │  ├─ LandingPageItem.php
│  │  │  │  └─ MarketingEvent.php
│  │  │  ├─ Resources
│  │  │  │  └─ LandingPageResource.php
│  │  │  └─ Services
│  │  │     └─ LandingCheckoutService.php
│  │  ├─ Marketing
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
│  │  │     ├─ AdminOrderCreationService.php
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
│  └─ Providers
│     ├─ AppServiceProvider.php
│     └─ ViewServiceProvider.php
├─ artisan
├─ bootstrap
│  ├─ app.php
│  ├─ cache
│  │  ├─ pac5F08.tmp
│  │  ├─ pacB772.tmp
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
│  │  ├─ 2026_02_27_153901_create_landing_pages_table.php
│  │  ├─ 2026_02_27_153902_create_landing_page_items_table.php
│  │  ├─ 2026_02_27_153902_create_orders_table.php
│  │  ├─ 2026_02_27_153903_create_order_addresses_table.php
│  │  ├─ 2026_02_27_153904_create_coupon_usages_table.php
│  │  ├─ 2026_02_27_153938_create_order_items_table.php
│  │  ├─ 2026_03_04_053308_create_carts_table.php
│  │  ├─ 2026_03_04_053331_create_cart_items_table.php
│  │  ├─ 2026_03_07_153023_create_device_tokens_table.php
│  │  ├─ 2026_03_07_153203_create_courier_shipments_table.php
│  │  ├─ 2026_03_07_154330_create_webhooks_table.php
│  │  ├─ 2026_03_14_074212_create_hero_banners_table.php
│  │  ├─ 2026_03_28_155636_create_order_transactions_table.php
│  │  ├─ 2026_03_28_155815_create_commissions_table.php
│  │  ├─ 2026_04_08_192246_create_order_notes_table.php
│  │  ├─ 2026_04_09_000001_create_activity_log_table.php
│  │  ├─ 2026_04_09_100001_create_notifications_table.php
│  │  ├─ 2026_04_09_120001_create_settings_table.php
│  │  ├─ 2026_04_13_114146_create_certifications_table.php
│  │  ├─ 2026_04_13_114207_create_certification_product_table.php
│  │  ├─ 2026_04_13_120333_create_media_videos_table.php
│  │  └─ 2026_04_13_120334_create_social_proofs_table.php
│  └─ seeders
│     ├─ CategorySeeder.php
│     ├─ CertificationSeeder.php
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
│     │  ├─ landing-pages
│     │  │  ├─ create.blade.php
│     │  │  ├─ edit.blade.php
│     │  │  └─ index.blade.php
│     │  ├─ notifications
│     │  │  └─ index.blade.php
│     │  ├─ orders
│     │  │  ├─ create.blade.php
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
│     ├─ landing
│     │  ├─ partials
│     │  │  └─ _checkout.blade.php
│     │  └─ templates
│     │     ├─ combo-default.blade.php
│     │     ├─ mangrove-gold-honey.blade.php
│     │     ├─ product-default.blade.php
│     │     ├─ sales-default.blade.php
│     │     └─ sukkari.blade.php
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
│  │     ├─ ads
│  │     │  ├─ promo-image-1.jpg
│  │     │  ├─ promo-image-2.jpg
│  │     │  ├─ promo-image-3.jpg
│  │     │  └─ ramadan-banner.jpg
│  │     ├─ categories
│  │     │  ├─ dates.gif
│  │     │  ├─ dry_fruits.gif
│  │     │  ├─ ghee.gif
│  │     │  ├─ honey.gif
│  │     │  ├─ nuts.gif
│  │     │  ├─ oils.gif
│  │     │  ├─ seeds.gif
│  │     │  ├─ spices.gif
│  │     │  └─ sweeteners.gif
│  │     ├─ certifications
│  │     │  ├─ bsti.png
│  │     │  ├─ gmo.png
│  │     │  ├─ gmp.png
│  │     │  ├─ haccp.png
│  │     │  ├─ halal-food.png
│  │     │  ├─ halal.png
│  │     │  ├─ iso.png
│  │     │  ├─ msg.png
│  │     │  ├─ premium.png
│  │     │  └─ pure.png
│  │     ├─ combos
│  │     │  ├─ combo.png
│  │     │  ├─ combo1.jpg
│  │     │  ├─ energy-booster.jpg
│  │     │  ├─ family-pack.jpg
│  │     │  ├─ heart-oil.jpg
│  │     │  ├─ immunity-pack.jpg
│  │     │  ├─ kitchen-essentials.jpg
│  │     │  ├─ premium-dates.jpg
│  │     │  ├─ royal-dates.jpg
│  │     │  ├─ sweet-bundle.jpg
│  │     │  ├─ vitality-boost.jpg
│  │     │  └─ weight-management.jpg
│  │     ├─ hero-products
│  │     │  ├─ beet-root.png
│  │     │  ├─ floral-honey.png
│  │     │  ├─ honey-gift.png
│  │     │  ├─ honey-jar-2.png
│  │     │  └─ honey-jar.png
│  │     ├─ images
│  │     │  ├─ bionic-logo.png
│  │     │  ├─ bionic-white-logo.png
│  │     │  ├─ certificates
│  │     │  │  ├─ bsti.png
│  │     │  │  ├─ gmo-free.png
│  │     │  │  ├─ haccp.png
│  │     │  │  ├─ halal.png
│  │     │  │  ├─ iso-22000.png
│  │     │  │  └─ no-msg.png
│  │     │  ├─ customer1.png
│  │     │  ├─ customer2.png
│  │     │  ├─ customer3.png
│  │     │  ├─ dates.png
│  │     │  ├─ honey-gift.png
│  │     │  ├─ honey-jar-2.png
│  │     │  ├─ honey-jar.png
│  │     │  ├─ offer-1.jpg
│  │     │  ├─ offer-2.jpg
│  │     │  ├─ offer-3.jpg
│  │     │  ├─ offer1.png
│  │     │  ├─ offer2.png
│  │     │  ├─ offer3.png
│  │     │  ├─ product-1.png
│  │     │  ├─ product-2.png
│  │     │  ├─ product-3.png
│  │     │  ├─ product-4.png
│  │     │  └─ product-5.png
│  │     ├─ offer
│  │     │  └─ products.gif
│  │     ├─ products
│  │     │  ├─ ajwa-dates.jpg
│  │     │  ├─ beetroot-powder.jpg
│  │     │  ├─ black-seed-oil.jpg
│  │     │  ├─ brain-booster-mix.jpg
│  │     │  ├─ california-almonds.jpg
│  │     │  ├─ ceylon-cinnamon.jpg
│  │     │  ├─ chia-seeds.jpg
│  │     │  ├─ coconut-oil.jpg
│  │     │  ├─ dried-apricots.jpg
│  │     │  ├─ extra-virgin-olive-oil.jpg
│  │     │  ├─ floral-gold-honey.jpg
│  │     │  ├─ fresh-apples.jpg
│  │     │  ├─ goler-gurr.jpg
│  │     │  ├─ green-cardamom.jpg
│  │     │  ├─ honey-jar.png
│  │     │  ├─ honey.jpg
│  │     │  ├─ kalmi-dates.jpg
│  │     │  ├─ mangrove-gold-honey.jpg
│  │     │  ├─ mariyam-dates.jpg
│  │     │  ├─ medjool-dates.jpg
│  │     │  ├─ mixed-premium-nuts.jpg
│  │     │  ├─ mustard-oil.jpg
│  │     │  ├─ pink-salt.jpg
│  │     │  ├─ premium-ghee.jpg
│  │     │  ├─ sukkari-dates.jpg
│  │     │  ├─ tokma-seeds.jpg
│  │     │  └─ vital-mix.jpg
│  │     ├─ review
│  │     │  ├─ review-1.jpeg
│  │     │  ├─ review-2.jpeg
│  │     │  └─ review-3.jpeg
│  │     └─ video
│  │        ├─ video-file.mp4
│  │        └─ video-thumbnail.png
│  ├─ debugbar
│  │  ├─ 01KPF9KQFMVWJSTVVX1355GJ2R.json
│  │  ├─ 01KPF9KXAB076NGQX423G62BN8.json
│  │  ├─ 01KPF9KYN145G1YNM61Z10G8BJ.json
│  │  ├─ 01KPF9YFWA9BMESTD3GKGK4F2G.json
│  │  ├─ 01KPF9YMVHGG80B584F26QCQ45.json
│  │  ├─ 01KPF9YNVZMP3XA2VKYACSRBV0.json
│  │  ├─ 01KPF9YQGX2ESD0XDHE819FR8X.json
│  │  ├─ 01KPF9YRD4H3J5Z6E0S0ZNMT21.json
│  │  ├─ 01KPF9YZ1H7JAVY00FVCE4PAVV.json
│  │  ├─ 01KPF9Z0HQ1AJS3E8E6AYJT9H2.json
│  │  ├─ 01KPF9Z1HTWTA71S34M7AANGDJ.json
│  │  ├─ 01KPF9Z5SBCE9QAHX24MMAQ3NF.json
│  │  ├─ 01KPF9Z75206Q2Z4XZJ3TN9GR8.json
│  │  ├─ 01KPF9Z83VDHD91CHE2NJGXDC5.json
│  │  ├─ 01KPFA0QJ3ZAVTR362AAX2R6S6.json
│  │  ├─ 01KPFA0RQFQYRTCKNJVYT6CYZM.json
│  │  ├─ 01KPFA0SMC4C89GPSZVST8PFFH.json
│  │  ├─ 01KPFA59D7224YD6M8V7CVV38M.json
│  │  ├─ 01KPFA5BJGV5KWR2DMRG6TVSQZ.json
│  │  ├─ 01KPFA5DA2NC272MG7ES2G1EJ5.json
│  │  ├─ 01KPFA5F7WK7TY7AV0W7WEQPQT.json
│  │  ├─ 01KPFA5G8YWTQ5FM55CTRMYHED.json
│  │  ├─ 01KPFA5H738HATMRK8P5GHEBSM.json
│  │  ├─ 01KPFA5JES93CHGA46QJMEXGB3.json
│  │  ├─ 01KPFA5KG65QZKDEBV6N9Z0HSA.json
│  │  ├─ 01KPFA5ZMK5TG4BTE2D4BPPQMG.json
│  │  ├─ 01KPFA610AAR6NC2WATXZM6HEB.json
│  │  ├─ 01KPFA622R069VYQQ10JA3VTJ3.json
│  │  ├─ 01KPFA678R812QW5JZ2135C2AN.json
│  │  ├─ 01KPFA6977QAP6CEG9DKQHAZVF.json
│  │  ├─ 01KPFA6AH9SDMBSEP0DJ21D1NN.json
│  │  ├─ 01KPFA6BKB6Z9V0G5KEJVF7KYE.json
│  │  ├─ 01KPFA6D3E51H9PT9THRJ4935P.json
│  │  ├─ 01KPFA6E05M0ZQJ4PWER8981CC.json
│  │  ├─ 01KPFA6M7QB18SH7BX95M2S97G.json
│  │  ├─ 01KPFA6PCRBG82FAC8Q1QCVACW.json
│  │  ├─ 01KPFA6R3QAKMQNNQMJVDZ27PC.json
│  │  ├─ 01KPFA6T2AQC8DS92YRBF3VZVT.json
│  │  ├─ 01KPFA6V24NA8Y37B2H7J99763.json
│  │  ├─ 01KPFA703B5PDM6B71614CYEPT.json
│  │  ├─ 01KPFA7161EXBGF13745TGKQAD.json
│  │  ├─ 01KPFA72GWFC7JAT04BK2MT0QN.json
│  │  ├─ 01KPFA73J1HBM9GZHRH4X3E638.json
│  │  ├─ 01KPFA800J1NEZWBWPZHAC3MVG.json
│  │  ├─ 01KPFA81ZXE4YPP9MCS3Q6VY63.json
│  │  ├─ 01KPFA831H5G6K2Y3B29A18M7Z.json
│  │  ├─ 01KPFA88V64C185XQXRWBHEKCF.json
│  │  ├─ 01KPFA8AG0866MEJY75BFNZRKZ.json
│  │  ├─ 01KPFA8BJ0VRXZMANQFM9S5Z4V.json
│  │  ├─ 01KPFA8EP6FK7CAJX1F9Y8K89K.json
│  │  ├─ 01KPFA8FZRK16VCTJR8X9XXRQB.json
│  │  ├─ 01KPFA8GVNPB1WF604SHKMF5RT.json
│  │  ├─ 01KPFA966PM5CNNNRANVRXSY9J.json
│  │  ├─ 01KPFA97J74Q1SRSX13XQ27BT5.json
│  │  ├─ 01KPFA98FCKX10E0N7EZZRJC3K.json
│  │  ├─ 01KPFAA13392NJ1GN6840NQS2B.json
│  │  ├─ 01KPFAA2NVV7C5P2DN7FHQYQ6Y.json
│  │  ├─ 01KPFAA3SM9SKS36NK6996J77R.json
│  │  ├─ 01KPFAA4T5T4H4RK19C6PQCMW5.json
│  │  ├─ 01KPFAA5RYYM0KCJ1TAT8X6XRR.json
│  │  ├─ 01KPFAA7KTJ6QSYNCP1HB02N4A.json
│  │  ├─ 01KPFAA8TFYSCKPBCDYGWG57NE.json
│  │  ├─ 01KPFAACFKKF2B7CWWJNX5G29M.json
│  │  ├─ 01KPFAADPMKCRZSARV7KBGXTYG.json
│  │  ├─ 01KPFAAEQ8T2ZJ53FF24AA8K9D.json
│  │  ├─ 01KPFAB36MVBBYPCMXZASC90RA.json
│  │  ├─ 01KPFAB9SGA6KEAQGTWJMHYGMN.json
│  │  ├─ 01KPFABQ9PC7J6B3MMWKRP877P.json
│  │  ├─ 01KPFABXDD16FRCB867PDAW6SQ.json
│  │  ├─ 01KPFABYZNGG002DKBHQEJHMC9.json
│  │  ├─ 01KPFAC0FZT0D7JS5JDXFSJAN8.json
│  │  ├─ 01KPFAEFADGXPS30QBSAR21BTH.json
│  │  ├─ 01KPFAEHGDVWAJ555KMHCKCBQY.json
│  │  ├─ 01KPFAEJRQJKTYHZ3F0G5ZWSYE.json
│  │  ├─ 01KPFAEM8D62PTQEDC8F2B7FKX.json
│  │  ├─ 01KPFAENR299VC30FTMX928FC8.json
│  │  ├─ 01KPFAEPMQ7G2Q2HT8E08G8D6A.json
│  │  ├─ 01KPFAETSPJZGT6YETXV4JPE4W.json
│  │  ├─ 01KPFAEWSC8K2TZJJRJACFVJ6S.json
│  │  ├─ 01KPFAEXYE8BFQN9HKV8DV1Y0M.json
│  │  ├─ 01KPFAF04BK3JXYZ69P50BZVKG.json
│  │  ├─ 01KPFAF1KCF4CC1PMDP9XYRP87.json
│  │  ├─ 01KPFAF2R8MJ1F2HCCS7DSAXW8.json
│  │  ├─ 01KPFAF4YEPEHZJMG3HCHHMD7J.json
│  │  ├─ 01KPFAF6K4QWW5TM9QNEZ8TYH1.json
│  │  ├─ 01KPFAF7M3EBDTMTPNCM86H799.json
│  │  ├─ 01KPFAFASGCDS93ZZ0S4ZTHG0T.json
│  │  ├─ 01KPFAFCDW807TA8HQ2JZ0MG19.json
│  │  ├─ 01KPFAFDW7E62TXFSGB7E14KDV.json
│  │  ├─ 01KPFAFF2JBV8D6DTMP93BXC5Z.json
│  │  ├─ 01KPFAFGN6C4DJTSEY49QAD0J3.json
│  │  ├─ 01KPFAFHMND9X4XQHG99FG6C1V.json
│  │  ├─ 01KPFAFR4ERTCFAZYE2TKVR4Y1.json
│  │  ├─ 01KPFAFSJ8ZA9XNCM7PSDNJZDK.json
│  │  ├─ 01KPFAFTE5PPJ8EXZK120H4M6V.json
│  │  ├─ 01KPFAGDNJ3R9QKKVZH01FRAQ9.json
│  │  ├─ 01KPFAGF1BAHMJ7CMXC3Q63YH9.json
│  │  ├─ 01KPFAGGDN753J839RFF3QFXF4.json
│  │  ├─ 01KPFAGJ0J11R2CQ0ER82ZF4E1.json
│  │  ├─ 01KPFAGK9XC6C0N092RA0X9V92.json
│  │  ├─ 01KPFAGKX34E3RFRF5XW01HBFG.json
│  │  ├─ 01KPFAGTJRSFWRDE9G413KYH23.json
│  │  ├─ 01KPFAGW7YSFD24KCJCW3KAT6B.json
│  │  ├─ 01KPFAGXA7Z23A1TS9ZER3M4MH.json
│  │  ├─ 01KPFAGXVM2TFJ4CJW3HAP7EYP.json
│  │  ├─ 01KPFAGZSCQJNPJ5NX7V2FHRG9.json
│  │  ├─ 01KPFAH0V36GMBMYE5VENQGQPT.json
│  │  ├─ 01KPFAM87E5X0SNNKGB0YTBNE7.json
│  │  ├─ 01KPFAMA4CB9ZTFYMJVK97EQQ1.json
│  │  ├─ 01KPFAMDYYJW711FRY215Q1THR.json
│  │  ├─ 01KPFAMF96SY46RYSHRZGDJ6BZ.json
│  │  ├─ 01KPFAMKCHCCFF1YMFWVZ75TM4.json
│  │  └─ 01KPFAMMWSCE2K2KJ2G02CSB0N.json
│  ├─ framework
│  │  ├─ cache
│  │  │  └─ data
│  │  ├─ sessions
│  │  ├─ testing
│  │  └─ views
│  │     ├─ 04fd903f47a47e208d3c57aa108f7f9c.php
│  │     ├─ 069fbd28d13a421839802438bb95dcd4.php
│  │     ├─ 078b54b02a68659e6397f1e516b780ee.php
│  │     ├─ 0978eb187b2f6e5abd91ef8940481c43.php
│  │     ├─ 145bd1317591dedc7b3885af2eb3882f.php
│  │     ├─ 19e43a973006d96a667afed50a3bc938.php
│  │     ├─ 1b97c66821b117a7a87f20019cf48a42.php
│  │     ├─ 1e6cbbc15bc47c63d0f14e35f0c2b01c.php
│  │     ├─ 1ff9b59c11e1b117846804e0c0e370dc.php
│  │     ├─ 258441c29c0cefac54d14dd917712507.php
│  │     ├─ 2c9f2d16afd1bdc2c5e1e4ed30b94c73.php
│  │     ├─ 2da1f7958bdfe18aa69ba799665ceff9.php
│  │     ├─ 32a3ed27c1680cb934728e83f89171e7.php
│  │     ├─ 394f92b630eac525c254e6379778cdd8.php
│  │     ├─ 4021f97fa2d4c626e9826a04dfb8e762.php
│  │     ├─ 41978af39c8a6b825b5d1290ea6d4e5a.php
│  │     ├─ 46a23365478ec7adcb2ef1b89df7f1bf.php
│  │     ├─ 4bfb9a16160c18216a13e70346c8d2a8.php
│  │     ├─ 4cde8096cb1c8ceca049bdfba26785ff.php
│  │     ├─ 53f13d19fb513704ae50f4af16a4bd03.php
│  │     ├─ 5441415c95962af9f9127580912b95bb.php
│  │     ├─ 562c108607088433c682389637edcadb.php
│  │     ├─ 593e15931a640cc2b83867c5c4639c0c.php
│  │     ├─ 5ac6e21b3023c607c4869f7c6eccd2c8.php
│  │     ├─ 60de5b31477eed2c917cb94f66259bcf.php
│  │     ├─ 651802ac66473df462322637a51cef1c.php
│  │     ├─ 7061dea29e8661ef7aab5489e77193ef.php
│  │     ├─ 707b7cea1dde63b894cac31692decb42.php
│  │     ├─ 73be8f2acefae7b615c99ca834e36a55.php
│  │     ├─ 7a1d471a639501c7b2d590ee17693a6f.php
│  │     ├─ 7f701b811b81161a54d05257e6c62408.php
│  │     ├─ 7faeacc0118cb9aa78a261b93a6f0bf3.php
│  │     ├─ 8511ebe2b20016d901aa5ff0036dd164.php
│  │     ├─ 86bcc272718c1167fa51cc1e9957744e.php
│  │     ├─ 8c7ca36001d8a7387f81e2ddc8ba2829.php
│  │     ├─ 98b8a5f90f21a88bae4bd1859e2be72f.php
│  │     ├─ 99259c941ed5b5deb2581763998f1c77.php
│  │     ├─ 9a4a61a52c6f07271fd54eab27bb4624.php
│  │     ├─ 9ba4e17c1b9389d766fdaf0e168540e0.php
│  │     ├─ 9d44be4ca158697c403a005eb9b44001.php
│  │     ├─ a0f816882ddf036bd25623f756af160d.php
│  │     ├─ a8d09f7b90c6b184fbf5baf9c1ca8990.php
│  │     ├─ b24f7823960397adaf09d5fdc96cfaac.php
│  │     ├─ b2e6a1a43c02095750c7f3ccd7373958.php
│  │     ├─ b8824bece766f48c28cc4c00e5897c1e.php
│  │     ├─ b8d5e7c616a797308060736cd70f44b9.php
│  │     ├─ b90509c8b764ca0b54c78b6ec21c1be6.php
│  │     ├─ c6a66fadae7fef1b3218363659299981.php
│  │     ├─ d21e7b8d471798023792595ba3e5d834.php
│  │     ├─ d31892a6b2f18f0341802845f0b876ba.php
│  │     ├─ d4ba63431bd07b0f138446c51a0f8217.php
│  │     ├─ df5878eb8be8762191dc9237f3d0ea28.php
│  │     ├─ e1da0da6368228eb4ca60cb3f64bb1e7.php
│  │     ├─ e71be9fee3e1d45128db6c7691a89f74.php
│  │     ├─ e74a8d16447a4c9f2de05cacf96c4000.php
│  │     ├─ eecc48342a04c8666dcdfad24fe847a1.php
│  │     ├─ f398a81a725d4e2981ab858b1d1001be.php
│  │     └─ faca7892463b368ba62ea56e2285545b.php
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

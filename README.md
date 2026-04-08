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


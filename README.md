<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). 



```
bionic-api
├─ .editorconfig
├─ app
│  ├─ Console
│  │  └─ Commands
│  │     └─ AbandonExpiredCarts.php
│  ├─ Core
│  │  ├─ BaseController.php
│  │  ├─ BaseRepository.php
│  │  └─ BaseService.php
│  ├─ Domains
│  │  ├─ Auth
│  │  │  ├─ Controllers
│  │  │  │  ├─ AuthController.php
│  │  │  │  ├─ ForgotPasswordController.php
│  │  │  │  ├─ LoginController.php
│  │  │  │  ├─ LogoutController.php
│  │  │  │  └─ RegisterController.php
│  │  │  ├─ Requests
│  │  │  │  ├─ LoginRequest.php
│  │  │  │  └─ RegisterRequest.php
│  │  │  ├─ Resources
│  │  │  │  └─ UserResource.php
│  │  │  └─ Services
│  │  │     └─ AuthService.php
│  │  ├─ Cart
│  │  │  ├─ Controllers
│  │  │  │  └─ CartController.php
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
│  │  │  │  ├─ StoreCouponRequest.php
│  │  │  │  └─ UpdateCouponRequest.php
│  │  │  └─ Services
│  │  │     └─ CouponValidationService.php
│  │  ├─ Courier
│  │  │  ├─ Models
│  │  │  │  └─ CourierShipment.php
│  │  │  └─ Services
│  │  │     └─ ShipmentService.php
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
│  │  ├─ Order
│  │  │  ├─ Actions
│  │  │  │  ├─ ConfirmOrderAction.php
│  │  │  │  ├─ CreateOrderAction.php
│  │  │  │  └─ ShipOrderAction.php
│  │  │  ├─ Controllers
│  │  │  │  ├─ AdminOrderController.php
│  │  │  │  ├─ CheckoutController.php
│  │  │  │  └─ OrderTrackingController.php
│  │  │  ├─ Enums
│  │  │  │  └─ OrderStatus.php
│  │  │  ├─ Events
│  │  │  │  ├─ OrderDelivered.php
│  │  │  │  └─ OrderPlaced.php
│  │  │  ├─ Listeners
│  │  │  │  ├─ SendOrderConfirmation.php
│  │  │  │  └─ TriggerMarketingEvent.php
│  │  │  ├─ Models
│  │  │  │  ├─ Order.php
│  │  │  │  ├─ OrderAddress.php
│  │  │  │  └─ OrderItem.php
│  │  │  ├─ Requests
│  │  │  │  ├─ CheckoutRequest.php
│  │  │  │  └─ UpdateOrderStatusRequest.php
│  │  │  ├─ Resources
│  │  │  │  └─ OrderResource.php
│  │  │  └─ Services
│  │  │     ├─ OrderCalculationService.php
│  │  │     ├─ OrderService.php
│  │  │     └─ OrderStatusService.php
│  │  ├─ Product
│  │  │  ├─ Controllers
│  │  │  │  ├─ AdminProductController.php
│  │  │  │  ├─ ProductLandingController.php
│  │  │  │  ├─ ProductRecommendationController.php
│  │  │  │  ├─ ProductRelationController.php
│  │  │  │  ├─ ProductSearchController.php
│  │  │  │  ├─ ProductTierPriceController.php
│  │  │  │  └─ PublicProductController.php
│  │  │  ├─ Models
│  │  │  │  ├─ Product.php
│  │  │  │  ├─ ProductRelation.php
│  │  │  │  ├─ ProductTierPrice.php
│  │  │  │  └─ ProductVariant.php
│  │  │  ├─ Requests
│  │  │  │  ├─ ProductSearchRequest.php
│  │  │  │  ├─ StoreProductRequest.php
│  │  │  │  └─ UpdateProductRequest.php
│  │  │  ├─ Resources
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
│  │  │  │  ├─ StoreShippingZoneRequest.php
│  │  │  │  └─ UpdateShippingZoneRequest.php
│  │  │  ├─ Resources
│  │  │  │  └─ ShippingZoneResource.php
│  │  │  └─ Services
│  │  │     └─ ShippingCalculator.php
│  │  ├─ Store
│  │  │  ├─ Controllers
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
│  │     ├─ SecureHeaders.php
│  │     └─ SecurityHeaders.php
│  ├─ Infrastructure
│  │  ├─ Courier
│  │  │  ├─ CourierInterface.php
│  │  │  ├─ CourierService.php
│  │  │  └─ Drivers
│  │  │     ├─ PathaoCourier.php
│  │  │     ├─ RedXCourier.php
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
│  │  ├─ OrderStatusNotificationListener.php
│  │  ├─ SendOrderConfirmationEmail.php
│  │  ├─ SendOrderSMSListener.php
│  │  ├─ SendOrderSMSNotification.php
│  │  ├─ SendOrderStatusEmail.php
│  │  ├─ SendOrderWhatsAppListener.php
│  │  └─ SendWhatsAppOrderNotification.php
│  ├─ Models
│  │  ├─ Combo.php
│  │  ├─ ComboItem.php
│  │  ├─ Commission.php
│  │  ├─ OrderTransaction.php
│  │  └─ User.php
│  ├─ Notifications
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
│  │  ├─ packages.php
│  │  └─ services.php
│  └─ providers.php
├─ composer.json
├─ composer.lock
├─ config
│  ├─ app.php
│  ├─ auth.php
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
│  │  └─ 2026_03_28_155815_create_commissions_table.php
│  ├─ schema
│  │  └─ mysql-schema.sql
│  └─ seeders
│     ├─ CategorySeeder.php
│     ├─ ComboSeeder.php
│     ├─ DatabaseSeeder.php
│     ├─ HeroBannerSeeder.php
│     ├─ ProductSeeder.php
│     ├─ RoleSeeder.php
│     └─ UserSeeder.php
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
│  │  │  ├─ gmp.png
│  │  │  ├─ haccp.png
│  │  │  ├─ halal-food.png
│  │  │  ├─ halal.png
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
│  │  │  └─ product-5.png
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
│  │  ├─ api
│  │  │  ├─ auth.js
│  │  │  ├─ cart.js
│  │  │  ├─ client.js
│  │  │  ├─ coupon.js
│  │  │  ├─ order.js
│  │  │  └─ product.js
│  │  ├─ app.js
│  │  ├─ bootstrap.js
│  │  ├─ cart
│  │  │  ├─ AddToCartBinder.js
│  │  │  ├─ CartManager.js
│  │  │  ├─ CartRenderer.js
│  │  │  └─ product-card.js
│  │  ├─ filter
│  │  │  └─ categoryFilter.js
│  │  ├─ flash.js
│  │  ├─ managers
│  │  │  └─ video-manager.js
│  │  ├─ pages
│  │  │  ├─ cart.js
│  │  │  ├─ checkout.js
│  │  │  ├─ home.js
│  │  │  ├─ product.js
│  │  │  └─ shop.js
│  │  └─ search-suggestion.js
│  └─ views
│     ├─ components
│     │  ├─ combo-card.blade.php
│     │  ├─ flash-container.blade.php
│     │  ├─ floating-object.blade.php
│     │  ├─ footer.blade.php
│     │  ├─ navbar.blade.php
│     │  └─ product-card.blade.php
│     ├─ layouts
│     │  ├─ admin.blade.php
│     │  └─ app.blade.php
│     └─ store
│        ├─ cart.blade.php
│        ├─ checkout.blade.php
│        ├─ pages
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
│  │     │  ├─ ghee.gif
│  │     │  ├─ honey.gif
│  │     │  ├─ nuts.gif
│  │     │  ├─ oils.gif
│  │     │  └─ seeds.gif
│  │     ├─ certificates
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
│  │     ├─ combo-products
│  │     │  ├─ combo.jpg
│  │     │  ├─ combo.png
│  │     │  └─ combo1.jpg
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
│  │     │  ├─ default-products.jpg
│  │     │  ├─ honey-jar.png
│  │     │  ├─ product-1.jpg
│  │     │  ├─ product-2.jpg
│  │     │  ├─ product-3.jpg
│  │     │  ├─ product-4.jpg
│  │     │  ├─ product-6.jpg
│  │     │  ├─ product-7.jpg
│  │     │  └─ product-8.jpg
│  │     ├─ review
│  │     │  ├─ review-1.jpeg
│  │     │  ├─ review-2.jpeg
│  │     │  └─ review-3.jpeg
│  │     └─ video
│  │        ├─ video-file.mp4
│  │        └─ video-thumbnail.png
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
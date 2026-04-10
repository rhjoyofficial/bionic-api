<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->index();
            $table->string('key', 100)->unique();
            $table->enum('type', ['string', 'boolean', 'integer', 'text'])->default('string');
            $table->text('value')->nullable();
            $table->string('label', 150);
            $table->string('description', 300)->nullable();
            $table->boolean('is_readonly')->default(false)
                ->comment('Read-only settings cannot be changed from the admin UI');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ── Seed default settings ──────────────────────────────────────────
        $now = now();
        DB::table('settings')->insert([
            // General
            ['group' => 'general', 'key' => 'app_name',       'type' => 'string',  'value' => config('app.name', 'My Store'),     'label' => 'Application Name',    'description' => 'The public name of your store.',           'is_readonly' => false, 'sort_order' => 1,  'created_at' => $now, 'updated_at' => $now],
            ['group' => 'general', 'key' => 'app_timezone',   'type' => 'string',  'value' => config('app.timezone', 'UTC'),       'label' => 'Timezone',            'description' => 'Server timezone for all date/time display.','is_readonly' => false, 'sort_order' => 2,  'created_at' => $now, 'updated_at' => $now],
            ['group' => 'general', 'key' => 'app_locale',     'type' => 'string',  'value' => config('app.locale', 'en'),          'label' => 'Default Locale',      'description' => 'Default language locale (e.g. en, bn).',   'is_readonly' => false, 'sort_order' => 3,  'created_at' => $now, 'updated_at' => $now],
            ['group' => 'general', 'key' => 'app_url',        'type' => 'string',  'value' => config('app.url', ''),               'label' => 'Application URL',     'description' => 'Full public URL of your application.',     'is_readonly' => false, 'sort_order' => 4,  'created_at' => $now, 'updated_at' => $now],
            ['group' => 'general', 'key' => 'maintenance_secret', 'type' => 'string', 'value' => '',                              'label' => 'Maintenance Bypass Secret', 'description' => 'Secret URL token to bypass maintenance mode.', 'is_readonly' => false, 'sort_order' => 5, 'created_at' => $now, 'updated_at' => $now],

            // Contact
            ['group' => 'contact', 'key' => 'admin_email',    'type' => 'string',  'value' => config('bionic.admin_email', ''),    'label' => 'Admin Email',         'description' => 'Primary admin notification email.',        'is_readonly' => false, 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'contact', 'key' => 'admin_phone',    'type' => 'string',  'value' => config('bionic.admin_phone', ''),    'label' => 'Admin Phone',         'description' => 'Primary admin phone (for SMS alerts).',    'is_readonly' => false, 'sort_order' => 11, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'contact', 'key' => 'support_email',  'type' => 'string',  'value' => '',                                  'label' => 'Support Email',       'description' => 'Customer-facing support email address.',   'is_readonly' => false, 'sort_order' => 12, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'contact', 'key' => 'support_phone',  'type' => 'string',  'value' => '',                                  'label' => 'Support Phone',       'description' => 'Customer-facing support phone number.',    'is_readonly' => false, 'sort_order' => 13, 'created_at' => $now, 'updated_at' => $now],

            // Business
            ['group' => 'business', 'key' => 'currency',           'type' => 'string',  'value' => 'BDT',   'label' => 'Currency Code',        'description' => 'ISO 4217 currency code (e.g. BDT, USD).',   'is_readonly' => false, 'sort_order' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'business', 'key' => 'currency_symbol',    'type' => 'string',  'value' => '৳',     'label' => 'Currency Symbol',      'description' => 'Currency symbol displayed in the storefront.','is_readonly' => false, 'sort_order' => 21, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'business', 'key' => 'order_prefix',       'type' => 'string',  'value' => 'ORD-',  'label' => 'Order Number Prefix',  'description' => 'Prefix prepended to every order number.',   'is_readonly' => false, 'sort_order' => 22, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'business', 'key' => 'low_stock_threshold','type' => 'integer', 'value' => '5',     'label' => 'Low Stock Threshold',  'description' => 'Alert when product stock falls below this.', 'is_readonly' => false, 'sort_order' => 23, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'business', 'key' => 'default_tax_rate',   'type' => 'integer', 'value' => '0',     'label' => 'Default Tax Rate (%)', 'description' => 'Default VAT/tax percentage applied to orders.','is_readonly' => false, 'sort_order' => 24, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'business', 'key' => 'free_shipping_above','type' => 'integer', 'value' => '0',     'label' => 'Free Shipping Above',  'description' => 'Order subtotal above which shipping is free (0 = disabled).', 'is_readonly' => false, 'sort_order' => 25, 'created_at' => $now, 'updated_at' => $now],

            // Mail info (read-only — sourced from env, displayed for reference)
            ['group' => 'mail_info', 'key' => 'mail_mailer',       'type' => 'string', 'value' => config('mail.default', 'log'),                                    'label' => 'Mail Driver',      'description' => 'Set via MAIL_MAILER in .env',     'is_readonly' => true, 'sort_order' => 30, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'mail_info', 'key' => 'mail_host',         'type' => 'string', 'value' => config('mail.mailers.smtp.host', ''),                             'label' => 'SMTP Host',        'description' => 'Set via MAIL_HOST in .env',       'is_readonly' => true, 'sort_order' => 31, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'mail_info', 'key' => 'mail_port',         'type' => 'string', 'value' => (string) config('mail.mailers.smtp.port', ''),                    'label' => 'SMTP Port',        'description' => 'Set via MAIL_PORT in .env',       'is_readonly' => true, 'sort_order' => 32, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'mail_info', 'key' => 'mail_from_address', 'type' => 'string', 'value' => config('mail.from.address', ''),                                  'label' => 'From Address',     'description' => 'Set via MAIL_FROM_ADDRESS in .env','is_readonly' => true, 'sort_order' => 33, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'mail_info', 'key' => 'mail_from_name',    'type' => 'string', 'value' => config('mail.from.name', config('app.name', '')),                 'label' => 'From Name',        'description' => 'Set via MAIL_FROM_NAME in .env',  'is_readonly' => true, 'sort_order' => 34, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

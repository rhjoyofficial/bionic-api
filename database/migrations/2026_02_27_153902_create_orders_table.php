<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            // 1. Identity & Tracking
            $table->id();
            $table->string('order_number')->unique();
            $table->string('checkout_token')->nullable()->unique();
            $table->string('source', 30)->default('checkout'); // checkout, landing, admin

            // 2. Foreign Keys
            $table->foreignId('user_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('landing_page_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('zone_id')->constrained('shipping_zones');
            $table->foreignId('coupon_id')->nullable()->constrained()->restrictOnDelete();

            // 3. Customer Information
            $table->string('customer_name', 150);
            $table->string('customer_phone', 20);
            $table->string('customer_email')->nullable();

            // 4. Financials
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_total', 10, 2)->default(0);
            $table->decimal('shipping_cost', 10, 2);
            $table->decimal('grand_total', 10, 2);

            // Coupon Snapshot
            $table->string('coupon_code_snapshot')->nullable();
            $table->decimal('coupon_discount', 10, 2)->default(0);

            // 5. Payment Details
            $table->enum('payment_method', ['cod', 'sslcommerz'])->default('cod');
            $table->enum('payment_status', ['unpaid', 'paid', 'failed'])->default('unpaid');
            $table->string('gateway_transaction_id')->nullable()->comment('SSLCommerz tran_id, etc.');

            // 6. Order Status & Notes
            $table->enum('order_status', [
                'pending',
                'confirmed',
                'processing',
                'shipped',
                'delivered',
                'cancelled',
                'returned'
            ])->default('pending');
            $table->text('notes')->nullable();

            // 7. Milelines & Timestamps
            $table->timestamp('placed_at')->nullable()->useCurrent();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            // 8. Indexes for Performance
            $table->index(['order_status', 'customer_phone']);
            $table->index(['placed_at', 'delivered_at']);
            $table->index('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

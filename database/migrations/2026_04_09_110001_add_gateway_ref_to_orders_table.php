<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds gateway_transaction_id to orders for payment reconciliation tracking.
 * Stores the external payment provider's reference (e.g. SSLCommerz tran_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('gateway_transaction_id')->nullable()->after('checkout_token')
                ->comment('External payment gateway reference (SSLCommerz tran_id, etc.)');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('gateway_transaction_id');
        });
    }
};

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
        Schema::create('courier_shipments', function (Blueprint $table) {
            // 1. Primary & Foreign Keys
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // 2. Courier Identity
            $table->string('courier'); // e.g., 'Pathao', 'Steadfast'
            $table->string('consignment_id')->nullable();
            $table->string('tracking_code')->nullable();

            // 3. Status & Messaging
            $table->string('status')->default('pending');
            $table->string('courier_status_message')->nullable();

            // 4. Financials
            $table->decimal('delivery_fee', 10, 2)->nullable();
            $table->decimal('cod_amount', 10, 2)->nullable();

            // 5. Raw Data & Synchronization
            $table->json('courier_response')->nullable();
            $table->timestamp('status_synced_at')->nullable();

            // 6. Milestone Timestamps
            $table->timestamp('picked_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps(); // created_at and updated_at

            // 7. Indexes (Grouped for performance/readability)
            $table->index('tracking_code');
            $table->index('consignment_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_shipments');
    }
};

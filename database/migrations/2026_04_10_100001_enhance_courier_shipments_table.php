<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courier_shipments', function (Blueprint $table) {
            $table->string('consignment_id')->nullable()->after('tracking_code');
            $table->decimal('delivery_fee', 10, 2)->nullable()->after('status');
            $table->decimal('cod_amount', 10, 2)->nullable()->after('delivery_fee');
            $table->string('courier_status_message')->nullable()->after('cod_amount');
            $table->json('courier_response')->nullable()->after('courier_status_message');
            $table->foreignId('created_by')->nullable()->after('courier_response')
                  ->constrained('users')->nullOnDelete();
            $table->timestamp('picked_at')->nullable()->after('created_by');
            $table->timestamp('delivered_at')->nullable()->after('picked_at');
            $table->timestamp('status_synced_at')->nullable()->after('delivered_at');

            $table->index('consignment_id');
        });
    }

    public function down(): void
    {
        Schema::table('courier_shipments', function (Blueprint $table) {
            $table->dropIndex(['consignment_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'consignment_id',
                'delivery_fee',
                'cod_amount',
                'courier_status_message',
                'courier_response',
                'created_by',
                'picked_at',
                'delivered_at',
                'status_synced_at',
            ]);
        });
    }
};

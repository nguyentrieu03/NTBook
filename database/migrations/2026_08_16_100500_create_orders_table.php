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
            $table->id();
            $table->unsignedBigInteger('marketplace_account_id')->index();
            $table->string('external_order_id', 40);
            $table->string('buyer_username', 120)->nullable()->index();
            $table->enum('status', ['paid', 'shipped', 'delivered', 'cancelled', 'unmapped'])->default('paid')->index();
            $table->string('ship_to_name', 120)->nullable();
            $table->string('ship_to_city', 120)->nullable();
            $table->string('ship_to_state', 80)->nullable();
            $table->string('ship_to_country', 80)->nullable();
            $table->string('ship_to_postal_code', 20)->nullable();
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->char('currency', 3)->default('USD');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamp('ordered_at')->nullable()->index();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['marketplace_account_id', 'external_order_id'], 'uq_orders_account_external_order');
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

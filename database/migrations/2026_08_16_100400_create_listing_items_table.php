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
        Schema::create('listing_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id')->index();
            $table->string('seller_sku', 120)->nullable()->index();
            $table->string('variation_label', 255)->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedBigInteger('product_variant_id')->nullable()->index();
            $table->enum('mapping_status', ['unmapped', 'mapped', 'ignored'])->default('unmapped')->index();
            $table->timestamp('mapped_at')->nullable();
            $table->unsignedBigInteger('mapped_by')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_items');
    }
};

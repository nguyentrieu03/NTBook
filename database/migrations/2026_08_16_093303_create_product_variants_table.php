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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->tinyInteger('is_active')->default(1); // 1: đang bán
            $table->integer('sort_order')->default(0);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};

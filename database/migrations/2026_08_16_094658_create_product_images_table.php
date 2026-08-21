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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_variant_id')->nullable()->index(); // NULL = ảnh cấp product
            $table->string('disk', 30)->default('public');
            $table->string('path', 500);
            $table->string('file_name')->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->tinyInteger('is_primary')->default(0);
            $table->timestamps();
            $table->index(['product_id', 'sort_order']);
            //Rule: variant.image NULL -> fallback ảnh primary của product
            //Rule: 1 primary per product (enforce app/DB trigger)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};

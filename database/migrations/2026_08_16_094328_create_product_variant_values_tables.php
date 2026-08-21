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
        Schema::create('product_variant_values', function (Blueprint $table) {
            $table->unsignedBigInteger('product_variant_id')->index();
            $table->unsignedBigInteger('attribute_value_id')->index();
            $table->primary(['product_variant_id', 'attribute_value_id']);
            $table->unique(['product_variant_id', 'attribute_value_id'], 'uq_variant_combo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_values');
    }
};

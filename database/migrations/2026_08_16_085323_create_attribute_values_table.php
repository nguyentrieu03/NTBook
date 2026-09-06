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
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attribute_id');
            $table->string('value', 120); // hiển thị: "M", "Vàng", .etc 
            $table->string('normalized_value', 120); // lưu: "m", "vang", .etc (để dedup)
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->unique(['attribute_id', 'normalized_value']);
            $table->index(['attribute_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_values');
    }
};

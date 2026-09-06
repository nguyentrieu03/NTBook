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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id')->nullable()->index();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->integer('level')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->integer('is_active')->default(1);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            $table->index(['is_active', 'sort_order', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

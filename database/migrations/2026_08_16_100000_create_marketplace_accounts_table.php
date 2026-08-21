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
        Schema::create('marketplace_accounts', function (Blueprint $table) {
            $table->id();
            $table->enum('platform', ['ebay'])->default('ebay')->index();
            $table->string('name', 120);
            $table->string('username', 80);
            $table->enum('site_code', ['EBAY_US', 'EBAY_GB', 'EBAY_AU', 'EBAY_DE']);
            $table->enum('health_status', ['ok', 'warn', 'err'])->default('ok')->index();
            $table->tinyInteger('sync_enabled')->default(1);
            $table->tinyInteger('is_active')->default(1)->index();
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['platform', 'username', 'site_code'], 'uq_marketplace_accounts_platform_user_site');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_accounts');
    }
};

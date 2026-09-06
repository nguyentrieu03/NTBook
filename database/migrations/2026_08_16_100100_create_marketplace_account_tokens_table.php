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
        Schema::create('marketplace_account_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marketplace_account_id')->unique();
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->string('token_type', 30)->nullable();
            $table->string('scope', 255)->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_account_tokens');
    }
};

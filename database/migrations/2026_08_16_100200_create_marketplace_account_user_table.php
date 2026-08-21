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
        Schema::create('marketplace_account_user', function (Blueprint $table) {
            $table->unsignedBigInteger('marketplace_account_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->primary(['marketplace_account_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_account_user');
    }
};

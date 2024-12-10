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
        Schema::create('paybill_wallets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('shortcode');
            $table->string('initiator');
            $table->text('SecurityCredential')->nullable();
            $table->string('key');
            $table->string('secret');
            $table->string('passkey');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paybill_wallets');
    }
};

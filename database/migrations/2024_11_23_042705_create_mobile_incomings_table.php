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
        Schema::create('mobile_incomings', function (Blueprint $table) {
            $table->id();
            $table->string('csp');
            $table->string('type'); //ussd or senderid or shortcode
            $table->string('shortcode');
            $table->text('api_pass')->nullable();
            $table->string('api_user')->nullable();
            $table->string('api_url')->nullable();
            $table->string('api_key')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_incomings');
    }
};

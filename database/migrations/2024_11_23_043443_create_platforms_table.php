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
        Schema::create('platforms', function (Blueprint $table) {
            $table->id();
            $table->string('platform');
            $table->integer('mobile_incoming');
            $table->integer('mobile_outgoing');
            $table->integer('b2c_wallet');
            $table->integer('paybill_wallet');
            $table->integer('bet_minimum')->default(40);
            $table->integer('bet_maximum');
            $table->integer('win_ratio');
            $table->integer('win_maximum');
            $table->integer('win_minimum');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platforms');
    }
};

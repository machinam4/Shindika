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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->string("MerchantRequestID")->nullable();
            $table->string("CheckoutRequestID")->nullable();
            $table->string("ResultCode")->nullable();
            $table->string("TransactionType")->nullable();
            $table->string("TransID")->nullable();
            $table->dateTime("TransTime")->nullable();
            $table->integer("TransAmount")->nullable();
            $table->string("BusinessShortCode")->nullable();
            $table->string("BillRefNumber")->nullable();
            $table->string("InvoiceNumber")->nullable();
            $table->integer("OrgAccountBalance")->nullable();
            $table->string("ThirdPartyTransID")->nullable();
            $table->string("MSISDN")->nullable();
            $table->string("FirstName")->nullable();
            $table->string("MiddleName")->nullable();
            $table->string("LastName")->nullable();
            $table->string("SmsShortcode")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};

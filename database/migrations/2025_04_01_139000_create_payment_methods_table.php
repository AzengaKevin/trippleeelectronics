<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('phone_number')->nullable();
            $table->string('paybill_number')->nullable();
            $table->string('account_number')->nullable();
            $table->string('till_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('default_payment_status')->nullable();
            $table->text('description')->nullable();
            $table->jsonb('required_fields')->nullable();
            $table->jsonb('properties')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};

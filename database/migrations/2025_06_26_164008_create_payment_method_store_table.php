<?php

use App\Models\PaymentMethod;
use App\Models\Store;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_method_store', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Store::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(PaymentMethod::class)->constrained()->cascadeOnDelete();
            $table->string('phone_number')->nullable();
            $table->string('paybill_number')->nullable();
            $table->string('account_number')->nullable();
            $table->string('till_number')->nullable();
            $table->string('account_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_method_store');
    }
};

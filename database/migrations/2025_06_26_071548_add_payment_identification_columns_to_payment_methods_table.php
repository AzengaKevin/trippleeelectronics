<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('phone_number')->nullable();
            $table->string('paybill_number')->nullable();
            $table->string('account_number')->nullable();
            $table->string('till_number')->nullable();
            $table->string('account_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('phone_number');
            $table->dropColumn('paybill_number');
            $table->dropColumn('account_number');
            $table->dropColumn('till_number');
            $table->dropColumn('account_name');
        });
    }
};

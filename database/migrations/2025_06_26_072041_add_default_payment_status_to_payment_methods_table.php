<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('default_payment_status')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {

            $table->dropColumn('default_payment_status');
        });
    }
};

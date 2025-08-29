<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->boolean('taxable')->nullable();
            $table->decimal('tax_rate', 5, 2)->nullable()->default(0);
            $table->decimal('discount_rate', 5, 2)->nullable()->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('taxable');
            $table->dropColumn('tax_rate');
            $table->dropColumn('discount_rate');
        });
    }
};

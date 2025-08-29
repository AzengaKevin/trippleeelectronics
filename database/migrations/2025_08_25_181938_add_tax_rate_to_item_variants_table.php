<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_variants', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->nullable()->default(0.00)->after('price')->comment('Tax rate as a percentage');
        });
    }

    public function down(): void
    {
        Schema::table('item_variants', function (Blueprint $table) {
            $table->dropColumn('tax_rate');
        });
    }
};

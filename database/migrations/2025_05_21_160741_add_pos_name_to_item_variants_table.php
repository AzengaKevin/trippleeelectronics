<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_variants', function (Blueprint $table) {
            $table->string('pos_name')->nullable()->unique()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('item_variants', function (Blueprint $table) {
            $table->dropColumn('pos_name');
        });
    }
};

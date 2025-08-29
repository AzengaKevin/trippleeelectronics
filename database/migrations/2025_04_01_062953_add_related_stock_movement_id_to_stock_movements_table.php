<?php

use App\Models\StockMovement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignIdFor(StockMovement::class, 'related_stock_movement_id')
                ->nullable()
                ->after('store_id')
                ->constrained('stock_movements', 'id')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignIdFor(StockMovement::class, 'related_stock_movement_id');
        });
    }
};

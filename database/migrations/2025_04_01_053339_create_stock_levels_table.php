<?php

use App\Models\Store;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_levels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Store::class)->nullable()->constrained()->nullOnDelete();
            $table->uuidMorphs('stockable');
            $table->integer('quantity')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_levels');
    }
};

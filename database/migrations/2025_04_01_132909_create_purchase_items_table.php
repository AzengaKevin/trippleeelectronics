<?php

use App\Models\Purchase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Purchase::class)->constrained()->cascadeOnDelete();
            $table->uuidMorphs('item');
            $table->integer('quantity')->default(1);
            $table->decimal('cost', 10, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};

<?php

use App\Models\Quotation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Quotation::class)->constrained()->cascadeOnDelete();
            $table->uuidMorphs('item');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->boolean('taxable')->nullable()->default(false);
            $table->decimal('tax_rate', 5, 2)->nullable()->default(0);
            $table->decimal('discount_rate', 5, 2)->nullable()->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};

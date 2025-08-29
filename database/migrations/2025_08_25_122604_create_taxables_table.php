<?php

use App\Models\Tax;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Tax::class)->constrained()->cascadeOnDelete();
            $table->uuidMorphs('taxable');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxables');
    }
};

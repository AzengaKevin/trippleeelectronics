<?php

use App\Models\Building;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('floors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Building::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('active')->nullable()->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('floors');
    }
};

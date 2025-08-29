<?php

use App\Models\Jurisdiction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Jurisdiction::class)->nullable()->constrained()->nullOnUpdate();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('rate', 10, 2);
            $table->string('type')->nullable();
            $table->boolean('is_compound')->default(false);
            $table->boolean('is_inclusive')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};

<?php

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class, 'author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(Store::class)->nullable()->constrained()->nullOnDelete();
            $table->nullableUuidMorphs('stockable');
            $table->nullableUuidMorphs('action');
            $table->string('type');
            $table->integer('quantity');
            $table->longText('description')->nullable();
            $table->decimal('cost_implication', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};

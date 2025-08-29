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
        Schema::create('purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class, 'author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(Store::class)->nullable()->constrained()->nullOnDelete();
            $table->string('reference')->nullable();
            $table->nullableUuidMorphs('supplier');
            $table->decimal('amount', 15, 2)->nullable();
            $table->decimal('shipping_amount', 15, 2)->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};

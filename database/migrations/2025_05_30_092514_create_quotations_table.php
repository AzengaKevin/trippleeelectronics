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
        Schema::create('quotations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference');
            $table->nullableUuidMorphs('customer');
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(User::class, 'author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(Store::class)->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('shipping_amount', 10, 2)->nullable();
            $table->decimal('tax_amount', 10, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};

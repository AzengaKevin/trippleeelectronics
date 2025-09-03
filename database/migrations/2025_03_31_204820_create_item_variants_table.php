<?php

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class, 'author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(Item::class)->nullable()->constrained()->nullOnDelete();
            $table->string('barcode')->nullable()->unique();
            $table->string('image_url')->nullable();
            $table->string('attribute');
            $table->string('value');
            $table->string('sku')->unique();
            $table->string('name')->unique();
            $table->string('pos_name')->nullable()->unique();
            $table->string('slug')->unique();
            $table->decimal('cost', 10, 2);
            $table->decimal('price', 10, 2);
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->longText('description')->nullable();
            $table->string('pos_description')->nullable();
            $table->decimal('tax_rate', 5, 2)->nullable();
            $table->unsignedBigInteger('quantity')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_variants');
    }
};

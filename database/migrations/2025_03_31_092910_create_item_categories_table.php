<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class, 'author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('parent_id')->nullable();
            $table->unsignedInteger('_lft')->nullable();
            $table->unsignedInteger('_rgt')->nullable();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->unsignedInteger('items_count_manual')->nullable();
            $table->boolean('featured')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_categories');
    }
};

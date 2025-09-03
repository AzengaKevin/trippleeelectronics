<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class, 'author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name')->unique();
            $table->string('short_name')->nullable()->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('paybill')->nullable();
            $table->string('account_number')->nullable();
            $table->string('till')->nullable();
            $table->string('kra_pin')->nullable();
            $table->string('address')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};

<?php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('individuals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class, 'author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(User::class)->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignIdFor(Organization::class)->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('kra_pin')->nullable();
            $table->string('id_number')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('individuals');
    }
};

<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('threads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->foreignIdFor(User::class, 'author_user_id')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->foreignIdFor(Group::class)->nullable()->unique()->constrained()->nullOnDelete();
            $table->dateTime('last_updated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('threads');
    }
};

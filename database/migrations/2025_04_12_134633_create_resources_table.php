<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name')->unique();
            $table->string('route_name')->nullable()->unique();
            $table->string('icon')->nullable();
            $table->tinyInteger('order')->nullable();
            $table->longText('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->bigInteger('count')->nullable();
            $table->string('required_permission')->nullable();
            $table->string('morph_class')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};

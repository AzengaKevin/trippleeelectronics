<?php

use App\Models\Individual;
use App\Models\Property;
use App\Models\SourceChannel;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class, 'author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(Property::class)->nullable()->constrained('properties')->nullOnDelete();
            $table->foreignIdFor(Individual::class, 'primary_individual_id')->nullable()->constrained('individuals')->nullOnDelete();
            $table->foreignIdFor(SourceChannel::class)->nullable()->constrained('source_channels')->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('status')->nullable();
            $table->date('checkin_date')->nullable();
            $table->date('checkout_date')->nullable();
            $table->integer('guests_count')->nullable();
            $table->integer('rooms_count')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->decimal('tendered_amount', 10, 2)->nullable();
            $table->decimal('balance_amount', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};

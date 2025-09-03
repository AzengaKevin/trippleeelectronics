<?php

use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class, 'author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(Payment::class)->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('transaction_method')->nullable();
            $table->string('status')->default('pending');
            $table->string('till')->nullable();
            $table->string('paybill')->nullable();
            $table->string('account_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('reference')->nullable();
            $table->string('local_reference')->nullable();
            $table->decimal('fee', 10, 2)->nullable();
            $table->jsonb('data')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

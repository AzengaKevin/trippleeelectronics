<?php

use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class, 'author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(AccountingPeriod::class, 'accounting_period_id')->constrained('accounting_periods')->nullOnDelete();
            $table->foreignIdFor(Account::class, 'account_id')->constrained('accounts')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_entries');
    }
};

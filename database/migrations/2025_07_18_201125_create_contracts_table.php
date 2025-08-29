<?php

use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Employee::class)->constrained()->cascadeOnDelete();
            $table->string('contract_type');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('salary', 10, 2);
            $table->string('status')->default('active');
            $table->jsonb('responsibilities')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};

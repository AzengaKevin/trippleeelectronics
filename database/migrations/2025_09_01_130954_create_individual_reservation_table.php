<?php

use App\Models\Individual;
use App\Models\Reservation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('individual_reservation', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Individual::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Reservation::class)->constrained()->cascadeOnDelete();
            $table->string('role')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('individual_reservation');
    }
};

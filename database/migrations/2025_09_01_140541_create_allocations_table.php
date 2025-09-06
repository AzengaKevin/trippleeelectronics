<?php

use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Reservation::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(RoomType::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Room::class)->nullable()->constrained()->cascadeOnDelete();
            $table->date('date')->nullable();
            $table->dateTime('start')->nullable();
            $table->dateTime('end')->nullable();
            $table->integer('occupants')->nullable();
            $table->string('status')->nullable();
            $table->string('discount')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allocations');
    }
};

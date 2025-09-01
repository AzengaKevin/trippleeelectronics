<?php

use App\Models\Amenity;
use App\Models\Room;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amenity_room', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Amenity::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Room::class)->constrained()->cascadeOnDelete();
            $table->unique(['amenity_id', 'room_id']);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amenity_room');
    }
};

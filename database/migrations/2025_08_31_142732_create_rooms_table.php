<?php

use App\Models\Building;
use App\Models\Floor;
use App\Models\Property;
use App\Models\RoomType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Property::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Building::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Floor::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(RoomType::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->integer('occupancy')->nullable();
            $table->boolean('active')->nullable()->default(true);
            $table->decimal('price', 10, 2)->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};

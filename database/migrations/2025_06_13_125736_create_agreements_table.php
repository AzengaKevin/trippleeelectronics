<?php

use App\Models\Enums\AgreementStatus;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agreements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class, 'author_user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Store::class)->nullable()->constrained()->nullOnDelete();
            $table->uuidMorphs('client');
            $table->nullableUuidMorphs('agreementable');
            $table->longText('content')->nullable();
            $table->string('status')->default(AgreementStatus::PENDING->value);
            $table->dateTime('since')->nullable();
            $table->dateTime('until')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('terminated_at')->nullable();
            $table->dateTime('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreements');
    }
};

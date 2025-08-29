<?php

namespace App\Models;

use App\Models\Enums\ContractStatus;
use App\Models\Enums\EmployeeDocument;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Employee extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia, LogsActivity, Searchable, SoftDeletes;

    public $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'position',
        'department',
        'identification_number',
        'kra_pin',
        'hire_date',
    ];

    protected function casts()
    {
        return [
            'hire_date' => 'date:Y-m-d',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function suspensions(): HasMany
    {
        return $this->hasMany(Suspension::class);
    }

    public function isCurrentlySuspended()
    {
        return $this->suspensions()
            ->whereDate('from', '<=', now())
            ->where(fn ($q) => $q->whereNull('to')->orWhereDate('to', '>=', now()))->exists();
    }

    public function isOutisOutOfContract()
    {
        return $this->contracts()
            ->whereDate('start_date', '<=', now())
            ->where('status', ContractStatus::ACTIVE->value)
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', now()))->doesntExist();
    }

    public function toSearchableArray()
    {
        return $this->only('name', 'email', 'phone', 'position', 'department');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(EmployeeDocument::ID->value);
        $this->addMediaCollection(EmployeeDocument::GOOD_CONDUCT->value);
        $this->addMediaCollection(EmployeeDocument::KRA_PIN->value);
        $this->addMediaCollection(EmployeeDocument::RESUME->value);
        $this->addMediaCollection(EmployeeDocument::QUALIFICATION->value);
    }
}

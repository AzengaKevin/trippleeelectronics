<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasAuthor, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'property_id',
        'primary_individual_id',
        'source_channel_id',
        'status',
        'checkin_date',
        'checkout_date',
        'adults',
        'children',
        'infants',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reservation) {

            $reservation->reference = self::generateReservationReference($reservation->source_channel_id);
        });
    }

    public static function generateReservationReference($channel = null)
    {
        $now = now();

        $prefix = str($now->format('ym'))
            ->append($channel ? strtoupper($channel[0]) : 'X')
            ->append($now->format('d'))
            ->value();

        $lastReservation = Reservation::query()->withTrashed()->where('reference', 'like', $prefix.'%')
            ->orderByDesc('reference')
            ->first();

        if ($lastReservation) {
            $lastNumber = (int) substr($lastReservation->reference, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $reservationCount = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return $prefix.$reservationCount;
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function primaryIndividual(): BelongsTo
    {
        return $this->belongsTo(Individual::class, 'primary_individual_id');
    }

    public function sourceChannel(): BelongsTo
    {
        return $this->belongsTo(SourceChannel::class);
    }

    public function individuals(): BelongsToMany
    {
        return $this->belongsToMany(Individual::class, 'individual_reservation')
            ->using(IndividualReservation::class)
            ->withTimestamps()
            ->withPivot('role');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class);
    }
}

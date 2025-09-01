<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CheckEvent extends Model
{
    use HasAuthor, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'reservation_id',
        'event_type',
        'author_user_id',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}

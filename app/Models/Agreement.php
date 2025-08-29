<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Agreement extends Model implements HasMedia
{
    use HasAuthor, HasFactory, HasUuids, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'store_id',
        'client_id',
        'client_type',
        'agreementable_id',
        'agreementable_type',
        'content',
        'status',
        'since',
        'until',
        'approved_at',
        'rejected_at',
        'expires_at',
        'terminated_at',
        'archived_at',
    ];

    protected function casts()
    {
        return [
            'since' => 'date:Y-m-d',
            'until' => 'date:Y-m-d',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'expires_at' => 'datetime',
            'terminated_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function agreementable(): MorphTo
    {
        return $this->morphTo();
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function client(): MorphTo
    {
        return $this->morphTo();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Message extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia, Searchable;

    protected $fillable = [
        'user_id',
        'thread_id',
        'message',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function userReads(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'message_read')
            ->using(MessageRead::class)
            ->withTimestamps();
    }

    public function toSearchableArray()
    {
        return $this->only(['message']);
    }
}

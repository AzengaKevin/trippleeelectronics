<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Individual extends Model implements HasMedia
{
    use HasAuthor, HasFactory, HasUuids, InteractsWithMedia, Searchable, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'user_id',
        'organization_id',
        'name',
        'username',
        'email',
        'phone',
        'address',
        'kra_pin',
        'id_number',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function orders(): MorphMany
    {
        return $this->morphMany(Order::class, 'customer');
    }

    public function quotations(): MorphMany
    {
        return $this->morphMany(Quotation::class, 'customer');
    }

    public function toSearchableArray()
    {
        return $this->only([
            'name',
            'username',
            'email',
            'phone',
            'address',
            'kra_pin',
            'id_number',
        ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Contain, 320, 320)
            ->nonQueued();
    }

    public static function getExportFilename(): string
    {
        return str('individuals')
            ->append('-')
            ->append(now()->format('Y-m-d'))
            ->append('.xlsx')
            ->value();
    }
}

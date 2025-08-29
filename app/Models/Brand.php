<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Brand extends Model implements HasMedia
{
    use HasAuthor, HasFactory, HasUniqueSlug, HasUuids, InteractsWithMedia, Searchable, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'name',
        'slug',
        'description',
        'items_count_manual',
    ];

    protected function casts()
    {
        return [
            'items_count_manual' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function toSearchableArray()
    {
        return $this->only('name', 'slug', 'description');
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
        return str('brands')
            ->append('-')
            ->append(now()->format('Y-m-d'))
            ->append('.xlsx')
            ->value();
    }
}

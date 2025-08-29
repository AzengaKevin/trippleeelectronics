<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use App\Models\Enums\OrientationOption;
use App\Models\Enums\PositionOption;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Carousel extends Model implements HasMedia
{
    use HasAuthor, HasFactory, HasUuids, InteractsWithMedia, Searchable, Searchable, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'orientation',
        'position',
        'link',
        'title',
        'description',
        'active',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            $model->slug = static::generateUniqueSlug($model->title);

        });

        static::updating(function ($model) {

            if ($model->isDirty('title')) {

                $model->slug = static::generateUniqueSlug($model->title);
            }
        });
    }

    protected static function generateUniqueSlug($title, $modelId = null)
    {
        $slug = str()->slug($title);

        $count = static::where('slug', 'LIKE', "$slug%")
            ->when($modelId, fn ($query) => $query->where('id', '!=', $modelId))
            ->count();

        return $count ? "{$slug}-".($count + 1) : $slug;
    }

    protected function casts()
    {
        return [
            'orientation' => OrientationOption::class,
            'position' => PositionOption::class,
        ];
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
        return str('carousels')->append('-', now()->toDateString())->append('.xlsx');
    }

    public function toSearchableArray()
    {
        return $this->only([
            'orientation',
            'position',
            'title',
            'slug',
            'description',
        ]);
    }
}

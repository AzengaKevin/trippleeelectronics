<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Resource extends Model implements HasMedia
{
    use HasAuthor, HasFactory, InteractsWithMedia, Searchable, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'name',
        'route_name',
        'icon',
        'order',
        'description',
        'is_active',
        'count',
        'required_permission',
        'morph_class',
    ];

    protected function casts()
    {
        return [
            'order' => 'integer',
            'is_active' => 'boolean',
            'count' => 'integer',
        ];
    }

    public function toSearchableArray()
    {
        return $this->only([
            'name',
            'route_name',
            'icon',
            'description',
            'required_permission',
            'morph_class',
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
        return str('resources')
            ->append('-')
            ->append(now()->format('Y-m-d'))
            ->append('.xlsx')
            ->value();
    }
}

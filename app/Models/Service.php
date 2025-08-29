<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Service extends Model implements HasMedia
{
    use HasAuthor, HasFactory, HasUuids, InteractsWithMedia, Searchable, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'title',
        'description',
    ];

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Contain, 320, 320)
            ->nonQueued();
    }

    public function toSearchableArray()
    {
        return $this->only(['title', 'description']);
    }

    public static function getExportFilename(): string
    {
        return str('services')->append('-', now()->toDateString())->append('.xlsx');
    }
}

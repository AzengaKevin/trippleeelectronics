<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\NodeTrait;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ItemCategory extends Model implements HasMedia
{
    use HasAuthor, HasFactory, HasUniqueSlug, HasUuids, InteractsWithMedia, NodeTrait, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'items_count_manual',
        'featured',
    ];

    protected function casts()
    {
        return [
            'items_count_manual' => 'integer',
            'featured' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Contain, 320, 320)
            ->nonQueued();
    }

    public static function getExportFilename()
    {
        return str('item-categories')->append('-', now()->toDateString())->append('.xlsx');
    }
}

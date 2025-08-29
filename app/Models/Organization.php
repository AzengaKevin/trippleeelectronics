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

class Organization extends Model implements HasMedia
{
    use HasAuthor, HasFactory, HasUuids, InteractsWithMedia, Searchable, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'organization_category_id',
        'name',
        'email',
        'phone',
        'address',
        'kra_pin',
    ];

    public function organizationCategory()
    {
        return $this->belongsTo(OrganizationCategory::class);
    }

    public function orders()
    {
        return $this->morphMany(Order::class, 'customer');
    }

    public function quotations()
    {
        return $this->morphMany(Quotation::class, 'customer');
    }

    public function toSearchableArray()
    {
        return $this->only([
            'name',
            'email',
            'phone',
            'address',
            'kra_pin',
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
        return str('organizations')
            ->append('-')
            ->append(now()->format('Y-m-d'))
            ->append('.xlsx')
            ->value();
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Item extends Model implements HasMedia
{
    use HasAuthor, HasFactory, HasUniqueSlug, HasUuids, InteractsWithMedia, Searchable, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'item_category_id',
        'brand_id',
        'barcode',
        'image_url',
        'sku',
        'name',
        'pos_name',
        'cost',
        'price',
        'selling_price',
        'description',
        'pos_description',
        'quantity',
        'tax_rate',
    ];

    protected function casts()
    {
        return [
            'cost' => 'decimal:2',
            'price' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            $model->sku = static::generateUniqueSKU($model->name, $model->item_category_id);

            $model->slug = static::generateUniqueSlug($model->name);
        });

        static::updating(function ($model) {
            if ($model->isDirty('name')) {
                $model->slug = static::generateUniqueSlug($model->name, $model->id);
            }
        });
    }

    // Generate a unique SKU
    protected static function generateUniqueSKU($name, $categoryId)
    {
        $sku = str(strtoupper(substr(str_replace(' ', '', $name), 0, 3)))->append('-')->append(substr($categoryId, -4))->append('-')->append(mt_rand(1000, 9999))->upper()->value();

        if (static::where('sku', $sku)->exists()) {

            return static::generateUniqueSKU($name, $categoryId);
        }

        return $sku;
    }

    protected static function generateUniqueSlug($name, $categoryId = null)
    {
        $slug = str()->slug($name);

        $count = static::where('slug', 'LIKE', "$slug%")
            ->when($categoryId, fn ($query) => $query->where('id', '!=', $categoryId))
            ->count();

        return $count ? "{$slug}-".($count + 1) : $slug;
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ItemVariant::class);
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'stockable');
    }

    public function purchaseItems(): MorphMany
    {
        return $this->morphMany(PurchaseItem::class, 'item');
    }

    public function orderItems(): MorphMany
    {
        return $this->morphMany(OrderItem::class, 'item');
    }

    public function taxes(): MorphToMany
    {
        return $this->morphToMany(Tax::class, 'taxable')->using(Taxable::class)->withTimestamps();
    }

    public function toSearchableArray()
    {
        return $this->only(
            'sku',
            'name',
            'cost',
            'price',
            'description',
            'quantity',
        );
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
        return str('items')
            ->append('-')
            ->append(now()->format('Y-m-d'))
            ->append('.xlsx')
            ->value();
    }
}

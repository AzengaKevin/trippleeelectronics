<?php

namespace App\Models\Concerns;

trait HasUniqueSlug
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->slug = static::generateUniqueSlug($model->name);
        });

        static::updating(function ($model) {
            if ($model->isDirty('name')) {
                $model->slug = static::generateUniqueSlug($model->name, $model->id);
            }
        });
    }

    protected static function generateUniqueSlug($name, $modelId = null)
    {
        $slug = str()->slug($name);

        $count = static::where('slug', 'LIKE', "$slug%")
            ->when($modelId, fn ($query) => $query->where('id', '!=', $modelId))
            ->count();

        return $count ? "{$slug}-".($count + 1) : $slug;
    }
}

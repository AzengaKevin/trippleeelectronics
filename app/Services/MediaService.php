<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaService
{
    public function get(
        ?string $query = null,
        ?array $with = null,
        ?int $perPage = 96,
        ?int $limit = null,
        ?Model $model = null,
    ) {

        $mediaQuery = Media::query();

        $mediaQuery->when($query, function ($innerQuery, $query) {

            $innerQuery->where('name', 'like', "%{$query}%");
        });

        $mediaQuery->when($model, function ($innerQuery, $model) {

            $innerQuery->where('model_id', $model->id);
        });

        $mediaQuery->when($with, function ($innerQuery, $with) {

            $innerQuery->with($with);
        });

        $mediaQuery->when($limit, function ($innerQuery, $limit) {

            $innerQuery->limit($limit);
        });

        return is_null($perPage)
            ? $mediaQuery->get()
            : $mediaQuery->paginate($perPage)->withQueryString();
    }

    public function delete(Media $media)
    {
        return DB::transaction(function () use ($media) {

            return $media->delete();
        });
    }
}

<?php

namespace App\Services;

use App\Models\Carousel;
use Illuminate\Support\Facades\DB;

class CarouselService
{
    public function get(
        ?string $query = null,
        ?string $position = null,
        ?string $orientation = null,
        ?int $limit = null,
        ?array $with = null,
        ?int $perPage = 24,
    ) {

        $carouselQuery = Carousel::search($query, function ($defaultQuery) use ($limit, $with, $position, $orientation) {
            $defaultQuery->when($limit, function ($innerQuery, $limit) {
                $innerQuery->limit($limit);
            });
            $defaultQuery->when($with, function ($innerQuery, $with) {
                $innerQuery->with($with);
            });

            $defaultQuery->when($position, function ($innerQuery, $position) {
                $innerQuery->where('position', $position);
            });

            $defaultQuery->when($orientation, function ($innerQuery, $orientation) {
                $innerQuery->where('orientation', $orientation);
            });
        });

        return is_null($perPage)
            ? $carouselQuery->get()
            : $carouselQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $attributes = [
                'author_user_id' => data_get($data, 'author_user_id'),
                'orientation' => data_get($data, 'orientation'),
                'position' => data_get($data, 'position'),
                'link' => data_get($data, 'link'),
                'title' => data_get($data, 'title'),
                'description' => data_get($data, 'description'),
                'active' => data_get($data, 'active', true),
            ];

            $carousel = Carousel::query()->create($attributes);

            if ($image = data_get($data, 'image')) {
                $carousel->addMedia($image)->preservingOriginal()->toMediaCollection();
            }

            return $carousel->fresh();
        });
    }

    public function update(Carousel $carousel, array $data): bool
    {
        return DB::transaction(function () use ($carousel, $data) {
            $carousel->update([
                'orientation' => data_get($data, 'orientation'),
                'position' => data_get($data, 'position'),
                'link' => data_get($data, 'link'),
                'title' => data_get($data, 'title'),
                'description' => data_get($data, 'description'),
                'active' => data_get($data, 'active', $carousel->active),
            ]);

            if ($image = data_get($data, 'image')) {
                $carousel->clearMediaCollection();
                $carousel->addMedia($image)->preservingOriginal()->toMediaCollection();
            }

            return true;
        });
    }

    public function delete(Carousel $carousel, bool $destroy = false)
    {
        if ($destroy) {
            $carousel->clearMediaCollection();
            $carousel->forceDelete();
        } else {
            $carousel->delete();
        }
    }

    public function importRow(array $data)
    {

        $attributes = [
            'title' => data_get($data, 'title'),
        ];

        $values = [
            'orientation' => data_get($data, 'orientation'),
            'position' => data_get($data, 'position'),
            'link' => data_get($data, 'link'),
            'description' => data_get($data, 'description'),
            'active' => boolval(data_get($data, 'active', false)),
        ];

        Carousel::query()->updateOrCreate(
            $attributes,
            $values
        );
    }
}

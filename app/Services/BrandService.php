<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Support\Facades\DB;

class BrandService
{
    public function get(
        ?string $query = null,
        ?int $limit = null,
        ?int $perPage = 24,
    ) {

        $brandQuery = Brand::search($query, function ($defaultQuery) use ($limit) {
            $defaultQuery->when($limit, function ($query) use ($limit) {
                $query->limit($limit);
            });
        });

        return is_null($perPage)
            ? $brandQuery->get()
            : $brandQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Brand
    {
        return DB::transaction(function () use ($data) {

            $attributes = [
                'author_user_id' => data_get($data, 'author_user_id'),
                'name' => data_get($data, 'name'),
                'description' => data_get($data, 'description'),
            ];

            $brand = Brand::query()->create($attributes);

            if ($image = data_get($data, 'image')) {

                $brand->addMedia($image)->preservingOriginal()->toMediaCollection();
            }

            return $brand->fresh();
        });
    }

    public function update(Brand $brand, array $data): bool
    {
        return DB::transaction(function () use ($brand, $data) {

            $attributes = [
                'name' => data_get($data, 'name'),
                'description' => data_get($data, 'description'),
            ];

            $brand->update($attributes);

            if ($image = data_get($data, 'image')) {

                $brand->clearMediaCollection();

                $brand->addMedia($image)->preservingOriginal()->toMediaCollection();
            }

            return true;
        });
    }

    public function delete(Brand $brand, bool $forever = false): bool
    {
        if ($forever) {

            $brand->clearMediaCollection();

            return $brand->forceDelete();
        }

        return $brand->delete();
    }

    public function importRow(array $data)
    {
        $attributes = [
            'name' => data_get($data, 'name'),
        ];

        $values = [
            'id' => data_get($data, 'reference', str()->uuid()) ?? str()->uuid(),
            'slug' => data_get($data, 'slug', str()->slug(data_get($data, 'name'))),
            'description' => data_get($data, 'description'),
            'items_count_manual' => data_get($data, 'items_count_manual', 0) ?? 0,
            'created_at' => data_get($data, 'created_at'),
            'updated_at' => data_get($data, 'updated_at'),
            'deleted_at' => data_get($data, 'deleted_at'),
        ];

        DB::table('brands')->updateOrInsert(
            $attributes,
            $values
        );
    }
}

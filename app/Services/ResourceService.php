<?php

namespace App\Services;

use App\Models\Resource;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ResourceService
{
    public function get(
        ?string $query = null,
        ?int $limit = null,
        ?int $perPage = 24,
        ?string $orderBy = 'order',
        ?string $orderDirection = 'asc',
    ) {
        $resourceQuery = Resource::search($query, function ($defaultQuery) use ($limit) {
            $defaultQuery->when($limit, fn ($query) => $query->limit($limit));
        });

        return is_null($perPage)
            ? $resourceQuery->get()
            : $resourceQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Resource
    {
        return DB::transaction(function () use ($data) {
            $attributes = [
                'author_user_id' => data_get($data, 'author_user_id'),
                'name' => data_get($data, 'name'),
                'route_name' => data_get($data, 'route_name'),
                'icon' => data_get($data, 'icon'),
                'order' => data_get($data, 'order'),
                'description' => data_get($data, 'description'),
                'is_active' => data_get($data, 'is_active', true),
                'count' => data_get($data, 'count', 0),
                'required_permission' => data_get($data, 'required_permission'),
                'morph_class' => data_get($data, 'morph_class'),
            ];

            $resource = Resource::create($attributes)->fresh();

            if ($image = data_get($data, 'image')) {

                $resource->addMedia($image)->preservingOriginal()->toMediaCollection();
            }

            return $resource;
        });
    }

    public function update(Resource $resource, array $data): bool
    {
        return DB::transaction(function () use ($resource, $data) {

            if ($image = data_get($data, 'image')) {

                $resource->addMedia($image)->preservingOriginal()->toMediaCollection();
            }

            $attributes = [
                'name' => data_get($data, 'name'),
                'route_name' => data_get($data, 'route_name'),
                'icon' => data_get($data, 'icon'),
                'order' => data_get($data, 'order'),
                'description' => data_get($data, 'description'),
                'is_active' => data_get($data, 'is_active', true),
                'count' => data_get($data, 'count', 0),
                'required_permission' => data_get($data, 'required_permission'),
                'morph_class' => data_get($data, 'morph_class'),
            ];

            return $resource->update($attributes);
        });
    }

    public function delete(Resource $resource, bool $forever = false): bool
    {
        return $forever
            ? $resource->forceDelete()
            : $resource->delete();
    }

    public function importRow(array $data): void
    {
        $attributes = [
            'name' => data_get($data, 'name'),
        ];

        $values = [
            'route_name' => data_get($data, 'route_name'),
            'icon' => data_get($data, 'icon'),
            'order' => data_get($data, 'order', 0),
            'description' => data_get($data, 'description'),
            'is_active' => data_get($data, 'is_active', true),
            'count' => data_get($data, 'count', 0),
            'required_permission' => data_get($data, 'required_permission'),
            'morph_class' => data_get($data, 'morph_class'),
        ];

        Resource::query()->updateOrCreate($attributes, $values);
    }

    public function getByUser(?User $user = null, ?string $query = null)
    {

        $resources = collect([]);

        if ($user) {

            $permissions = $user->getAllPermissions()->pluck('name')->all();

            $resources = Resource::search($query, fn ($defaultQuery) => $defaultQuery->whereIn('required_permission', $permissions))->orderBy('order')->get();
        }

        return $resources;
    }
}

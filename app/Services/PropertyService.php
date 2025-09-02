<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Support\Facades\DB;

class PropertyService
{
    public function get(
        ?string $query = null,
        ?string $orderBy = 'created_at',
        ?string $orderDirection = 'asc',
        ?int $perPage = 24,
        ?int $limit = null,
        ?array $with = null,
        ?array $withCount = null,
    ) {
        $propertyQuery = Property::search($query, function ($defaultQuery) use ($limit, $with, $withCount) {
            $defaultQuery->when($limit, fn ($query) => $query->limit($limit))
                ->when($with, fn ($query) => $query->with($with))
                ->when($withCount, fn ($query) => $query->withCount($withCount));
        });

        $propertyQuery->orderBy($orderBy, $orderDirection);

        return is_null($perPage)
            ? $propertyQuery->get()
            : $propertyQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $attributes = [
                'name' => data_get($data, 'name'),
                'code' => data_get($data, 'code'),
                'address' => data_get($data, 'address'),
                'active' => data_get($data, 'active', true),
            ];

            return Property::query()->create($attributes);
        });
    }

    public function update(Property $property, array $data)
    {
        return DB::transaction(function () use ($property, $data) {

            $attributes = [
                'name' => data_get($data, 'name', $property->name),
                'code' => data_get($data, 'code', $property->code),
                'address' => data_get($data, 'address', $property->address),
                'active' => data_get($data, 'active', $property->active),
            ];

            return $property->update($attributes);
        });
    }

    public function delete(Property $property)
    {
        return DB::transaction(function () use ($property) {
            return $property->delete();
        });
    }

    public function importRow(array $data)
    {
        $attributes = [
            'name' => data_get($data, 'name'),
            'code' => data_get($data, 'code'),
            'address' => data_get($data, 'address'),
            'active' => data_get($data, 'active', true),
        ];

        return Property::query()->create($attributes);
    }
}

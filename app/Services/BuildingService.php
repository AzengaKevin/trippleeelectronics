<?php

namespace App\Services;

use App\Models\Building;
use App\Models\Property;
use Illuminate\Support\Facades\DB;

class BuildingService
{
    public function get(
        ?string $query = null,
        ?Property $property = null,
        ?array $with = null,
        ?array $withCount = null,
        ?int $limit = null,
        ?int $perPage = 24,
        string $orderBy = 'created_at',
        string $orderDirection = 'desc',
    ) {

        $buildingQuery = Building::search($query, function ($defaultQuery) use ($with, $withCount, $limit, $property) {

            $defaultQuery->when($with, fn ($q) => $q->with($with))
                ->when($withCount, fn ($q) => $q->withCount($withCount))
                ->when($limit, fn ($q) => $q->take($limit))
                ->when($property, fn ($q) => $q->where('property_id', $property->id));
        });

        $buildingQuery->orderBy($orderBy, $orderDirection);

        return is_null($perPage)
            ? $buildingQuery->get()
            : $buildingQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Building
    {

        return DB::transaction(function () use ($data) {

            $attributes = [
                'property_id' => data_get($data, 'property'),
                'name' => data_get($data, 'name'),
                'code' => data_get($data, 'code'),
                'active' => data_get($data, 'active', true),
            ];

            return Building::query()->create($attributes);
        });
    }

    public function update(Building $building, array $data): bool
    {

        return DB::transaction(function () use ($building, $data) {

            $attributes = [
                'property_id' => data_get($data, 'property', $building->property_id),
                'name' => data_get($data, 'name', $building->name),
                'code' => data_get($data, 'code', $building->code),
                'active' => data_get($data, 'active', $building->active),
            ];

            return $building->update($attributes);
        });
    }

    public function delete(Building $building): bool
    {

        return DB::transaction(function () use ($building) {

            return $building->delete();
        });
    }

    public function importRow(array $data): Building
    {
        $property = Property::where('code', data_get($data, 'property_code'))->first();

        $attributes = [
            'property_id' => $property?->id,
            'name' => data_get($data, 'name'),
            'code' => data_get($data, 'code'),
            'active' => boolval(data_get($data, 'active')),
        ];

        return Building::query()->create($attributes);
    }
}

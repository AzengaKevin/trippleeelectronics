<?php

namespace App\Services;

use App\Models\Tax;
use Illuminate\Support\Facades\DB;

class TaxService
{
    public function get(
        ?string $query = null,
        ?array $with = null,
        ?array $withCount = null,
        ?int $limit = null,
        ?int $perPage = 24,
        ?string $orderBy = 'created_at',
        ?string $orderDirection = 'desc',
    ) {

        $taxQuery = Tax::search($query, function ($defaultQuery) use ($with, $withCount, $limit) {

            $defaultQuery->when($with, function ($q) use ($with) {
                $q->with($with);
            });
            $defaultQuery->when($withCount, function ($q) use ($withCount) {
                $q->withCount($withCount);
            });

            $defaultQuery->when($limit, function ($q) use ($limit) {
                $q->take($limit);
            });
        });

        $taxQuery->orderBy($orderBy, $orderDirection);

        return is_null($perPage)
            ? $taxQuery->get()
            : $taxQuery->paginate($perPage);
    }

    public function create(array $data): Tax
    {
        return DB::transaction(function () use ($data) {

            $attributes = [
                'jurisdiction_id' => data_get($data, 'jurisdiction'),
                'name' => data_get($data, 'name'),
                'description' => data_get($data, 'description'),
                'rate' => data_get($data, 'rate'),
                'is_compound' => data_get($data, 'is_compound'),
                'is_inclusive' => data_get($data, 'is_inclusive'),
            ];

            return Tax::create($attributes);
        });
    }

    public function update(Tax $tax, array $data): bool
    {
        return DB::transaction(function () use ($tax, $data) {

            $attributes = [
                'jurisdiction_id' => data_get($data, 'jurisdiction'),
                'name' => data_get($data, 'name'),
                'description' => data_get($data, 'description'),
                'rate' => data_get($data, 'rate'),
                'is_compound' => data_get($data, 'is_compound'),
                'is_inclusive' => data_get($data, 'is_inclusive'),
            ];

            return $tax->update($attributes);
        });
    }

    public function delete(Tax $tax): bool
    {
        return DB::transaction(function () use ($tax) {

            return $tax->delete();
        });
    }

    public function fetchPrimaryTax(): Tax
    {
        $attributes = [
            'name' => 'VAT',
        ];

        $values = [
            'rate' => 16.00,
        ];

        return Tax::query()->updateOrCreate($attributes, $values);
    }
}

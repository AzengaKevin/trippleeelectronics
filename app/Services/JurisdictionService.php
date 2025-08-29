<?php

namespace App\Services;

use App\Models\Jurisdiction;

class JurisdictionService
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

        $jurisdictionQuery = Jurisdiction::query();

        $jurisdictionQuery->when($query, fn ($q) => $q->where('name', 'like', "%{$query}%"));

        $jurisdictionQuery->when($with, fn ($q) => $q->with($with));

        $jurisdictionQuery->when($withCount, fn ($q) => $q->withCount($withCount));

        $jurisdictionQuery->when($limit, fn ($q) => $q->limit($limit));

        $jurisdictionQuery->orderBy($orderBy, $orderDirection);

        return is_null($perPage)
            ? $jurisdictionQuery->get()
            : $jurisdictionQuery->paginate($perPage);
    }
}

<?php

namespace App\Services;

use App\Models\OrganizationCategory;
use Illuminate\Support\Facades\DB;

class OrganizationCategoryService
{
    public function get(
        ?string $query = null,
        ?int $limit = null,
        ?int $perPage = 24,
    ) {
        $organizationCategoryQuery = OrganizationCategory::search($query, function ($defaultQuery) use ($limit) {
            $defaultQuery->when($limit, function ($query) use ($limit) {
                $query->limit($limit);
            });
        });

        return is_null($perPage)
            ? $organizationCategoryQuery->get()
            : $organizationCategoryQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data): OrganizationCategory
    {
        return DB::transaction(function () use ($data) {

            $attributes = [
                'author_user_id' => data_get($data, 'author_user_id'),
                'name' => data_get($data, 'name'),
            ];

            return OrganizationCategory::create($attributes);
        });
    }

    public function update(OrganizationCategory $organizationCategory, array $data): bool
    {
        return DB::transaction(function () use ($organizationCategory, $data) {

            $attributes = [
                'name' => data_get($data, 'name'),
            ];

            return $organizationCategory->update($attributes);
        });
    }

    public function delete(OrganizationCategory $organizationCategory): bool
    {
        return DB::transaction(function () use ($organizationCategory) {
            return $organizationCategory->delete();
        });
    }

    public function importRow(array $data)
    {
        return DB::transaction(function () use ($data) {

            $attributes = [
                'name' => data_get($data, 'name'),
            ];

            return OrganizationCategory::create($attributes);
        });
    }
}

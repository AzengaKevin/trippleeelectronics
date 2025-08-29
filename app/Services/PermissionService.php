<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    public function __construct() {}

    public function get(
        ?string $query = null,
        ?array $with = null,
        ?int $limit = null,
        string $orderBy = 'name',
        string $orderByDir = 'asc',
        ?int $perPage = 48,
        array $columns = ['*']
    ) {
        $permissionQuery = Permission::query();

        $permissionQuery->when($query, function ($innerQuery, $query) {

            $innerQuery->where('name', 'like', "%{$query}%");
        })->when($with, function ($innerQuery, $with) {

            $innerQuery->with($with);
        })->when($limit, function ($innerQuery, $limit) {

            $innerQuery->limit($limit);
        })->orderBy($orderBy, $orderByDir);

        return is_null($perPage)
            ? $permissionQuery->get(columns: $columns)
            : $permissionQuery->paginate(perPage: $perPage, columns: $columns);
    }

    public function create(array $data)
    {
        $attributes = [
            'name' => data_get($data, 'name'),
        ];

        return Permission::query()->create($attributes);
    }

    public function update(Permission $permission, array $data)
    {
        $attributes = [
            'name' => data_get($data, 'name'),
        ];

        return $permission->update($attributes);
    }

    public function delete(Permission $permission)
    {
        return $permission->delete();
    }

    public function getUserPermissions(?User $user = null)
    {
        if (is_null($user)) {
            return collect([]);
        }

        return $user->getAllPermissions()->pluck('name')->all();

    }
}

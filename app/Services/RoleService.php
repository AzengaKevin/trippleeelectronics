<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function createOrUpdateAdminRole(bool $update = true): Model
    {
        /** @var Role $role */
        $role = Role::query()->firstOrCreate(['name' => 'admin']);

        if ($update) {

            $role->permissions()->sync(Permission::query()->pluck('id')->all());
        }

        return $role;
    }

    public function createOrUpdateStaffRole(bool $update = true): Model
    {

        /** @var Role $role */
        $role = Role::query()->firstOrCreate(['name' => 'staff']);

        if ($update) {

            $permissionNames = [
                'access-pos',
                'access-backoffice',
                'browse-brands',
                'create-brands',
                'browse-individuals',
                'create-individuals',
                'browse-items',
                'create-items',
                'browse-item-categories',
                'browse-orders',
                'create-orders',
                'browse-payments',
                'create-payments',
                'browse-purchases',
                'browse-stock-levels',
                'browse-stock-movements',
                'browse-organization-categories',
                'browse-organizations',
                'browse-services',
                'delete-brands',
                'update-orders',
                'export-orders',
                'transfer-stock',
                'browse-notifications',
                'create-notifications',
                'update-notifications',
                'delete-notifications',
                'browse-reports',
                'print-reports',
                'browse-quotations',
                'create-quotations',
                'browse-messages',
                'create-messages',
                'read-employment',
                'update-employment',
                'browse-contracts',
            ];

            $role->permissions()->sync(Permission::query()->whereIn('name', $permissionNames)->pluck('id')->all());
        }

        return $role;
    }

    public function get(?string $query = null, ?array $with = null, ?array $withCount = null, string $orderBy = 'name', string $orderByDir = 'asc', ?int $perPage = 48)
    {
        $roleQuery = Role::query();

        $roleQuery->when($query, function ($innerQuery, $query) {

            $innerQuery->where('name', 'like', "%{$query}%");
        });

        $roleQuery->when($with, function ($innerQuery, $with) {

            $innerQuery->with($with);
        });

        $roleQuery->when($withCount, function ($innerQuery, $withCount) {

            $innerQuery->withCount($withCount);
        });

        $roleQuery->orderBy($orderBy, $orderByDir);

        return is_null($perPage)
            ? $roleQuery->get()
            : $roleQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Role
    {

        return DB::transaction(function () use ($data) {

            $attributes = [
                'name' => data_get($data, 'name'),
                'description' => data_get($data, 'description'),
            ];

            /** @var Role */
            $role = Role::query()->create($attributes);

            if ($permissions = data_get($data, 'permissions')) {

                $role->permissions()->sync($permissions);
            }

            return $role->fresh();
        });
    }

    public function update(Role $role, array $data)
    {
        return DB::transaction(function () use ($role, $data) {

            if ($permissions = data_get($data, 'permissions')) {

                $role->permissions()->sync($permissions);
            }

            $attributes = [
                'name' => data_get($data, 'name'),
                'description' => data_get($data, 'description'),
            ];

            return $role->update($attributes);
        });
    }

    public function delete(Role $role)
    {
        return $role->delete();
    }
}

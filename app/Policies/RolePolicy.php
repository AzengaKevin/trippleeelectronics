<?php

namespace App\Policies;

use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('browse-roles');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-roles');
    }

    public function import(User $user): bool
    {
        return $user->hasPermissionTo('import-roles');
    }

    public function export(User $user): bool
    {
        return $user->hasPermissionTo('export-roles');
    }
}

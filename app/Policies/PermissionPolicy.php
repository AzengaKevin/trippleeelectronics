<?php

namespace App\Policies;

use App\Models\User;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('browse-permissions');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-permissions');
    }

    public function import(User $user): bool
    {
        return $user->hasPermissionTo('import-permissions');
    }

    public function export(User $user): bool
    {
        return $user->hasPermissionTo('export-permissions');
    }
}

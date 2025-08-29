<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('browse-users');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-users');
    }

    public function import(User $user): bool
    {
        return $user->hasPermissionTo('import-users');
    }

    public function export(User $user): bool
    {
        return $user->hasPermissionTo('export-users');
    }
}

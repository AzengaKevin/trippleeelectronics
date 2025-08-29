<?php

namespace App\Policies;

use App\Models\User;

class PurchasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('browse-purchases');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-purchases');
    }

    public function import(User $user): bool
    {
        return $user->hasPermissionTo('import-purchases');
    }

    public function export(User $user): bool
    {
        return $user->hasPermissionTo('export-purchases');
    }
}

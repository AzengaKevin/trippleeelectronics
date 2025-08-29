<?php

namespace App\Services;

use App\Models\Group;
use App\Models\User;

class GroupService
{
    public function __construct(private readonly RoleService $roleService) {}

    public function createAdminAndStaffGroup(?string $name = null, bool $updateUsers = false)
    {
        $localName = $name ?? 'Admin & Staff Group';

        $group = Group::query()->updateOrCreate(['name' => $localName]);

        if ($updateUsers) {

            $adminRole = $this->roleService->createOrUpdateAdminRole(false);

            $staffRole = $this->roleService->createOrUpdateStaffRole(false);

            $users = User::query()->role([$adminRole->name, $staffRole->name])->pluck('id')->all();

            $group->users()->sync($users);
        }

        return $group->refresh();
    }

    public function createAdminGroup(?string $name = null, bool $updateUsers = false)
    {
        $localName = $name ?? 'Admin Group';

        $group = Group::query()->updateOrCreate(['name' => $localName]);

        if ($updateUsers) {

            $adminRole = $this->roleService->createOrUpdateAdminRole(false);

            $users = User::query()->role($adminRole->name)->pluck('id')->all();

            $group->users()->sync($users);
        }

        return $group->refresh();
    }

    public function createStaffGroup(?string $name = null, bool $updateUsers = false)
    {
        $localName = $name ?? 'Staff Group';

        $group = Group::query()->updateOrCreate(['name' => $localName]);

        if ($updateUsers) {

            $staffRole = $this->roleService->createOrUpdateStaffRole(false);

            $users = User::query()->role($staffRole->name)->pluck('id')->all();

            $group->users()->sync($users);
        }

        return $group->refresh();
    }
}

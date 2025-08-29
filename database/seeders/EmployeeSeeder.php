<?php

namespace Database\Seeders;

use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $staffRole = app(RoleService::class)->createOrUpdateStaffRole(update: false);

        $users = app(UserService::class)->get(
            query: null,
            perPage: null,
            with: ['employee'],
            role: $staffRole
        );

        if ($users->isEmpty()) {
            return;
        }

        $users->each(function ($user) {

            if ($user->employee) {
                return;
            }

            $user->employee()->updateOrCreate($user->only(['name', 'email', 'phone']));
        });
    }
}

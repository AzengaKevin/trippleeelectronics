<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\RoleService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        /** @var Role $role */
        $role = app(RoleService::class)->createOrUpdateAdminRole();

        $azenga = User::query()->create([
            'name' => 'Azenga Kevin',
            'email' => 'azenga.kevin7@gmail.com',
            'phone' => '+254700016349',
            'password' => Hash::make('turtledove'),
            'email_verified_at' => now(),
        ]);

        $azenga->assignRole($role);
    }
}

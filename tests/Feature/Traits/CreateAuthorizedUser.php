<?php

namespace Tests\Feature\Traits;

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait CreateAuthorizedUser
{
    use WithFaker;

    protected function createUserWithPermissions(?string $role = null, array $permissions = []): User
    {
        $roleName = $role ?? $this->faker->word();

        /** @var Role $roleModel */
        $roleModel = Role::firstOrCreate(['name' => $roleName]);

        $permissions = collect($permissions)->map(fn ($name) => Permission::query()->firstOrCreate(compact('name')));

        $roleModel->permissions()->sync($permissions);

        // Create the user
        $user = User::factory()->create();

        // Assign the role to the user
        $user->assignRole($roleModel);

        return $user;
    }
}

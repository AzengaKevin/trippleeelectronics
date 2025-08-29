<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'browse-roles',
            'create-roles',
            'import-roles',
            'export-roles',
            'update-roles',
            'delete-roles',
            'access-backoffice',
        ]);
    }

    public function test_backoffice_roles_index_route(): void
    {

        collect($roleNames = ['admin', 'staff'])->each(fn ($name) => Role::query()->create(compact('name')));

        $response = $this->actingAs($this->user)->get(route('backoffice.roles.index'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('roles.data')
        );
    }

    public function test_backoffice_roles_create_route(): void
    {

        $response = $this->actingAs($this->user)->get(route('backoffice.roles.create'));

        $response->assertStatus(200);
    }

    public function test_backoffice_roles_store_route(): void
    {

        $permissions = collect(['test-permission', 'another-test-permission'])->map(fn ($name) => Permission::query()->updateOrCreate(compact('name')));

        $payload = [
            'name' => $this->faker->unique()->word,
            'permissions' => $permissions->pluck('id')->all(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.roles.store'), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.roles.index'));

        $this->assertDatabaseHas('roles', [
            'name' => $payload['name'],
        ]);

        $newRole = Role::query()->where('name', $payload['name'])->first();

        $this->assertNotNull($newRole);

        $this->assertEquals($permissions->pluck('id')->all(), $newRole->permissions->pluck('id')->all());
    }

    public function test_backoffice_roles_show_route(): void
    {
        $role = Role::query()->firstOrCreate(['name' => $this->faker->unique()->word()]);

        $permissions = collect(['test-permission', 'another-test-permission'])->map(fn ($name) => Permission::query()->updateOrCreate(compact('name')));

        $role->permissions()->attach($permissions);

        $response = $this->actingAs($this->user)->get(route('backoffice.roles.show', $role));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('role')
                ->where('role.id', $role->id)
                ->where('role.name', $role->name)
                ->has('role.permissions')
        );
    }

    public function test_backoffice_roles_edit_route(): void
    {
        $role = Role::query()->create(['name' => 'original-role-name']);

        $permissions = collect(['test-permission', 'another-test-permission'])->map(fn ($name) => Permission::query()->updateOrCreate(compact('name')));

        $role->permissions()->attach($permissions);

        $response = $this->actingAs($this->user)->get(route('backoffice.roles.edit', $role));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('role')
                ->where('role.id', $role->id)
                ->where('role.name', $role->name)
        );
    }

    public function test_backoffice_roles_update_route(): void
    {
        $role = Role::query()->create(['name' => 'original-role-name']);

        $permissions = collect(['test-permission', 'another-test-permission'])->map(fn ($name) => Permission::query()->updateOrCreate(compact('name')));

        $payload = [
            'name' => 'new-role-name',
            'permissions' => $permissions->pluck('id')->all(),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.roles.update', $role), $payload);

        $response->assertStatus(302);
        $response->assertRedirect(route('backoffice.roles.show', $role));

        $this->assertDatabaseHas('roles', [
            'name' => $payload['name'],
        ]);

        $this->assertEquals($permissions->pluck('id')->all(), $role->fresh()->permissions->pluck('id')->all());
    }

    public function test_backoffice_roles_destroy_route(): void
    {
        $role = Role::query()->create([
            'name' => $this->faker->unique()->word,
        ]);

        $response = $this->actingAs($this->user)->delete(route('backoffice.roles.destroy', $role));

        $response->assertStatus(302);
        $response->assertRedirect(route('backoffice.roles.index'));

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_backoffice_roles_export_route(): void
    {

        $this->user->givePermissionTo('export-roles');

        Excel::fake();

        collect(['admin', 'staff'])->each(fn ($name) => Role::query()->create(compact('name')));

        $response = $this->actingAs($this->user)->get(route('backoffice.roles.export'));

        $response->assertStatus(200);

        $filename = str('roles')->append('-')->append(now()->toDateString())->append('.xlsx')->value();

        Excel::assertDownloaded($filename);
    }

    public function test_backoffice_roles_import_route_get_method(): void
    {
        $this->user->givePermissionTo('import-roles');

        $response = $this->actingAs($this->user)->get(route('backoffice.roles.import'));

        $response->assertOk();
    }

    public function test_backoffice_roles_import_route_post_method(): void
    {

        $this->user->givePermissionTo('import-roles');

        Excel::fake();

        $response = $this->actingAs($this->user)->post(route('backoffice.roles.import'), [
            'file' => \Illuminate\Http\UploadedFile::fake()->create('roles.xlsx', 1024),
        ]);

        $response->assertStatus(302);
    }
}

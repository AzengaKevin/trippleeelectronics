<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class PermissionControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'browse-permissions',
            'create-permissions',
            'import-permissions',
            'export-permissions',
            'update-permissions',
            'delete-permissions',
            'access-backoffice',
        ]);
    }

    public function test_backoffice_permissions_index_route(): void
    {

        $this->user->givePermissionTo('browse-permissions');

        collect($roleNames = ['admin', 'staff'])->each(fn ($name) => Permission::query()->create(compact('name')));

        $response = $this->actingAs($this->user)->get(route('backoffice.permissions.index'));

        $response->assertStatus(200);

        $response->assertInertia(fn (AssertableInertia $page) => $page->has('permissions'));
    }

    public function test_backoffice_permissions_create_route(): void
    {
        $this->user->givePermissionTo('create-permissions');

        $response = $this->actingAs($this->user)->get(route('backoffice.permissions.create'));

        $response->assertStatus(200);
    }

    public function test_backoffice_permissions_store_route(): void
    {
        $this->user->givePermissionTo('create-permissions');

        $payload = [
            'name' => $this->faker->unique()->word,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.permissions.store'), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.permissions.index'));

        $this->assertDatabaseHas('permissions', $payload);
    }

    public function test_backoffice_permissions_show_route(): void
    {
        $permission = Permission::query()->create([
            'name' => $this->faker->unique()->word,
        ]);

        $response = $this->actingAs($this->user)->get(route('backoffice.permissions.show', $permission));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('permission')
                ->where('permission.id', $permission->id)
                ->where('permission.name', $permission->name)
        );
    }

    public function test_backoffice_permissions_edit_route(): void
    {
        $permission = Permission::query()->create([
            'name' => $this->faker->unique()->word,
        ]);

        $response = $this->actingAs($this->user)->get(route('backoffice.permissions.edit', $permission));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('permission')
                ->where('permission.id', $permission->id)
                ->where('permission.name', $permission->name)
        );
    }

    public function test_backoffice_permissions_update_route(): void
    {
        $permission = Permission::query()->create([
            'name' => $this->faker->unique()->word,
        ]);

        $payload = [
            'name' => $this->faker->unique()->word,
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.permissions.update', $permission), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.permissions.show', $permission));

        $this->assertDatabaseHas('permissions', $payload);
    }

    public function test_backoffice_permissions_destroy_route(): void
    {
        $permission = Permission::query()->create([
            'name' => $this->faker->unique()->word,
        ]);

        $response = $this->actingAs($this->user)->delete(route('backoffice.permissions.destroy', $permission));

        $response->assertStatus(302);
        $response->assertRedirect(route('backoffice.permissions.index'));

        $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
    }

    public function test_backoffice_permissions_export_route(): void
    {
        $this->user->givePermissionTo('export-permissions');

        Excel::fake();

        collect(['admin', 'staff'])->each(fn ($name) => Permission::query()->create(compact('name')));

        $response = $this->actingAs($this->user)->get(route('backoffice.permissions.export'));

        $response->assertStatus(200);

        $filename = str('permissions')->append('-')->append(now()->toDateString())->append('.xlsx')->value();

        Excel::assertDownloaded($filename);
    }

    public function test_backoffice_permissions_import_route_get_method(): void
    {

        $this->user->givePermissionTo('import-permissions');

        $response = $this->actingAs($this->user)->get(route('backoffice.permissions.import'));

        $response->assertOk();
    }

    public function test_backoffice_permissions_import_route_post_method(): void
    {
        $this->user->givePermissionTo('import-permissions');

        Excel::fake();

        $response = $this->actingAs($this->user)->post(route('backoffice.permissions.import'), [
            'file' => \Illuminate\Http\UploadedFile::fake()->create('permissions.xlsx', 1024),
        ]);

        $response->assertStatus(302);
    }
}

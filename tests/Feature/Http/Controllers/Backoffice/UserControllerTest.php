<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Mail\AccountCreatedMail;
use App\Mail\AccountPasswordUpdatedMail;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $admin = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createUserWithPermissions(permissions: ['access-backoffice', 'browse-users', 'create-users', 'import-users', 'export-users', 'update-users', 'delete-users']);

        Mail::fake();
    }

    public function test_backoffice_users_index_route(): void
    {
        User::factory()->count($usersCount = 2)->create();

        $response = $this->actingAs($this->admin)->get(route('backoffice.users.index'));

        $response->assertStatus(200);

        $response->assertInertia(fn (AssertableInertia $page) => $page->has('users.data', $usersCount + 1)->has('roles')->has('stores')->has('params'));
    }

    public function test_backoffice_users_create_route(): void
    {
        $response = $this->actingAs($this->admin)->get(route('backoffice.users.create'));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->has('roles'));
    }

    public function test_backoffice_users_store_route(): void
    {
        $payload = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('#########'))->value(),
        ];

        $response = $this->actingAs($this->admin)->post(route('backoffice.users.store'), $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('users', [
            ...$payload,
            'author_user_id' => $this->admin->id,
        ]);

        Mail::assertSent(AccountCreatedMail::class);
    }

    public function test_backoffice_users_store_route_with_stores(): void
    {
        $stores = Store::factory()->count(2)->create();

        $payload = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('#########'))->value(),
            'stores' => $stores->pluck('id')->all(),
        ];

        $response = $this->actingAs($this->admin)->post(route('backoffice.users.store'), $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('users', [
            'name' => data_get($payload, 'name'),
            'email' => data_get($payload, 'email'),
            'phone' => data_get($payload, 'phone'),
        ]);

        $user = User::query()->where([
            ['name', $payload['name']],
            ['email', $payload['email']],
            ['phone', $payload['phone']],
        ])->first();

        $this->assertEquals($payload['stores'], $user->stores->pluck('id')->all());

        Mail::assertSent(AccountCreatedMail::class);
    }

    public function test_backoffice_users_store_route_with_roles(): void
    {
        $roles = collect($roleNames = ['Admin', 'Staff'])->map(fn ($name) => Role::query()->updateOrCreate(compact('name')))->pluck('id')->all();

        $payload = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('#########'))->value(),
            'roles' => $roles,
        ];

        $response = $this->actingAs($this->admin)->post(route('backoffice.users.store'), $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('users', [
            'name' => data_get($payload, 'name'),
            'email' => data_get($payload, 'email'),
            'phone' => data_get($payload, 'phone'),
        ]);

        $user = User::query()->where([
            ['name', $payload['name']],
            ['email', $payload['email']],
            ['phone', $payload['phone']],
        ])->first();

        $this->assertEquals($roleNames, $user->roles->pluck('name')->all());

        Mail::assertSent(AccountCreatedMail::class);
    }

    public function test_backoffice_users_show_route(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('backoffice.users.show', $user));

        $response->assertStatus(200);

        $response->assertInertia(fn (AssertableInertia $page) => $page->has('user'));
    }

    public function test_backoffice_users_edit_route(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('backoffice.users.edit', $user));

        $response->assertStatus(200);

        $response->assertInertia(fn (AssertableInertia $page) => $page->hasAll('user', 'roles'));
    }

    public function test_backoffice_users_update_route(): void
    {
        $user = User::factory()->create();

        $payload = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('#########'))->value(),
        ];

        $response = $this->actingAs($this->admin)->put(route('backoffice.users.update', $user), $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('users', $payload);
    }

    public function test_backoffice_users_update_route_with_stores(): void
    {
        $user = User::factory()->create();

        $stores = Store::factory()->count(2)->create();

        $payload = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('#########'))->value(),
            'stores' => $stores->pluck('id')->all(),
        ];

        $response = $this->actingAs($this->admin)->put(route('backoffice.users.update', $user), $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('users', [
            ['name', $payload['name']],
            ['email', $payload['email']],
            ['phone', $payload['phone']],
        ]);

        $this->assertEquals($payload['stores'], $user->stores->pluck('id')->all());
    }

    public function test_backoffice_users_update_route_with_roles(): void
    {
        $user = User::factory()->create();

        $roles = collect($roleNames = ['Admin', 'Staff'])->map(fn ($name) => Role::query()->updateOrCreate(compact('name')))->pluck('id')->all();

        $payload = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('#########'))->value(),
            'roles' => $roles,
        ];

        $response = $this->actingAs($this->admin)->put(route('backoffice.users.update', $user), $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('users', [
            ['name', $payload['name']],
            ['email', $payload['email']],
            ['phone', $payload['phone']],
        ]);

        $this->assertEquals($roleNames, $user->roles->pluck('name')->all());
    }

    public function test_backoffice_users_destroy_route(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('backoffice.users.destroy', $user));

        $response->assertStatus(302);

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    public function test_backoffice_users_destroy_route_with_destroy_param(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('backoffice.users.destroy', [
            'user' => $user,
            'destroy' => true,
        ]));

        $response->assertStatus(302);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_backoffice_users_export_route(): void
    {
        $this->admin->givePermissionTo('export-users');

        Excel::fake();

        User::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)->get(route('backoffice.users.export'));

        $response->assertStatus(200);

        Excel::assertDownloaded(User::getExportFileName());
    }

    public function test_backoffice_users_import_route_get(): void
    {
        $this->admin->givePermissionTo('import-users');

        $response = $this->actingAs($this->admin)->get(route('backoffice.users.import'));

        $response->assertStatus(200);
    }

    public function test_backoffice_users_import_route_post(): void
    {
        $this->admin->givePermissionTo('import-users');

        Excel::fake();

        $response = $this->actingAs($this->admin)->post(route('backoffice.users.import'), [
            'file' => \Illuminate\Http\UploadedFile::fake()->create('users.xlsx', 1024),
        ]);

        $response->assertStatus(302);
    }

    public function test_backoffice_users_update_password_route(): void
    {
        $user = User::factory()->create();

        $this->assertTrue(Hash::check('password', $user->fresh()->password));

        $response = $this->actingAs($this->admin)->patch(route('backoffice.users.update-password', $user));

        $response->assertStatus(302);

        $this->assertFalse(Hash::check('password', $user->fresh()->password));

        Mail::assertSent(AccountPasswordUpdatedMail::class);
    }
}

<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Employee;
use App\Models\Suspension;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class SuspensionControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-suspensions',
            'create-suspensions',
            'update-suspensions',
            'delete-suspensions',
            'import-suspensions',
            'export-suspensions',
        ]);
    }

    public function test_backoffice_suspensions_index_route(): void
    {
        Suspension::factory()->count(2)->for(Employee::factory()->for(User::factory()))->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.suspensions.index'));

        $response->assertOk();

        $response->assertInertia(fn (AssertableInertia $res) => $res->component('backoffice/suspensions/IndexPage')->has('suspensions')->has('employees'));
    }

    public function test_backoffice_suspensions_create_route(): void
    {
        Employee::factory()->for(User::factory())->create();

        Employee::factory()->for(User::factory())->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.suspensions.create'));

        $response->assertOk();

        $response->assertInertia(fn (AssertableInertia $res) => $res->component('backoffice/suspensions/CreatePage')->has('employees'));
    }

    public function test_backoffice_suspensions_store_route(): void
    {
        $employee = Employee::factory()->for(User::factory())->create();

        $data = [
            'employee' => $employee->id,
            'from' => now()->subMonth()->toDateString(),
            'to' => now()->addMonths(4)->toDateString(),
            'reason' => $this->faker->paragraph(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.suspensions.store'), $data);

        $response->assertRedirect();

        $this->assertDatabaseHas('suspensions', [
            'employee_id' => data_get($data, 'employee'),
            'from' => data_get($data, 'from'),
            'to' => data_get($data, 'to'),
            'reason' => data_get($data, 'reason'),
        ]);
    }

    public function test_backoffice_suspensions_show_route(): void
    {
        $employee = Employee::factory()->for(User::factory())->create();

        $suspension = Suspension::factory()->for($employee)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.suspensions.show', $suspension));

        $response->assertInertia(fn (AssertableInertia $res) => $res->component('backoffice/suspensions/ShowPage')->has('suspension'));
    }

    public function test_backoffice_suspensions_edit_route(): void
    {
        $employee = Employee::factory()->for(User::factory())->create();

        $suspension = Suspension::factory()->for($employee)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.suspensions.edit', $suspension));

        $response->assertInertia(fn (AssertableInertia $res) => $res->component('backoffice/suspensions/EditPage')->has('suspension')->has('employees'));
    }

    public function test_backoffice_suspensions_update_route(): void
    {
        $employee = Employee::factory()->for(User::factory())->create();

        $suspension = Suspension::factory()->for($employee)->create();

        $data = [
            'employee' => $employee->id,
            'from' => now()->subMonth()->toDateString(),
            'to' => now()->addMonths(4)->toDateString(),
            'reason' => $this->faker->paragraph(),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.suspensions.update', $suspension), $data);

        $response->assertRedirect();

        $this->assertDatabaseHas('suspensions', [
            'employee_id' => data_get($data, 'employee'),
            'from' => data_get($data, 'from'),
            'to' => data_get($data, 'to'),
            'reason' => data_get($data, 'reason'),
        ]);
    }

    public function test_backoffice_suspensions_destroy_route(): void
    {
        $employee = Employee::factory()->for(User::factory())->create();

        $suspension = Suspension::factory()->for($employee)->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.suspensions.destroy', $suspension));

        $this->assertSoftDeleted($suspension);

        $response->assertRedirect();
    }
}

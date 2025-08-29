<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class EmployeeControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-employees',
            'create-employees',
            'update-employees',
            'delete-employees',
        ]);
    }

    public function test_backoffice_employees_index_route(): void
    {
        Employee::factory()->for(User::factory(), 'user')->create();

        Employee::factory()->for(User::factory(), 'user')->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.employees.index'));

        $response->assertOk();

        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/employees/IndexPage')
                ->has('employees')
                ->has('params')
        );
    }

    public function test_backoffice_employees_show_route(): void
    {
        $employee = Employee::factory()->for(User::factory())->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.employees.show', $employee));

        $response->assertOk();

        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/employees/ShowPage')
                ->has('employee')
        );
    }

    public function test_backoffice_employees_edit_route(): void
    {
        $employee = Employee::factory()->for(User::factory())->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.employees.edit', $employee));

        $response->assertOk();

        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/employees/EditPage')
                ->has('employee')
        );
    }

    public function test_backoffice_employees_update_route(): void
    {
        $employee = Employee::factory()->for(User::factory())->create();

        $payload = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('#########'))->value(),
            'position' => $this->faker->jobTitle(),
            'department' => $this->faker->randomElement(['HR', 'IT', 'Finance', 'Marketing']),
            'kra_pin' => str('A')->append($this->faker->numerify('########'))->append($this->faker->randomElement())->upper()->value(),
            'identification_number' => $this->faker->numerify('########'),
            'hire_date' => now()->toDateString(),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.employees.update', $employee), $payload);

        $response->assertRedirect(route('backoffice.employees.show', $employee));

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'name' => $payload['name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'],
            'position' => $payload['position'],
            'department' => $payload['department'],
            'kra_pin' => $payload['kra_pin'],
            'identification_number' => $payload['identification_number'],
            'hire_date' => $payload['hire_date'],
        ]);
    }

    public function test_backoffice_employees_suspend_route(): void
    {

        $employee = Employee::factory()->for(User::factory())->create();

        $response = $this->actingAs($this->user)->post(route('backoffice.employees.suspend', $employee));

        $response->assertRedirect();

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('suspensions', [
            'employee_id' => $employee->id,
            'from' => now()->toDateString(),
            'to' => null,
            'reason' => null,
        ]);
    }

    public function test_backoffice_employees_destroy_route(): void
    {
        $employee = Employee::factory()->for(User::factory())->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.employees.destroy', $employee));

        $response->assertRedirect(route('backoffice.employees.index'));

        $this->assertSoftDeleted('employees', [
            'id' => $employee->id,
        ]);
    }
}

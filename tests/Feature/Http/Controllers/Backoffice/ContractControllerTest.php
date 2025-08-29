<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Contract;
use App\Models\Employee;
use App\Models\Enums\ContractStatus;
use App\Models\Enums\ContractType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class ContractControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-contracts',
            'create-contracts',
            'update-contracts',
            'delete-contracts',
            'import-contracts',
            'export-contracts',
        ]);
    }

    public function test_contacts_index_route(): void
    {
        Contract::factory()->count(2)->for(Employee::factory()->for(User::factory()))->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.contracts.index'));

        $response->assertOk();

        $response->assertInertia(fn (AssertableInertia $res) => $res->component('backoffice/contracts/IndexPage')->has('contracts')->has('types')->has('status')->has('employees'));
    }

    public function test_backoffice_contracts_create_route(): void
    {
        Employee::factory()->for(User::factory())->create();

        Employee::factory()->for(User::factory())->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.contracts.create'));

        $response->assertOk();

        $response->assertInertia(fn (AssertableInertia $res) => $res->component('backoffice/contracts/CreatePage')->has('types')->has('statuses')->has('employees'));
    }

    public function test_backoffice_contracts_store_route(): void
    {
        $employee = Employee::factory()->for(User::factory())->create();

        $data = [
            'employee' => $employee->id,
            'contract_type' => $this->faker->randomElement(ContractType::options()),
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonths(4)->toDateString(),
            'salary' => $this->faker->numberBetween(20000, 40000),
            'status' => $this->faker->randomElement(ContractStatus::options()),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.contracts.store'), $data);

        $response->assertRedirect();

        $this->assertDatabaseHas('contracts', [
            'employee_id' => data_get($data, 'employee'),
            'contract_type' => data_get($data, 'contract_type'),
            'start_date' => data_get($data, 'start_date'),
            'end_date' => data_get($data, 'end_date'),
            'salary' => data_get($data, 'salary'),
            'status' => data_get($data, 'status'),
        ]);
    }

    public function test_backoffice_contracts_store_route_with_responsibilities(): void
    {
        $employee = Employee::factory()->for(User::factory())->create();

        $data = [
            'employee' => $employee->id,
            'contract_type' => $this->faker->randomElement(ContractType::options()),
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonths(4)->toDateString(),
            'salary' => $this->faker->numberBetween(20000, 40000),
            'status' => $this->faker->randomElement(ContractStatus::options()),
            'responsibilities' => [
                'Responsibility 1',
                'Responsibility 2',
                'Responsibility 3',
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.contracts.store'), $data);

        $response->assertRedirect();

        $this->assertDatabaseHas('contracts', [
            'employee_id' => data_get($data, 'employee'),
            'contract_type' => data_get($data, 'contract_type'),
            'start_date' => data_get($data, 'start_date'),
            'end_date' => data_get($data, 'end_date'),
            'salary' => data_get($data, 'salary'),
            'status' => data_get($data, 'status'),
        ]);

        $contract = $employee->contracts()->first();

        $this->assertNotNull($contract);

        $this->assertEquals(data_get($data, 'responsibilities'), $contract->responsibilities);
    }

    public function test_backoffice_contracts_show_route(): void
    {
        $employee = Employee::factory()->for(User::factory())->create();

        $contract = Contract::factory()->for($employee)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.contracts.show', $contract));

        $response->assertInertia(fn (AssertableInertia $res) => $res->component('backoffice/contracts/ShowPage')->has('contract'));
    }

    public function test_backoffice_contracts_edit_route(): void
    {
        $employee = Employee::factory()->for(User::factory())->create();

        $contract = Contract::factory()->for($employee)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.contracts.edit', $contract));

        $response->assertInertia(fn (AssertableInertia $res) => $res->component('backoffice/contracts/EditPage')->has('contract')->has('employees')->has('types')->has('statuses'));
    }

    public function test_backoffice_contracts_update_route(): void
    {
        $employee = Employee::factory()->for(User::factory())->create();

        $contract = Contract::factory()->for($employee)->create();

        $data = [
            'employee' => $employee->id,
            'contract_type' => $this->faker->randomElement(ContractType::options()),
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonths(4)->toDateString(),
            'salary' => $this->faker->numberBetween(20000, 40000),
            'status' => $this->faker->randomElement(ContractStatus::options()),
            'responsibilities' => [
                'Responsibility 1',
                'Responsibility 2',
                'Responsibility 3',
            ],
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.contracts.update', $contract), $data);

        $response->assertRedirect();

        $this->assertDatabaseHas('contracts', [
            'employee_id' => data_get($data, 'employee'),
            'contract_type' => data_get($data, 'contract_type'),
            'start_date' => data_get($data, 'start_date'),
            'end_date' => data_get($data, 'end_date'),
            'salary' => data_get($data, 'salary'),
            'status' => data_get($data, 'status'),
        ]);

        $contract->refresh();

        $this->assertNotNull($contract);

        $this->assertEquals(data_get($data, 'responsibilities'), $contract->responsibilities);
    }

    public function test_backoffice_contracts_destroy_route(): void
    {
        $employee = Employee::factory()->for(User::factory())->create();

        $contract = Contract::factory()->for($employee)->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.contracts.destroy', $contract));

        $this->assertSoftDeleted($contract);

        $response->assertRedirect();
    }
}

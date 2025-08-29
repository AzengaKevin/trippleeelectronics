<?php

namespace Tests\Feature\Models;

use App\Models\Contract;
use App\Models\Employee;
use App\Models\Enums\ContractStatus;
use App\Models\Enums\ContractType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContractTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_contract_creation()
    {

        /** @var User $user */
        $user = User::factory()->create();

        /** @var Employee $employee */
        $employee = Employee::factory()->for($user, 'user')->create();

        $attributes = [
            'employee_id' => $employee->id,
            'contract_type' => $this->faker->randomElement(ContractType::options()),
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonths(4)->toDateString(),
            'salary' => $this->faker->numberBetween(20000, 40000),
            'status' => $this->faker->randomElement(ContractStatus::options()),
        ];

        $contract = Contract::query()->create($attributes);

        $this->assertDatabaseHas('contracts', [
            'employee_id' => $employee->id,
            'contract_type' => $attributes['contract_type'],
            'start_date' => $attributes['start_date'],
            'end_date' => $attributes['end_date'],
            'salary' => $attributes['salary'],
            'status' => $attributes['status'],
        ]);

        $this->assertNotNull($contract->employee);
    }
}

<?php

namespace Tests\Feature\Models;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_employee_creation()
    {
        $user = User::factory()->create();

        $attributes = [
            'user_id' => $user->id,
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('#########'))->value(),
            'position' => $this->faker->jobTitle(),
            'department' => $this->faker->randomElement(['HR', 'IT', 'Finance', 'Marketing']),
            'kra_pin' => str('A')->append($this->faker->numerify('########'))->append($this->faker->randomElement())->upper()->value(),
            'identification_number' => $this->faker->numerify('########'),
            'hire_date' => now()->toDateString(),
        ];

        $employee = Employee::create($attributes);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'phone' => $attributes['phone'],
            'position' => $attributes['position'],
            'department' => $attributes['department'],
            'kra_pin' => $attributes['kra_pin'],
            'identification_number' => $attributes['identification_number'],
            'hire_date' => $attributes['hire_date'],
        ]);
    }

    public function test_employee_belongs_to_user()
    {
        $user = User::factory()->create();

        $employee = Employee::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $employee->user);

        $this->assertEquals($user->id, $employee->user->id);
    }
}

<?php

namespace Tests\Feature\Models;

use App\Models\Employee;
use App\Models\Suspension;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SuspensionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_suspension(): void
    {

        $user = User::factory()->create();

        $employee = Employee::factory()->for(User::factory())->create();

        $attributes = [
            'author_user_id' => $user->id,
            'employee_id' => $employee->id,
            'from' => now()->subWeek()->toDateString(),
            'to' => now()->addWeek()->toDateString(),
            'reason' => $this->faker->paragraph(),
        ];

        $suspension = Suspension::query()->create($attributes);

        $this->assertNotNull($suspension);

        $this->assertDatabaseHas('suspensions', $attributes);
    }
}

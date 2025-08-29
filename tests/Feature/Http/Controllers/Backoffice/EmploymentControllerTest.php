<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Contract;
use App\Models\Employee;
use App\Models\Enums\ContractStatus;
use App\Models\Enums\EmployeeDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class EmploymentControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'read-employment',
            'update-employment',
        ]);

        $this->employee = Employee::factory()->for($this->user, 'user')->create();

        Contract::factory()->for($this->employee)->create(['start_date' => now()->toDateString(), 'end_date' => null, 'status' => ContractStatus::ACTIVE]);
    }

    public function test_backoffice_employment_show_route()
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.employment.show'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/employment/ShowPage')
                ->has('employee')
        );
    }

    public function test_backoffice_employment_edit_route()
    {

        $response = $this->actingAs($this->user)->get(route('backoffice.employment.edit'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/employment/EditPage')
                ->has('employee')
                ->has('documentTypes')
        );
    }

    public function test_backoffice_employment_update_route()
    {
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

        $response = $this->actingAs($this->user)->put(route('backoffice.employment.update', $this->employee), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.employment.show'));

        $this->assertDatabaseHas('employees', $payload);
    }

    public function test_backoffice_employement_update_route_with_documents()
    {
        Storage::fake('public');

        $payload = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('#########'))->value(),
            'position' => $this->faker->jobTitle(),
            'department' => $this->faker->randomElement(['HR', 'IT', 'Finance', 'Marketing']),
            'kra_pin' => str('A')->append($this->faker->numerify('########'))->append($this->faker->randomElement())->upper()->value(),
            'identification_number' => $this->faker->numerify('########'),
            'hire_date' => now()->toDateString(),
            'documents' => [
                [
                    'type' => EmployeeDocument::ID->value,
                    'file' => UploadedFile::fake()->create('id.pdf'),
                ],
                [
                    'type' => EmployeeDocument::KRA_PIN->value,
                    'file' => UploadedFile::fake()->create('kra-pin.pdf'),
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.employment.update', $this->employee), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.employment.show'));

        $this->assertDatabaseHas('employees', [
            'name' => data_get($payload, 'name'),
            'email' => data_get($payload, 'email'),
            'phone' => data_get($payload, 'phone'),
            'position' => data_get($payload, 'position'),
            'department' => data_get($payload, 'department'),
            'kra_pin' => data_get($payload, 'kra_pin'),
            'identification_number' => data_get($payload, 'identification_number'),
            'hire_date' => data_get($payload, 'hire_date'),
        ]);

        $this->employee->refresh();

        $idMedia = $this->employee->getFirstMedia(EmployeeDocument::ID->value);

        $this->assertNotNull($idMedia);

        $this->assertEquals(str($this->employee->name)->append('-')->append(EmployeeDocument::ID->value)->slug()->append('.pdf')->value(), $idMedia->file_name);

        $kraPinMedia = $this->employee->getFirstMedia(EmployeeDocument::KRA_PIN->value);

        $this->assertNotNull($kraPinMedia);

        $this->assertEquals(str($this->employee->name)->append('-')->append(EmployeeDocument::KRA_PIN->value)->slug()->append('.pdf')->value(), $kraPinMedia->file_name);
    }
}

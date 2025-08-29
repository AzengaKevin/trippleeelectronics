<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Individual;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class IndividualControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-individuals',
            'create-individuals',
            'read-individuals',
            'update-individuals',
            'delete-individuals',
            'export-individuals',
            'import-individuals',
        ]);
    }

    public function test_backoffice_individuals_index_route(): void
    {
        Individual::factory()->count($individualsCount = 2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.individuals.index'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('individuals.data', $individualsCount)
        );
    }

    public function test_backoffice_individuals_create_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.individuals.create'));

        $response->assertStatus(200);
    }

    public function test_backoffice_individuals_store_route(): void
    {

        $payload = [
            'name' => $this->faker->name(),
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
            'address' => $this->faker->address(),
            'kra_pin' => str('A')->append($this->faker->numerify('########'))->append($this->faker->randomElement())->upper()->value(),
            'id_number' => $this->faker->numerify('########'),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.individuals.store'), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.individuals.index'));

        $this->assertDatabaseHas('individuals', [
            ...$payload,
            'author_user_id' => $this->user->id,
        ]);
    }

    public function test_backoffice_individuals_store_route_with_image(): void
    {

        Storage::fake('public');

        $payload = [
            'name' => $this->faker->name(),
            'image' => \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg'),
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
            'address' => $this->faker->address(),
            'kra_pin' => str('A')->append($this->faker->numerify('########'))->append($this->faker->randomElement())->upper()->value(),
            'id_number' => $this->faker->numerify('########'),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.individuals.store'), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.individuals.index'));

        $individual = Individual::where('email', $payload['email'])->first();

        $this->assertCount(1, $individual->getMedia());
    }

    public function test_backoffice_individuals_show_route(): void
    {
        $individual = Individual::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.individuals.show', $individual));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('individual')
                ->where('individual.id', $individual->id)
                ->where('individual.name', $individual->name)
                ->where('individual.username', $individual->username)
                ->where('individual.email', $individual->email)
                ->where('individual.phone', $individual->phone)
                ->where('individual.address', $individual->address)
                ->where('individual.kra_pin', $individual->kra_pin)
                ->where('individual.id_number', $individual->id_number)
        );
    }

    public function test_backoffice_individuals_edit_route(): void
    {
        $individual = Individual::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.individuals.edit', $individual));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('individual')
                ->where('individual.id', $individual->id)
                ->where('individual.name', $individual->name)
                ->where('individual.username', $individual->username)
                ->where('individual.email', $individual->email)
                ->where('individual.phone', $individual->phone)
                ->where('individual.address', $individual->address)
                ->where('individual.kra_pin', $individual->kra_pin)
                ->where('individual.id_number', $individual->id_number)
        );
    }

    public function test_backoffice_individuals_update_route(): void
    {
        $individual = Individual::factory()->create();

        $organization = Organization::factory()->create();

        $payload = [
            'organization' => $organization->id,
            'name' => $this->faker->name(),
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
            'address' => $this->faker->address(),
            'kra_pin' => str('A')->append($this->faker->numerify('########'))->append($this->faker->randomElement())->upper()->value(),
            'id_number' => $this->faker->numerify('########'),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.individuals.update', $individual), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.individuals.show', $individual));

        $this->assertDatabaseHas('individuals', [
            'name' => $payload['name'],
            'username' => $payload['username'],
            'email' => $payload['email'],
            'phone' => $payload['phone'],
            'address' => $payload['address'],
            'kra_pin' => $payload['kra_pin'],
            'id_number' => $payload['id_number'],
        ]);
    }

    public function test_backoffice_individuals_update_route_with_image(): void
    {
        Storage::fake('public');

        $individual = Individual::factory()->create();

        $organization = Organization::factory()->create();

        $payload = [
            'organization' => $organization->id,
            'name' => $this->faker->name(),
            'image' => \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg'),
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
            'address' => $this->faker->address(),
            'kra_pin' => str('A')->append($this->faker->numerify('########'))->append($this->faker->randomElement())->upper()->value(),
            'id_number' => $this->faker->numerify('########'),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.individuals.update', $individual), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.individuals.show', $individual));

        $this->assertCount(1, $individual->getMedia());
    }

    public function test_backoffice_individuals_destroy_route(): void
    {
        $individual = Individual::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.individuals.destroy', $individual));

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.individuals.index'));

        $this->assertSoftDeleted('individuals', [
            'id' => $individual->id,
            'name' => $individual->name,
            'email' => $individual->email,
            'phone' => $individual->phone,
        ]);
    }

    public function test_backoffice_individuals_destroy_route_forever(): void
    {
        $individual = Individual::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.individuals.destroy', [$individual, 'forever' => true]));

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.individuals.index'));

        $this->assertDatabaseMissing('individuals', [
            'id' => $individual->id,
            'name' => $individual->name,
            'email' => $individual->email,
            'phone' => $individual->phone,
        ]);
    }

    public function test_backoffice_individuals_export_route(): void
    {
        Excel::fake();

        Individual::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.individuals.export'));

        $response->assertStatus(200);

        Excel::assertDownloaded(Individual::getExportFileName());
    }

    public function test_backoffice_individuals_import_route_get_method(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.individuals.import'));

        $response->assertOk();
    }

    public function test_backoffice_individuals_import_route_post_method(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->user)->post(route('backoffice.individuals.import'), [
            'file' => \Illuminate\Http\UploadedFile::fake()->create('users.xlsx', 1024),
        ]);

        $response->assertStatus(302);
    }
}

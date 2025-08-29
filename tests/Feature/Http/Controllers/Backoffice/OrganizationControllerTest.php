<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Organization;
use App\Models\OrganizationCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class OrganizationControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-organizations',
            'create-organizations',
            'read-organizations',
            'update-organizations',
            'delete-organizations',
            'export-organizations',
            'import-organizations',
        ]);
    }

    public function test_backoffice_organizations_index_route(): void
    {
        Organization::factory()->count($organizationsCount = 2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.organizations.index'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('organizations.data', $organizationsCount)
        );
    }

    public function test_backoffice_organizations_create_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.organizations.create'));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->has('categories'));
    }

    public function test_backoffice_organizations_store_route(): void
    {
        Storage::fake('public');

        $category = OrganizationCategory::factory()->create();

        $payload = [
            'category' => $category->id,
            'name' => $this->faker->company(),
            'image' => UploadedFile::fake()->image('organization.jpg'),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
            'address' => $this->faker->address(),
            'kra_pin' => str('P')->append($this->faker->numerify('########'))->append($this->faker->randomLetter())->upper()->value(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.organizations.store'), $payload);

        $this->assertDatabaseHas('organizations', [
            'author_user_id' => $this->user->id,
            'organization_category_id' => $payload['category'],
            'name' => $payload['name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'],
            'address' => $payload['address'],
            'kra_pin' => $payload['kra_pin'],
        ]);

        $organization = Organization::query()->where('name', $payload['name'])->first();

        $this->assertNotNull($organization);

        $this->assertCount(1, $organization->getMedia());

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.organizations.index'));
    }

    public function test_backoffice_organizations_show_route(): void
    {
        $organization = Organization::factory()->for(OrganizationCategory::factory())->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.organizations.show', $organization));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('organization')
                ->where('organization.id', $organization->id)
                ->where('organization.name', $organization->name)
                ->where('organization.email', $organization->email)
                ->where('organization.phone', $organization->phone)
                ->where('organization.address', $organization->address)
                ->where('organization.kra_pin', $organization->kra_pin)
        );

    }

    public function test_backoffice_organizations_edit_route(): void
    {
        $organization = Organization::factory()->for(OrganizationCategory::factory())->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.organizations.edit', $organization));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('organization')
                ->where('organization.id', $organization->id)
                ->where('organization.name', $organization->name)
                ->where('organization.email', $organization->email)
                ->where('organization.phone', $organization->phone)
                ->where('organization.address', $organization->address)
                ->where('organization.kra_pin', $organization->kra_pin)
                ->has('categories')
        );
    }

    public function test_backoffice_organizations_update_route(): void
    {
        Storage::fake('public');

        $organization = Organization::factory()->for(OrganizationCategory::factory())->create();

        $payload = [
            'category' => $organization->organization_category_id,
            'name' => $this->faker->company(),
            'image' => UploadedFile::fake()->image('organization.jpg'),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
            'address' => $this->faker->address(),
            'kra_pin' => str('P')->append($this->faker->numerify('########'))->append($this->faker->randomLetter())->upper()->value(),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.organizations.update', $organization), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.organizations.show', $organization));

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => $payload['name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'],
            'address' => $payload['address'],
            'kra_pin' => $payload['kra_pin'],
        ]);

        $this->assertCount(1, $organization->getMedia());
    }

    public function test_backoffice_organizations_destroy_route(): void
    {
        $organization = Organization::factory()->for(OrganizationCategory::factory())->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.organizations.destroy', $organization));

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.organizations.index'));

        $this->assertSoftDeleted('organizations', [
            'id' => $organization->id,
            'name' => $organization->name,
            'email' => $organization->email,
            'phone' => $organization->phone,
            'address' => $organization->address,
            'kra_pin' => $organization->kra_pin,
        ]);
    }

    public function test_backoffice_organizations_export_route(): void
    {
        Excel::fake();

        Organization::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.organizations.export'));

        $response->assertStatus(200);

        Excel::assertDownloaded(Organization::getExportFilename());

    }

    public function test_backoffice_organizations_import_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.organizations.import'));

        $response->assertStatus(200);
    }

    public function test_backoffice_organizations_import_route_post(): void
    {
        Excel::fake();

        $file = UploadedFile::fake()->create('organizations.xlsx', 1024);

        $response = $this->actingAs($this->user)->post(route('backoffice.organizations.import'), [
            'file' => $file,
        ]);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.organizations.index'));
    }
}

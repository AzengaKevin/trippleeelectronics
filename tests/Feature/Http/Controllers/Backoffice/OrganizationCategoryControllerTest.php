<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\OrganizationCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class OrganizationCategoryControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-organization-categories',
            'create-organization-categories',
            'read-organization-categories',
            'update-organization-categories',
            'delete-organization-categories',
            'export-organization-categories',
            'import-organization-categories',
        ]);
    }

    public function test_backoffice_organization_categories_index_route(): void
    {

        $this->withoutExceptionHandling();

        OrganizationCategory::factory()->count($categoriesCount = 2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.organization-categories.index'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn ($page) => $page->has('categories.data', $categoriesCount)
                ->has('categories.links')
        );
    }

    public function test_backoffice_organization_categories_create_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.organization-categories.create'));

        $response->assertStatus(200);
    }

    public function test_backoffice_organization_categories_store_route(): void
    {
        $payload = [
            'name' => $this->faker->word(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.organization-categories.store'), $payload);

        $response->assertStatus(302);
        $response->assertRedirect(route('backoffice.organization-categories.index'));
        $this->assertDatabaseHas('organization_categories', [
            'name' => $payload['name'],
            'author_user_id' => $this->user->id,
        ]);
    }

    public function test_backoffice_organization_categories_show_route(): void
    {
        $category = OrganizationCategory::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.organization-categories.show', $category));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->has('category')
        );
    }

    public function test_backoffice_organization_categories_edit_route(): void
    {
        $category = OrganizationCategory::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.organization-categories.edit', $category));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->has('category')
        );
    }

    public function test_backoffice_organization_categories_update_route(): void
    {
        $category = OrganizationCategory::factory()->create();

        $payload = [
            'name' => $this->faker->word(),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.organization-categories.update', $category), $payload);

        $response->assertStatus(302);
        $response->assertRedirect(route('backoffice.organization-categories.show', $category));
        $this->assertDatabaseHas('organization_categories', [
            'id' => $category->id,
            'name' => $payload['name'],
        ]);
    }

    public function test_backoffice_organization_categories_destroy_route(): void
    {
        $category = OrganizationCategory::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.organization-categories.destroy', $category));

        $response->assertStatus(302);
        $response->assertRedirect(route('backoffice.organization-categories.index'));
        $this->assertSoftDeleted('organization_categories', [
            'id' => $category->id,
        ]);
    }

    public function test_backoffice_organization_categories_export_route(): void
    {
        Excel::fake();

        OrganizationCategory::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.organization-categories.export'));

        $response->assertStatus(200);

        Excel::assertDownloaded(OrganizationCategory::getExportFilename());
    }

    public function test_backoffice_organization_categories_import_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.organization-categories.import'));

        $response->assertStatus(200);
    }

    public function test_backoffice_organization_categories_import_store_route(): void
    {
        Excel::fake();

        $file = \Illuminate\Http\UploadedFile::fake()->create('test.xlsx', 100);

        $response = $this->actingAs($this->user)->post(route('backoffice.organization-categories.import'), [
            'file' => $file,
        ]);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.organization-categories.index'));
    }
}

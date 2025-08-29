<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Jurisdiction;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class TaxControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-taxes',
            'create-taxes',
            'update-taxes',
            'delete-taxes',
        ]);
    }

    public function test_backoffice_taxes_index(): void
    {

        $this->withoutExceptionHandling();

        Tax::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.taxes.index'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/taxes/IndexPage')
                ->has('taxes.data', 2)
                ->has('params')
        );
    }

    public function test_backoffice_taxes_create(): void
    {
        $this->withoutExceptionHandling();

        $response = $this->actingAs($this->user)->get(route('backoffice.taxes.create'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/taxes/CreatePage')
                ->has('jurisdictions')
        );
    }

    public function test_backoffice_taxes_store(): void
    {
        $this->withoutExceptionHandling();

        $jurisdiction = Jurisdiction::factory()->create();

        $data = [
            'jurisdiction' => $jurisdiction->id,
            'name' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'rate' => $this->faker->randomFloat(2, 0, 100),
            'is_compound' => $this->faker->boolean(),
            'is_inclusive' => $this->faker->boolean(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.taxes.store'), $data);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.taxes.index'));

        $this->assertDatabaseHas('taxes', [
            'name' => data_get($data, 'name'),
            'rate' => data_get($data, 'rate'),
            'description' => data_get($data, 'description'),
            'jurisdiction_id' => data_get($data, 'jurisdiction'),
            'is_compound' => data_get($data, 'is_compound'),
            'is_inclusive' => data_get($data, 'is_inclusive'),
        ]);
    }

    public function test_backoffice_taxes_show(): void
    {
        $this->withoutExceptionHandling();

        $tax = Tax::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.taxes.show', $tax));

        $response->assertStatus(200);

        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/taxes/ShowPage')
                ->has('tax')
        );
    }

    public function test_backoffice_taxes_edit(): void
    {
        $this->withoutExceptionHandling();

        $tax = Tax::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.taxes.edit', $tax));

        $response->assertStatus(200);

        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/taxes/EditPage')
                ->has('tax')
                ->has('jurisdictions')
        );
    }

    public function test_backoffice_taxes_update(): void
    {
        $this->withoutExceptionHandling();

        $tax = Tax::factory()->create();

        $jurisdiction = Jurisdiction::factory()->create();

        $data = [
            'jurisdiction' => $jurisdiction->id,
            'name' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'rate' => $this->faker->randomFloat(2, 0, 100),
            'is_compound' => $this->faker->boolean(),
            'is_inclusive' => $this->faker->boolean(),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.taxes.update', $tax), $data);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.taxes.index'));

        $this->assertDatabaseHas('taxes', [
            'id' => $tax->id,
            'name' => data_get($data, 'name'),
            'rate' => data_get($data, 'rate'),
            'description' => data_get($data, 'description'),
            'jurisdiction_id' => data_get($data, 'jurisdiction'),
            'is_compound' => data_get($data, 'is_compound'),
            'is_inclusive' => data_get($data, 'is_inclusive'),
        ]);
    }

    public function test_backoffice_taxes_destroy(): void
    {
        $this->withoutExceptionHandling();

        $tax = Tax::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.taxes.destroy', $tax));

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.taxes.index'));

        $this->assertSoftDeleted($tax);
    }
}

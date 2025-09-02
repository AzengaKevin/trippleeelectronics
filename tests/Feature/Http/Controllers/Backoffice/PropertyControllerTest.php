<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class PropertyControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(
            permissions: [
                'access-backoffice',
                'browse-properties',
                'create-properties',
                'update-properties',
                'delete-properties',
                'import-properties',
                'export-properties',
            ]
        );
    }

    public function test_backoffice_properties_index_route()
    {
        Property::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.properties.index'));

        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/properties/IndexPage')
                ->has('properties.data', 2)
                ->has('params')
        );
    }

    public function test_backoffice_properties_create_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.properties.create'));

        $response->assertInertia(fn ($page) => $page->component('backoffice/properties/CreatePage'));
    }

    public function test_backoffice_properties_store_route(): void
    {
        $payload = [
            'name' => $this->faker->sentence(),
            'code' => $this->faker->lexify('CODE-????'),
            'address' => $this->faker->address(),
            'active' => $this->faker->boolean(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.properties.store'), $payload);

        $response->assertRedirect(route('backoffice.properties.index'));

        $this->assertDatabaseHas('properties', $payload);
    }

    public function test_backoffice_properties_show_route(): void
    {
        $property = Property::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.properties.show', $property));

        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/properties/ShowPage')
                ->has('property')
                ->where('property.id', $property->id)
        );
    }
}

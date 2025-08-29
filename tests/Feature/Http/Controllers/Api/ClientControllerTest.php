<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Individual;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClientControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_api_clients_route_happy_path(): void
    {
        Individual::factory()->count($individualsCount = 2)->create();

        $response = $this->getJson(route('api.clients.index'));

        $response->assertJson(
            fn ($json) => $json->has('data', $individualsCount)->etc()
        );
    }

    public function test_api_clients_route_happy_path_with_oragnizations(): void
    {
        Organization::factory()->count($organizationsCount = 2)->create();

        $response = $this->getJson(route('api.clients.index'));

        $response->assertJson(
            fn ($json) => $json->has('data', $organizationsCount)->etc()
        );
    }

    public function test_api_clients_route_happy_path_with_query(): void
    {
        Individual::factory()->create(['name' => 'John Doe']);

        Individual::factory()->create(['name' => 'Jane Doe']);

        $response = $this->getJson(route('api.clients.index', ['query' => 'John']));

        $response->assertJson(
            fn ($json) => $json->has('data', 1)->etc()
        );
    }
}

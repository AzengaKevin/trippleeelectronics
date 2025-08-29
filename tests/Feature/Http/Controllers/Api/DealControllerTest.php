<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Individual;
use App\Models\Order;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DealControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_api_deals_index_route(): void
    {
        Order::factory()->count($ordersCount = 2)->create();

        Purchase::factory()->count($purchasesCount = 3)->create();

        $response = $this->getJson(route('api.deals.index'));

        $response->assertSuccessful();

        $response->assertJson(
            fn ($json) => $json->has('data', $ordersCount + $purchasesCount)
                ->etc()
        );
    }

    public function test_api_deals_index_route_client(): void
    {

        $client = Individual::factory()->create();

        Order::factory()->count($clientOrdersCount = 2)->for($client, 'customer')->create();

        Order::factory()->count(2)->create();

        Purchase::factory()->count($clientPurchasesCount = 3)->for($client, 'supplier')->create();

        Purchase::factory()->count(3)->create();

        $response = $this->getJson(route('api.deals.index', ['client' => $client->id]));

        $response->assertSuccessful();

        $response->assertJson(
            fn ($json) => $json->has('data', $clientOrdersCount + $clientPurchasesCount)->etc()
        );
    }
}

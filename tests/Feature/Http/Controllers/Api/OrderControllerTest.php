<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_api_orders_index_route(): void
    {
        Order::factory()->count(3)->create();

        $response = $this->getJson(route('api.orders.index'));

        $response->assertStatus(200);

        $response->assertJson(fn (AssertableJson $assertableJson) => $assertableJson->has('data')->etc());
    }
}

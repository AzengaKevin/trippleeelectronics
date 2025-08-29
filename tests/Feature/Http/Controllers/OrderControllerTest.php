<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    public function test_orders_receipt_route(): void
    {
        $user = $this->createUserWithPermissions(permissions: []);

        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->get(route('orders.receipt', ['order' => $order]));

        $response->assertOk();
    }
}

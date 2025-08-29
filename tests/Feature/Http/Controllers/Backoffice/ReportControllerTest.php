<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Enums\OrderStatus;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    private ?Store $store = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-reports',
            'print-reports',
        ]);

        $this->store = Store::factory()->create();

        $this->user->stores()->attach($this->store);
    }

    public function test_backoffice_reports_index_route_happy_path(): void
    {
        Order::factory()->has(OrderItem::factory()->for(Item::factory(), 'item'), 'items')->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.reports.index'));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->component('backoffice/reports/IndexPage')->hasAll('stores', 'authors', 'orderStatuses', 'statistics', 'params'));
    }

    public function test_backoffice_reports_print_route_happy_path(): void
    {
        Order::factory()->has(OrderItem::factory()->for(Item::factory(), 'item'), 'items')->count(2)->create([
            'order_status' => OrderStatus::COMPLETED,
        ]);

        $response = $this->actingAs($this->user)->get(route('backoffice.reports.print'));

        $response->assertOk();

        $response->assertSessionHasNoErrors();
    }

    public function test_backoffice_reports_pnl_route_happy_path(): void
    {
        Order::factory()->has(OrderItem::factory()->for(Item::factory(), 'item'), 'items')->count(2)->create(['order_status' => OrderStatus::COMPLETED]);

        $response = $this->actingAs($this->user)->get(route('backoffice.reports.pnl'));

        $response->assertOk();

        $response->assertInertia(fn ($page) => $page->component('backoffice/reports/PnlPage')->hasAll('stores', 'authors', 'params', 'items'));
    }

    public function test_backoffice_reports_pnl_print_route_happy_path(): void
    {
        Order::factory()->has(OrderItem::factory()->for(Item::factory(), 'item'), 'items')->count(2)->create(['order_status' => OrderStatus::COMPLETED]);

        $response = $this->actingAs($this->user)->get(route('backoffice.reports.pnl.print'));

        $response->assertOk();

        $response->assertSessionHasNoErrors();
    }
}

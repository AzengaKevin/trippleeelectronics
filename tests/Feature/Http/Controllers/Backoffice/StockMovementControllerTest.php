<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Enums\StockMovementType;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class StockMovementControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-stock-movements',
            'create-stock-movements',
            'read-stock-movements',
            'update-stock-movements',
            'delete-stock-movements',
            'export-stock-movements',
            'import-stock-movements',
            'transfer-stock',
        ]);
    }

    public function test_backoffice_stock_movements_index_route(): void
    {
        StockMovement::factory()->for(Store::factory())->for(Item::factory(), 'stockable')->count($count = 2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.stock-movements.index'));

        $response->assertStatus(200);

        $response->assertInertia(fn (AssertableInertia $page) => $page->has('stockMovements.data', $count)->has('stores')->has('params')->has('types'));
    }

    public function test_backoffice_stock_movements_create_route(): void
    {
        Store::factory()->count(2)->create();

        Item::factory()->count(2)->has(ItemVariant::factory()->count(2), 'variants')->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.stock-movements.create'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn ($response) => $response->component('backoffice/stock-movements/CreatePage')->hasAll(['stores', 'stockMovementTypes'])
        );
    }

    public function test_backoffice_stock_movements_store_route(): void
    {

        $this->withoutExceptionHandling();

        $store = Store::factory()->create();

        $item = Item::factory()->create(['quantity' => 0]);

        $payload = [
            'store' => $store->id,
            'product' => $item->id,
            'quantity' => $quantity = 100,
            'type' => $this->faker->randomElement(StockMovementType::options()),
            'description' => 'Restock',
            'cost_implication' => 200.0,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.stock-movements.store'), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.stock-movements.index'));

        $item->refresh();

        $this->assertDatabaseHas('stock_movements', [
            'author_user_id' => $this->user->id,
            'store_id' => $store->id,
            'stockable_type' => $item->getMorphClass(),
            'stockable_id' => $item->id,
            'quantity' => $quantity,
            'type' => $payload['type'],
            'description' => $payload['description'],
            'cost_implication' => $payload['cost_implication'],
        ])->assertDatabaseCount('stock_movements', 1);

        $this->assertDatabaseHas('stock_levels', [
            'store_id' => $store->id,
            'stockable_type' => $item->getMorphClass(),
            'stockable_id' => $item->id,
            'quantity' => $quantity,
        ]);

        $this->assertEquals($quantity, $item->quantity, 'Item quantity was not updated correctly after stock movement.');
    }

    public function test_backoffice_stock_movements_show_route(): void
    {
        $stockMovement = StockMovement::factory()->for(Store::factory())->for(Item::factory(), 'stockable')->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.stock-movements.show', $stockMovement));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('stockMovement')
                ->where('stockMovement.id', $stockMovement->id)
                ->has('stockMovement.stockable')
        );
    }

    public function test_backoffice_stock_movements_edit_route(): void
    {
        $stockMovement = StockMovement::factory()->for(Store::factory())->for(Item::factory(), 'stockable')->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.stock-movements.edit', $stockMovement));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('stockMovement')
                ->has('stores')->has('stockMovementTypes')
                ->where('stockMovement.id', $stockMovement->id)
        );
    }

    public function test_backoffice_stock_movements_update_route(): void
    {
        $item = Item::factory()->create(['quantity' => 0]);

        $stockMovement = StockMovement::factory()->for(Store::factory())->for($item, 'stockable')->create();

        $payload = [
            'store' => $stockMovement->store_id,
            'product' => $item->id,
            'quantity' => $quantity = 999,
            'type' => $this->faker->randomElement(StockMovementType::options()),
            'description' => 'Returned items',
            'cost_implication' => 100.0,
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.stock-movements.update', $stockMovement), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.stock-movements.show', $stockMovement));

        $this->assertDatabaseHas('stock_movements', [
            'id' => $stockMovement->id,
            'store_id' => $payload['store'],
            'stockable_type' => $item->getMorphClass(),
            'stockable_id' => $item->id,
            'quantity' => $quantity,
            'type' => $payload['type'],
            'description' => $payload['description'],
            'cost_implication' => $payload['cost_implication'],
        ]);

        $this->assertDatabaseHas('stock_levels', [
            'store_id' => $payload['store'],
            'stockable_type' => $item->getMorphClass(),
            'stockable_id' => $item->id,
            'quantity' => $quantity,
        ]);

        $item->refresh();

        $this->assertEquals($quantity, $item->quantity, 'Item quantity was not updated correctly after stock movement update.');
    }

    public function test_backoffice_stock_movements_destroy_route(): void
    {

        $item = Item::factory()->create();

        $store = Store::factory()->create();

        StockMovement::query()->create([
            'store_id' => $store->id,
            'stockable_id' => $item->id,
            'stockable_type' => $item->getMorphClass(),
            'quantity' => 50,
            'type' => StockMovementType::INITIAL,
            'description' => 'Initial stock',
            'cost_implication' => 100.0,
        ]);

        $stockMovement = StockMovement::factory()->for($store)->for($item, 'stockable')->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.stock-movements.destroy', $stockMovement));

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.stock-movements.index'));

        $this->assertSoftDeleted('stock_movements', [
            'id' => $stockMovement->id,
        ]);
    }

    public function test_backoffice_stock_movements_destroy_route_forever(): void
    {

        $item = Item::factory()->create();

        $store = Store::factory()->create();

        StockMovement::query()->create([
            'store_id' => $store->id,
            'stockable_id' => $item->id,
            'stockable_type' => $item->getMorphClass(),
            'quantity' => 50,
            'type' => StockMovementType::INITIAL,
            'description' => 'Initial stock',
            'cost_implication' => 100.0,
        ]);

        $stockMovement = StockMovement::factory()->for($store)->for($item, 'stockable')->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.stock-movements.destroy', [$stockMovement, 'forever' => true]));

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.stock-movements.index'));

        $this->assertDatabaseMissing('stock_movements', [
            'id' => $stockMovement->id,
        ]);
    }

    public function test_backoffice_stock_movements_export_route(): void
    {
        Excel::fake();

        StockMovement::factory()->for(Store::factory())->for(Item::factory(), 'stockable')->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.stock-movements.export'));

        $response->assertStatus(200);

        Excel::assertDownloaded(StockMovement::getExportFileName());
    }

    public function test_backoffice_stock_movements_import_route_get_method(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.stock-movements.import'));

        $response->assertOk();
    }

    public function test_backoffice_stock_movements_import_route_post_method(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->user)->post(route('backoffice.stock-movements.import'), [
            'file' => \Illuminate\Http\UploadedFile::fake()->create('stock_movements.xlsx', 1024),
        ]);

        $response->assertStatus(302);
    }

    public function test_backoffice_stock_movements_initial_items_stock_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.stock-movements.initial-items-stock'));

        $response->assertStatus(200);

        $response->assertInertia(fn (AssertableInertia $page) => $page->component('backoffice/stock-movements/InitialItemsStockPage')->hasAll('items', 'stores', 'categories', 'brands', 'params'));
    }

    public function test_backoffice_stock_movements_initial_items_stock_export_route(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->user)->get(route('backoffice.stock-movements.initial-items-stock.export'));

        $response->assertStatus(200);

        Excel::assertDownloaded('initial-items-stock.xlsx');
    }

    public function test_backoffice_stock_movements_initial_items_stock_import_route(): void
    {

        Excel::fake();

        $payload = [
            'file' => \Illuminate\Http\UploadedFile::fake()->create('initial_items_stock.xlsx', 576, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.stock-movements.initial-items-stock.import'), $payload);

        $response->assertRedirect();

        $response->assertSessionHasNoErrors();
    }

    public function test_backoffice_stock_movements_transfer_route(): void
    {
        $item = Item::factory()->create();

        $fromStore = Store::factory()->create();

        StockMovement::factory()->for($fromStore)->for($item, 'stockable')->create([
            'quantity' => 50,
            'type' => StockMovementType::PURCHASE->value,
            'description' => 'Initial stock',
            'cost_implication' => 0.0,
        ]);

        $toStore = Store::factory()->create();

        $payload = [
            'from' => $fromStore->id,
            'to' => $toStore->id,
            'item' => $item->id,
            'quantity' => 10,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.stock-movements.transfer'), $payload);

        // Check for from stock movement
        $fromStockMovement = StockMovement::where('store_id', $fromStore->id)
            ->where('author_user_id', $this->user->id)
            ->where('stockable_type', $item->getMorphClass())
            ->where('stockable_id', $item->id)
            ->where('quantity', $payload['quantity'] * -1)
            ->where('type', StockMovementType::TRANSFER->value)
            ->first();

        $this->assertNotNull($fromStockMovement, 'From stock movement was not created.');

        // Check for to stock movement

        $toStockMovement = StockMovement::where('store_id', $toStore->id)
            ->where('author_user_id', $this->user->id)
            ->where('stockable_type', $item->getMorphClass())
            ->where('stockable_id', $item->id)
            ->where('quantity', $payload['quantity'])
            ->where('type', StockMovementType::TRANSFER->value)
            ->first();

        $this->assertNotNull($toStockMovement, 'To stock movement was not created.');

        $response->assertStatus(302);

        $response->assertSessionHasNoErrors();
    }
}

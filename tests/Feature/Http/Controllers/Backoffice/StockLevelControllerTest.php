<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\StockLevel;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class StockLevelControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-stock-levels',
            'create-stock-levels',
            'read-stock-levels',
            'update-stock-levels',
            'delete-stock-levels',
            'export-stock-levels',
            'import-stock-levels',
        ]);
    }

    public function test_backoffice_stock_levels_index_route(): void
    {
        StockLevel::factory()->for(Store::factory())->for(Item::factory(), 'stockable')->count($count = 2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.stock-levels.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page->has('stockLevels.data', $count));
    }

    public function test_backoffice_stock_levels_create_route(): void
    {
        Store::factory()->count(2)->create();

        Item::factory()->count(2)->has(ItemVariant::factory()->count(2), 'variants')->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.stock-levels.create'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn ($response) => $response->has('stores')
                ->has('items')->has('stockableTypes')
        );
    }

    public function test_backoffice_stock_levels_store_route(): void
    {

        $this->withoutExceptionHandling();

        $store = Store::factory()->create();

        $item = Item::factory()->create();

        $payload = [
            'store' => $store->id,
            'stockable_type' => $item->getMorphClass(),
            'stockable_id' => $item->id,
            'quantity' => 100,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.stock-levels.store'), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.stock-levels.index'));

        $this->assertDatabaseHas('stock_levels', [
            'store_id' => $store->id,
            'stockable_type' => $item->getMorphClass(),
            'stockable_id' => $item->id,
            'quantity' => 100,
        ])->assertDatabaseCount('stock_levels', 1);
    }

    public function test_backoffice_stock_levels_show_route(): void
    {
        $stock = StockLevel::factory()->for(Store::factory())->for(Item::factory(), 'stockable')->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.stock-levels.show', $stock));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('stockLevel')
                ->where('stockLevel.id', $stock->id)
        );
    }

    public function test_backoffice_stock_levels_edit_route(): void
    {
        $stock = StockLevel::factory()->for(Store::factory())->for(Item::factory(), 'stockable')->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.stock-levels.edit', $stock));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('stockLevel')
                ->has('stores')->has('items')->has('stockableTypes')
                ->where('stockLevel.id', $stock->id)
        );
    }

    public function test_backoffice_stock_levels_update_route(): void
    {
        $stock = StockLevel::factory()->for(Store::factory())->for(Item::factory(), 'stockable')->create();

        $payload = [
            'store' => $stock->store_id,
            'stockable_type' => $stock->stockable->getMorphClass(),
            'stockable_id' => $stock->stockable_id,
            'quantity' => 999,
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.stock-levels.update', $stock), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.stock-levels.show', $stock));

        $this->assertDatabaseHas('stock_levels', [
            'id' => $stock->id,
            'quantity' => 999,
        ]);
    }

    public function test_backoffice_stock_levels_destroy_route(): void
    {
        $stock = StockLevel::factory()->for(Store::factory())->for(Item::factory(), 'stockable')->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.stock-levels.destroy', $stock));

        $response->assertStatus(302);
        $response->assertRedirect(route('backoffice.stock-levels.index'));

        $this->assertSoftDeleted('stock_levels', [
            'id' => $stock->id,
        ]);
    }

    public function test_backoffice_stock_levels_destroy_route_forever(): void
    {
        $stock = StockLevel::factory()->for(Store::factory())->for(Item::factory(), 'stockable')->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.stock-levels.destroy', [$stock, 'forever' => true]));

        $response->assertStatus(302);
        $response->assertRedirect(route('backoffice.stock-levels.index'));

        $this->assertDatabaseMissing('stock_levels', [
            'id' => $stock->id,
        ]);
    }

    public function test_backoffice_stock_levels_export_route(): void
    {
        Excel::fake();

        StockLevel::factory()->for(Store::factory())->for(Item::factory(), 'stockable')->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.stock-levels.export'));

        $response->assertStatus(200);
        Excel::assertDownloaded(StockLevel::getExportFileName());
    }

    public function test_backoffice_stock_levels_import_route_get_method(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.stock-levels.import'));

        $response->assertOk();
    }

    public function test_backoffice_stock_levels_import_route_post_method(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->user)->post(route('backoffice.stock-levels.import'), [
            'file' => \Illuminate\Http\UploadedFile::fake()->create('stock_levels.xlsx', 1024),
        ]);

        $response->assertStatus(302);
    }
}

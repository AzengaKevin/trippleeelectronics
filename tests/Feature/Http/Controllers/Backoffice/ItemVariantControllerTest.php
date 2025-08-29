<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class ItemVariantControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-item-variants',
            'create-item-variants',
            'update-item-variants',
            'delete-item-variants',
            'import-item-variants',
            'export-item-variants',
        ]);
    }

    public function test_backoffice_item_variants_index_route(): void
    {
        ItemVariant::factory()->count($variantsCount = 2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.item-variants.index'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('itemVariants.data', $variantsCount)
        );
    }

    public function test_backoffice_item_variants_create_route(): void
    {
        $item = Item::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.item-variants.create', ['item' => $item->id]));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('item')->has('items')
                ->where('item.id', $item->id)
                ->where('item.name', $item->name)
                ->where('items.0.id', $item->id)
                ->where('items.0.name', $item->name)
        );
    }

    public function test_backoffice_item_variant_store_route(): void
    {
        $item = Item::factory()->create();

        $payload = [
            'item' => $item->id,
            'attribute' => $this->faker->word(),
            'value' => $this->faker->word(),
            'name' => $this->faker->name(),
            'cost' => $this->faker->randomFloat(2, 10, 100),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.item-variants.store', ['item' => $item]), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.item-variants.index'));

        $this->assertDatabaseHas('item_variants', [
            'author_user_id' => $this->user->id,
            'item_id' => $item->id,
            'attribute' => $payload['attribute'],
            'value' => $payload['value'],
            'name' => $payload['name'],
            'cost' => $payload['cost'],
            'price' => $payload['price'],
            'description' => $payload['description'],
        ]);
    }

    public function test_backoffice_item_variant_store_route_with_pos_details(): void
    {
        $item = Item::factory()->create();

        $payload = [
            'item' => $item->id,
            'attribute' => $this->faker->word(),
            'value' => $this->faker->word(),
            'name' => $this->faker->name(),
            'cost' => $this->faker->randomFloat(2, 10, 100),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'description' => $this->faker->sentence(),
            'pos_name' => $this->faker->name(),
            'pos_description' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.item-variants.store', ['item' => $item]), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.item-variants.index'));

        $this->assertDatabaseHas('item_variants', [
            'author_user_id' => $this->user->id,
            'item_id' => $item->id,
            'attribute' => $payload['attribute'],
            'value' => $payload['value'],
            'name' => $payload['name'],
            'cost' => $payload['cost'],
            'price' => $payload['price'],
            'description' => $payload['description'],
            'pos_name' => $payload['pos_name'],
            'pos_description' => $payload['pos_description'],
        ]);
    }

    public function test_backoffice_item_variant_store_route_with_image(): void
    {
        Storage::fake('public');

        $item = Item::factory()->create();

        $payload = [
            'item' => $item->id,
            'attribute' => $this->faker->word(),
            'value' => $this->faker->word(),
            'name' => $this->faker->name(),
            'image' => \Illuminate\Http\UploadedFile::fake()->image('item.jpg'),
            'cost' => $this->faker->randomFloat(2, 10, 100),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.item-variants.store', ['item' => $item]), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.item-variants.index'));

        $itemVariant = ItemVariant::query()->where('name', '=', $payload['name'])->first();

        $this->assertNotNull($itemVariant->author);

        $this->assertCount(1, $itemVariant->getMedia());
    }

    public function test_backoffice_item_variant_show_route(): void
    {
        $itemVariant = ItemVariant::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.item-variants.show', $itemVariant));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('itemVariant')
                ->where('itemVariant.id', $itemVariant->id)
                ->where('itemVariant.sku', $itemVariant->sku)
                ->where('itemVariant.name', $itemVariant->name)
                ->where('itemVariant.description', $itemVariant->description)
        );
    }

    public function test_backoffice_item_variant_edit_route(): void
    {
        $itemVariant = ItemVariant::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.item-variants.edit', $itemVariant));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->has('items')
                ->has('itemVariant')
                ->where('itemVariant.id', $itemVariant->id)
                ->where('itemVariant.sku', $itemVariant->sku)
                ->where('itemVariant.name', $itemVariant->name)
        );
    }

    public function test_backoffice_item_variant_update_route(): void
    {
        $itemVariant = ItemVariant::factory()->for(Item::factory())->create();

        $item = Item::factory()->create();

        $payload = [
            'item' => $item->id,
            'attribute' => $this->faker->unique()->word(),
            'value' => $this->faker->unique()->word(),
            'name' => $this->faker->name(),
            'cost' => $this->faker->randomFloat(2, 10, 100),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.item-variants.update', $itemVariant), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.item-variants.show', $itemVariant));

        $this->assertDatabaseHas('item_variants', [
            'attribute' => $payload['attribute'],
            'value' => $payload['value'],
            'name' => $payload['name'],
            'cost' => $payload['cost'],
            'price' => $payload['price'],
            'description' => $payload['description'],
        ]);
    }

    public function test_backoffice_item_variant_update_route_with_pos_details(): void
    {
        $itemVariant = ItemVariant::factory()->for(Item::factory())->create();

        $item = Item::factory()->create();

        $payload = [
            'item' => $item->id,
            'attribute' => $this->faker->unique()->word(),
            'value' => $this->faker->unique()->word(),
            'name' => $this->faker->name(),
            'cost' => $this->faker->randomFloat(2, 10, 100),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'description' => $this->faker->sentence(),
            'pos_name' => $this->faker->name(),
            'pos_description' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.item-variants.update', $itemVariant), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.item-variants.show', $itemVariant));

        $this->assertDatabaseHas('item_variants', [
            'attribute' => $payload['attribute'],
            'value' => $payload['value'],
            'name' => $payload['name'],
            'cost' => $payload['cost'],
            'price' => $payload['price'],
            'description' => $payload['description'],
            'pos_name' => $payload['pos_name'],
            'pos_description' => $payload['pos_description'],
        ]);
    }

    public function test_backoffice_item_variant_update_route_with_image(): void
    {
        Storage::fake('public');

        $itemVariant = ItemVariant::factory()->for(Item::factory())->create();

        $item = Item::factory()->create();

        $payload = [
            'item' => $item->id,
            'attribute' => $this->faker->unique()->word(),
            'value' => $this->faker->unique()->word(),
            'name' => $this->faker->name(),
            'image' => UploadedFile::fake()->image('variant.jpg'),
            'cost' => $this->faker->randomFloat(2, 10, 100),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.item-variants.update', $itemVariant), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.item-variants.show', $itemVariant));

        $this->assertCount(1, $itemVariant->fresh()->getMedia());
    }

    public function test_backoffice_item_variant_destroy_route(): void
    {
        $itemVariant = ItemVariant::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.item-variants.destroy', $itemVariant));

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.item-variants.index'));

        $this->assertSoftDeleted('item_variants', [
            'id' => $itemVariant->id,
            'sku' => $itemVariant->sku,
            'name' => $itemVariant->name,
        ]);
    }

    public function test_backoffice_item_variant_export_route(): void
    {
        Excel::fake();

        ItemVariant::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.item-variants.export'));

        $response->assertStatus(200);

        Excel::assertDownloaded(ItemVariant::getExportFileName());
    }

    public function test_backoffice_item_variant_import_route_get_method(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.item-variants.import'));

        $response->assertOk();
    }

    public function test_backoffice_item_variant_import_route_post_method(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->user)->post(route('backoffice.item-variants.import'), [
            'file' => \Illuminate\Http\UploadedFile::fake()->create('item_variants.xlsx', 1024),
        ]);

        $response->assertStatus(302);
    }
}

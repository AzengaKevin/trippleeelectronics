<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Enums\ClientType;
use App\Models\Individual;
use App\Models\Item;
use App\Models\Organization;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class QuotationControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-quotations',
            'create-quotations',
            'update-quotations',
            'delete-quotations',
            'import-quotations',
            'export-quotations',
        ]);
    }

    public function test_backoffice_quotation_index_route(): void
    {
        Quotation::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.quotations.index'));

        $response->assertOk();

        $response->assertInertia(
            fn ($inertia) => $inertia
                ->component('backoffice/quotations/IndexPage')
                ->has('quotations')
                ->has('params')
                ->has('stores')
        );
    }

    public function test_backoffice_quotation_create_route(): void
    {
        Item::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.quotations.create'));

        $response->assertOk();

        $response->assertInertia(
            fn ($inertia) => $inertia
                ->component('backoffice/quotations/CreatePage')
                ->has('stores')
                ->has('primaryTax')
        );
    }

    public function test_backoffice_quotation_store_route(): void
    {
        $this->withoutExceptionHandling();

        $items = $this->createItems();

        $customerData = $this->createCustomerData();

        $amount = collect($items)->sum(fn ($item) => $item['quantity'] * $item['price']);
        $shippingCost = $this->faker->randomFloat(2, 100, 1_000);
        $totalAmount = $amount + $shippingCost;

        $payload = [
            'items' => $items,
            'customer' => $customerData,
            'shipping_cost' => $shippingCost,
            'amount' => $amount,
            'total_amount' => $totalAmount,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.quotations.store'), $payload);

        $customer = data_get($customerData, 'type') === ClientType::INDIVIDUAL->value
            ? Individual::where('name', $customerData['id'])->first()
            : Organization::where('name', $customerData['id'])->first();

        $quotation = Quotation::where('customer_id', $customer->id)->first();

        $this->assertNotNull($quotation);

        collect($items)->each(function ($item) use ($quotation) {
            $this->assertDatabaseHas('quotation_items', [
                'quotation_id' => $quotation->id,
                'item_id' => $item['product'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        });

        $response->assertRedirect(route('backoffice.quotations.show', $quotation->id));
    }

    public function test_backoffice_quotation_show_route(): void
    {
        $quotation = Quotation::factory()->has(QuotationItem::factory()->count(2)->for(Item::factory()), 'items')->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.quotations.show', $quotation->id));

        $response->assertOk();

        $response->assertInertia(
            fn ($inertia) => $inertia
                ->component('backoffice/quotations/ShowPage')
                ->has('quotation')
        );
    }

    public function test_backoffice_quotation_edit_route(): void
    {
        $quotation = Quotation::factory()->has(QuotationItem::factory()->count(2)->for(Item::factory()), 'items')->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.quotations.edit', $quotation->id));

        $response->assertOk();

        $response->assertInertia(
            fn ($inertia) => $inertia
                ->component('backoffice/quotations/EditPage')
                ->has('quotation')
                ->has('stores')
                ->has('primaryTax')
        );
    }

    public function test_backoffice_quotation_update_route(): void
    {

        $this->withoutExceptionHandling();

        $quotation = Quotation::factory()->has(QuotationItem::factory()->count(2)->for(Item::factory()), 'items')->create();

        $items = $this->createItems();

        $customerData = $this->createCustomerData();

        $amount = collect($items)->sum(fn ($item) => $item['quantity'] * $item['price']);
        $shippingCost = $this->faker->randomFloat(2, 100, 1_000);
        $totalAmount = $amount + $shippingCost;

        $payload = [
            'items' => $items,
            'customer' => $customerData,
            'shipping_amount' => $shippingCost,
            'amount' => $amount,
            'total_amount' => $totalAmount,
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.quotations.update', $quotation->id), $payload);

        $response->assertRedirect(route('backoffice.quotations.show', $quotation->id));

        // Assert that the quotation was updated
        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'amount' => $amount,
            'shipping_amount' => $shippingCost,
            'total_amount' => $totalAmount,
        ]);
    }

    public function test_backoffice_quotation_delete_route(): void
    {
        $quotation = Quotation::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.quotations.destroy', $quotation->id));

        $response->assertRedirect(route('backoffice.quotations.index'));

        $this->assertSoftDeleted('quotations', [
            'id' => $quotation->id,
        ]);
    }

    public function test_backoffice_quotation_export_route(): void
    {
        $this->withoutExceptionHandling();

        Excel::fake();

        Quotation::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.quotations.export'));

        $response->assertOk();

        Excel::assertDownloaded(Quotation::getExportFilename());
    }

    private function createItems(): array
    {
        return Item::factory(2)->create([
            'quantity' => $this->faker->numberBetween(20, 100),
        ])->map(function ($item) {
            return [
                'product' => $item->id,
                'quantity' => $this->faker->numberBetween(1, 10),
                'price' => $this->faker->randomFloat(2, 100, 1_000),
            ];
        })->toArray();
    }

    private function createCustomerData(): array
    {
        $individual = $this->faker->boolean();

        return [
            'type' => $individual ? ClientType::INDIVIDUAL->value : ClientType::ORGANIZATION->value,
            'id' => $individual ? $this->faker->name() : $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
            'address' => $this->faker->address(),
        ];
    }
}

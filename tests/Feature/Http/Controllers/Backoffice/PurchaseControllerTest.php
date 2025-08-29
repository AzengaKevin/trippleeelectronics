<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Enums\ClientType;
use App\Models\Enums\PaymentStatus;
use App\Models\Individual;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\Organization;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Store;
use App\Models\User;
use App\Settings\PaymentSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class PurchaseControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    private Store $store;

    private PaymentMethod $cashPaymentMethod;

    private PaymentMethod $mpesaPaymentMethod;

    private PaymentMethod $paybillPaymentMethod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-purchases',
            'create-purchases',
            'import-purchases',
            'export-purchases',
            'read-purchases',
            'update-purchases',
            'delete-purchases',
        ]);

        $this->store = Store::factory()->create();

        $this->cashPaymentMethod = PaymentMethod::factory()->create(['name' => 'Cash']);

        $this->mpesaPaymentMethod = PaymentMethod::factory()->create(['name' => 'Mpesa']);

        $this->paybillPaymentMethod = PaymentMethod::factory()->create(['name' => 'Paybill']);

        PaymentSettings::fake([
            'cash_payment_method' => $this->cashPaymentMethod->id,
            'mpesa_payment_method' => $this->mpesaPaymentMethod->id,
        ]);
    }

    public function test_backoffice_purchases_index_route(): void
    {

        Purchase::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.purchases.index'));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->has('purchases'));
    }

    public function test_backoffice_purchases_create_route(): void
    {

        Item::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.purchases.create'));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->hasAll('stores', 'paymentMethods'));
    }

    public function test_backoffice_purchases_store_route(): void
    {
        $this->withoutExceptionHandling();

        $items = $this->createItems();
        $supplierData = $this->createSupplierData();
        $amount = $this->faker->randomFloat(2, 10_000, 100_000);
        $shippingAmount = $this->faker->randomFloat(2, 1_000, 10_000);
        $totalAmount = $amount + $shippingAmount;

        $payload = [
            'store' => $this->store->id,
            'items' => $items,
            'supplier' => $supplierData,
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.purchases.store'), $payload);

        $response->assertRedirect();

        $supplier = data_get($supplierData, 'type') === ClientType::INDIVIDUAL->value
            ? Individual::where('name', $supplierData['id'])->first()
            : Organization::where('name', $supplierData['id'])->first();

        $this->assertNotNull($supplier);

        $this->assertDatabaseHas('purchases', [
            'author_user_id' => $this->user->id,
            'supplier_id' => $supplier->id,
            'supplier_type' => $supplier->getMorphClass(),
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
        ]);

        $purchase = Purchase::where('supplier_id', $supplier->id)->first();

        foreach ($items as $item) {
            $this->assertDatabaseHas('purchase_items', [
                'purchase_id' => $purchase->id,
                'item_id' => $item['product'],
                'quantity' => $item['quantity'],
                'cost' => $item['cost'],
            ]);

            $this->assertDatabaseHas('stock_movements', [
                'action_id' => $purchase->id,
                'action_type' => $purchase->getMorphClass(),
                'stockable_id' => $item['product'],
                'quantity' => $item['quantity'],
            ]);

            $product = Item::find($item['product']) ?? ItemVariant::find($item['product']);

            $this->assertEquals($item['cost'], $product->cost, "The cost of the product {$product->name} does not match the cost in the purchase item.");
        }
    }

    public function test_backoffice_purchases_store_route_without_supplier(): void
    {
        $this->withoutExceptionHandling();

        $items = $this->createItems();
        $amount = $this->faker->randomFloat(2, 10_000, 100_000);
        $shippingAmount = $this->faker->randomFloat(2, 1_000, 10_000);
        $totalAmount = $amount + $shippingAmount;

        $payload = [
            'store' => $this->store->id,
            'items' => $items,
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.purchases.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('purchases', [
            'author_user_id' => $this->user->id,
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
        ]);

        $purchase = Purchase::query()->first();

        foreach ($items as $item) {
            $this->assertDatabaseHas('purchase_items', [
                'purchase_id' => $purchase->id,
                'item_id' => $item['product'],
                'quantity' => $item['quantity'],
                'cost' => $item['cost'],
            ]);

            $this->assertDatabaseHas('stock_movements', [
                'action_id' => $purchase->id,
                'action_type' => $purchase->getMorphClass(),
                'stockable_id' => $item['product'],
                'quantity' => $item['quantity'],
            ]);

            $product = Item::find($item['product']) ?? ItemVariant::find($item['product']);

            $this->assertEquals($item['cost'], $product->cost, "The cost of the product {$product->name} does not match the cost in the purchase item.");
        }
    }

    public function test_backoffice_purchases_store_route_with_store(): void
    {
        $this->withoutExceptionHandling();

        $store = Store::factory()->create();

        $items = $this->createItems();
        $supplierData = $this->createSupplierData();
        $amount = $this->faker->randomFloat(2, 10_000, 100_000);
        $shippingAmount = $this->faker->randomFloat(2, 1_000, 10_000);
        $totalAmount = $amount + $shippingAmount;

        $payload = [
            'items' => $items,
            'supplier' => $supplierData,
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
            'store' => $store->id,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.purchases.store'), $payload);

        $response->assertRedirect();

        $supplier = data_get($supplierData, 'type') === ClientType::INDIVIDUAL->value
            ? Individual::where('name', $supplierData['id'])->first()
            : Organization::where('name', $supplierData['id'])->first();

        $this->assertNotNull($supplier);

        $this->assertDatabaseHas('purchases', [
            'store_id' => $store->id,
            'supplier_id' => $supplier->id,
            'supplier_type' => $supplier->getMorphClass(),
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
        ]);

        $purchase = Purchase::where('supplier_id', $supplier->id)->first();

        foreach ($items as $item) {
            $this->assertDatabaseHas('purchase_items', [
                'purchase_id' => $purchase->id,
                'item_id' => $item['product'],
                'quantity' => $item['quantity'],
                'cost' => $item['cost'],
            ]);

            $this->assertDatabaseHas('stock_movements', [
                'store_id' => $store->id,
                'action_id' => $purchase->id,
                'action_type' => $purchase->getMorphClass(),
                'stockable_id' => $item['product'],
                'quantity' => $item['quantity'],
            ]);

            $product = Item::find($item['product']) ?? ItemVariant::find($item['product']);

            $this->assertEquals($item['cost'], $product->cost, "The cost of the product {$product->name} does not match the cost in the purchase item.");
        }
    }

    public function test_backoffice_purchases_store_route_with_payments(): void
    {
        $this->withoutExceptionHandling();

        $store = Store::factory()->create();

        $items = $this->createItems();
        $supplierData = $this->createSupplierData();
        $amount = $this->faker->randomFloat(2, 10_000, 100_000);
        $shippingAmount = $this->faker->randomFloat(2, 1_000, 10_000);
        $totalAmount = $amount + $shippingAmount;

        $payments = [
            [
                ...$this->createPaymentForMethod($this->cashPaymentMethod, $firstMethodAmount = $this->faker->randomFloat(2, 100, $amount / 2)),
            ],
            [
                ...$this->createPaymentForMethod($this->paybillPaymentMethod, $amount - $firstMethodAmount),
            ],
        ];

        $payload = [
            'items' => $items,
            'supplier' => $supplierData,
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
            'store' => $store->id,
            'payments' => $payments,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.purchases.store'), $payload);

        $response->assertRedirect();

        $supplier = data_get($supplierData, 'type') === ClientType::INDIVIDUAL->value
            ? Individual::where('name', $supplierData['id'])->first()
            : Organization::where('name', $supplierData['id'])->first();

        $this->assertNotNull($supplier);

        $this->assertDatabaseHas('purchases', [
            'store_id' => $store->id,
            'supplier_id' => $supplier->id,
            'supplier_type' => $supplier->getMorphClass(),
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
        ]);

        $purchase = Purchase::where('supplier_id', $supplier->id)->first();

        collect($payments)->each(function ($paymentData) use ($purchase) {
            $this->assertDatabaseHas('payments', [
                'author_user_id' => $this->user->id,
                'payable_id' => $purchase->id,
                'payable_type' => $purchase->getMorphClass(),
                'payment_method_id' => $paymentData['payment_method'],
                'amount' => $paymentData['amount'],
                'status' => PaymentStatus::PAID->value,
            ]);
        });

        foreach ($items as $item) {
            $this->assertDatabaseHas('purchase_items', [
                'purchase_id' => $purchase->id,
                'item_id' => $item['product'],
                'quantity' => $item['quantity'],
                'cost' => $item['cost'],
            ]);

            $this->assertDatabaseHas('stock_movements', [
                'store_id' => $store->id,
                'action_id' => $purchase->id,
                'action_type' => $purchase->getMorphClass(),
                'stockable_id' => $item['product'],
                'quantity' => $item['quantity'],
            ]);

            $product = Item::find($item['product']) ?? ItemVariant::find($item['product']);

            $this->assertEquals($item['cost'], $product->cost, "The cost of the product {$product->name} does not match the cost in the purchase item.");
        }
    }

    public function test_backoffice_purchases_store_route_with_store_and_existing_organization(): void
    {
        $this->withoutExceptionHandling();

        $store = Store::factory()->create();

        $items = $this->createItems();

        $supplier = Organization::factory()->create();

        $supplierData = [
            'id' => $supplier->id,
        ];

        $amount = $this->faker->randomFloat(2, 10_000, 100_000);
        $shippingAmount = $this->faker->randomFloat(2, 1_000, 10_000);
        $totalAmount = $amount + $shippingAmount;

        $payload = [
            'items' => $items,
            'supplier' => $supplierData,
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
            'store' => $store->id,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.purchases.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('purchases', [
            'store_id' => $store->id,
            'supplier_id' => $supplier->id,
            'supplier_type' => $supplier->getMorphClass(),
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
        ]);

        $purchase = Purchase::where('supplier_id', $supplier->id)->first();

        foreach ($items as $item) {
            $this->assertDatabaseHas('purchase_items', [
                'purchase_id' => $purchase->id,
                'item_id' => $item['product'],
                'quantity' => $item['quantity'],
                'cost' => $item['cost'],
            ]);

            $this->assertDatabaseHas('stock_movements', [
                'store_id' => $store->id,
                'action_id' => $purchase->id,
                'action_type' => $purchase->getMorphClass(),
                'stockable_id' => $item['product'],
                'quantity' => $item['quantity'],
            ]);

            $product = Item::find($item['product']) ?? ItemVariant::find($item['product']);

            $this->assertEquals($item['cost'], $product->cost, "The cost of the product {$product->name} does not match the cost in the purchase item.");
        }
    }

    public function test_backoffice_purchases_show_route(): void
    {
        $purchase = Purchase::factory()->has(PurchaseItem::factory()->for(Item::factory(), 'item'), 'items')->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.purchases.show', $purchase));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->has('purchase')->has('stores')->has('paymentMethods'));
    }

    public function test_backoffice_purchases_edit_route(): void
    {
        $purchase = Purchase::factory()->has(PurchaseItem::factory()->for(Item::factory(), 'item'), 'items')->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.purchases.edit', $purchase));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->has('purchase')->hasAll('purchase', 'stores', 'paymentMethods'));
    }

    public function test_backoffice_purchases_update_route(): void
    {
        $supplier = Organization::factory()->create();

        $purchase = Purchase::factory()->has(PurchaseItem::factory()->for(Item::factory(), 'item'), 'items')->for($supplier, 'supplier')->create();

        $items = $this->createItems();
        $supplierData = ['id' => $supplier->id];
        $amount = $this->faker->randomFloat(2, 10_000, 100_000);
        $shippingAmount = $this->faker->randomFloat(2, 1_000, 10_000);
        $totalAmount = $amount + $shippingAmount;

        $payload = [
            'store' => $this->store->id,
            'items' => $items,
            'supplier' => $supplierData,
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
        ];

        $response = $this->actingAs($this->user)->patch(route('backoffice.purchases.update', $purchase), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('purchases', [
            'supplier_id' => $supplier->id,
            'supplier_type' => $supplier->getMorphClass(),
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
        ]);

        $this->assertEquals(count($items), PurchaseItem::where('purchase_id', $purchase->id)->count());

        foreach ($items as $item) {
            $this->assertDatabaseHas('purchase_items', [
                'purchase_id' => $purchase->id,
                'item_id' => $item['product'],
                'quantity' => $item['quantity'],
                'cost' => $item['cost'],
            ]);
        }
    }

    public function test_backoffice_purchases_update_route_with_store(): void
    {
        $store = Store::factory()->create();

        $supplier = Organization::factory()->create();

        $purchase = Purchase::factory()->has(PurchaseItem::factory()->for(Item::factory(), 'item'), 'items')->for($supplier, 'supplier')->create();

        $items = $this->createItems();
        $supplierData = ['id' => $supplier->id];
        $amount = $this->faker->randomFloat(2, 10_000, 100_000);
        $shippingAmount = $this->faker->randomFloat(2, 1_000, 10_000);
        $totalAmount = $amount + $shippingAmount;

        $payload = [
            'items' => $items,
            'supplier' => $supplierData,
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
            'store' => $store->id,
        ];

        $response = $this->actingAs($this->user)->patch(route('backoffice.purchases.update', $purchase), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('purchases', [
            'store_id' => $store->id,
            'supplier_id' => $supplier->id,
            'supplier_type' => $supplier->getMorphClass(),
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
        ]);

        $this->assertEquals(count($items), PurchaseItem::where('purchase_id', $purchase->id)->count());

        foreach ($items as $item) {
            $this->assertDatabaseHas('purchase_items', [
                'purchase_id' => $purchase->id,
                'item_id' => $item['product'],
                'quantity' => $item['quantity'],
                'cost' => $item['cost'],
            ]);
        }
    }

    public function test_backoffice_purchases_destroy_route(): void
    {
        $purchase = Purchase::factory()->has(PurchaseItem::factory()->for(Item::factory(), 'item'), 'items')->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.purchases.destroy', $purchase));

        $response->assertRedirect(route('backoffice.purchases.index'));

        $this->assertSoftDeleted('purchases', [
            'id' => $purchase->id,
        ]);
    }

    public function test_backoffice_purchases_export_route(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->user)->get(route('backoffice.purchases.export'));

        $response->assertStatus(200);

        Excel::assertDownloaded(Purchase::getExportFilename());
    }

    private function createPaymentForMethod(PaymentMethod $paymentMethod, float $amount): array
    {
        return [
            'payment_method' => $paymentMethod->id,
            'amount' => $amount,
        ];
    }

    private function createItems(): array
    {
        return Item::factory(2)->create()->map(function ($item) {
            return [
                'product' => $item->id,
                'quantity' => $this->faker->numberBetween(1, 10),
                'cost' => $this->faker->randomFloat(2, 100, 1_000),
            ];
        })->all();
    }

    private function createSupplierData(): array
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

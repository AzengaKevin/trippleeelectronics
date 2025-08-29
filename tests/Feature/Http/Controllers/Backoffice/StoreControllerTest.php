<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\PaymentMethod;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-stores',
            'create-stores',
            'read-stores',
            'update-stores',
            'delete-stores',
            'export-stores',
            'import-stores',
        ]);
    }

    public function test_backoffice_stores_index_route(): void
    {
        Store::factory()->count($storesCount = 2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.stores.index'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('stores.data', $storesCount)
        );
    }

    public function test_backoffice_stores_create_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.stores.create'));

        $response->assertStatus(200);

        $response->assertInertia(fn (AssertableInertia $assertableInertia) => $assertableInertia->component('backoffice/stores/CreatePage')->has('methods'));
    }

    public function test_backoffice_stores_store_route(): void
    {
        $payload = [
            'name' => $this->faker->unique()->company(),
            'address' => $this->faker->address(),
            'location' => $this->faker->city(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.stores.store'), $payload);

        $response->assertStatus(302);
        $response->assertRedirect(route('backoffice.stores.index'));

        $this->assertDatabaseHas('stores', [
            ...$payload,
            'author_user_id' => $this->user->id,
        ]);
    }

    public function test_backoffice_stores_store_route_with_short_name(): void
    {
        $payload = [
            'name' => $this->faker->unique()->company(),
            'short_name' => str($this->faker->lexify('???'))->upper()->value(),
            'address' => $this->faker->address(),
            'location' => $this->faker->city(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.stores.store'), $payload);

        $response->assertStatus(302);
        $response->assertRedirect(route('backoffice.stores.index'));

        $this->assertDatabaseHas('stores', [
            ...$payload,
            'author_user_id' => $this->user->id,
        ]);
    }

    public function test_backoffice_stores_store_route_with_printing_details(): void
    {

        $payload = [
            'name' => $this->faker->unique()->company(),
            'address' => $this->faker->address(),
            'location' => $this->faker->city(),
            'name' => $this->faker->unique()->company(),
            'phone' => $this->faker->phoneNumber(),
            'paybill' => $this->faker->numerify('######'),
            'account_number' => $this->faker->numerify('ACC-######'),
            'till' => $this->faker->numerify('######'),
            'kra_pin' => $this->faker->numerify('KRA-######'),
            'address' => $this->faker->address(),
            'location' => $this->faker->city(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.stores.store'), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.stores.index'));

        $this->assertDatabaseHas('stores', [
            ...$payload,
            'author_user_id' => $this->user->id,
        ]);
    }

    public function test_backoffice_store_store_route_with_payment_methods(): void
    {

        $this->withoutExceptionHandling();

        $paymentMethods = PaymentMethod::factory()->count($paymentMethodsCount = 2)->create();

        $methods = $paymentMethods->map(fn (PaymentMethod $method) => [
            'id' => $method->id,
            'phone_number' => $this->faker->phoneNumber(),
            'paybill_number' => $this->faker->numerify('######'),
            'account_number' => $this->faker->numerify('ACC-######'),
            'till_number' => $this->faker->numerify('######'),
            'account_name' => $this->faker->name(),
        ])->all();

        $payload = [
            'name' => $this->faker->unique()->company(),
            'short_name' => str($this->faker->lexify('???'))->upper()->value(),
            'address' => $this->faker->address(),
            'location' => $this->faker->city(),
            'name' => $this->faker->unique()->company(),
            'phone' => $this->faker->phoneNumber(),
            'paybill' => $this->faker->numerify('######'),
            'account_number' => $this->faker->numerify('ACC-######'),
            'till' => $this->faker->numerify('######'),
            'kra_pin' => $this->faker->numerify('KRA-######'),
            'address' => $this->faker->address(),
            'location' => $this->faker->city(),
            'payment_methods' => $methods,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.stores.store'), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.stores.index'));

        $this->assertDatabaseHas('stores', [
            'name' => $payload['name'],
            'short_name' => $payload['short_name'],
            'phone' => $payload['phone'],
            'paybill' => $payload['paybill'],
            'account_number' => $payload['account_number'],
            'till' => $payload['till'],
            'kra_pin' => $payload['kra_pin'],
            'address' => $payload['address'],
            'location' => $payload['location'],
            'author_user_id' => $this->user->id,
        ]);

        $store = Store::where('name', $payload['name'])->firstOrFail();

        $this->assertCount($paymentMethodsCount, $store->paymentMethods);

        collect($methods)->each(function ($method) use ($store) {
            $this->assertDatabaseHas('payment_method_store', [
                'store_id' => $store->id,
                'payment_method_id' => $method['id'],
                'phone_number' => $method['phone_number'],
                'paybill_number' => $method['paybill_number'],
                'account_number' => $method['account_number'],
                'till_number' => $method['till_number'],
                'account_name' => $method['account_name'],
            ]);
        });
    }

    public function test_backoffice_stores_show_route(): void
    {
        $store = Store::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.stores.show', $store));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->component('backoffice/stores/ShowPage')
                ->has('store')
                ->has('store.payment_methods')
                ->where('store.id', $store->id)
                ->where('store.name', $store->name)
                ->where('store.address', $store->address)
                ->where('store.location', $store->location)
        );
    }

    public function test_backoffice_stores_edit_route(): void
    {
        $store = Store::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.stores.edit', $store));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->component('backoffice/stores/EditPage')
                ->has('methods')
                ->has('store')
                ->has('store.payment_methods')
                ->where('store.id', $store->id)
                ->where('store.name', $store->name)
                ->where('store.address', $store->address)
                ->where('store.location', $store->location)
        );
    }

    public function test_backoffice_stores_update_route(): void
    {
        $store = Store::factory()->create();

        $payload = [
            'name' => $this->faker->company(),
            'address' => $this->faker->address(),
            'location' => $this->faker->city(),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.stores.update', $store), $payload);

        $response->assertStatus(302);
        $response->assertRedirect(route('backoffice.stores.show', $store));

        $this->assertDatabaseHas('stores', $payload);
    }

    public function test_backoffice_stores_update_route_with_short_name(): void
    {
        $store = Store::factory()->create();

        $payload = [
            'name' => $this->faker->company(),
            'short_name' => str($this->faker->lexify('???'))->upper()->value(),
            'address' => $this->faker->address(),
            'location' => $this->faker->city(),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.stores.update', $store), $payload);

        $response->assertStatus(302);
        $response->assertRedirect(route('backoffice.stores.show', $store));

        $this->assertDatabaseHas('stores', [
            ...$payload,
        ]);
    }

    public function test_backoffice_stores_update_route_with_printing_details(): void
    {
        $store = Store::factory()->create();

        $payload = [
            'name' => $this->faker->company(),
            'address' => $this->faker->address(),
            'location' => $this->faker->city(),
            'phone' => $this->faker->phoneNumber(),
            'paybill' => $this->faker->numerify('######'),
            'account_number' => $this->faker->numerify('ACC-######'),
            'till' => $this->faker->numerify('######'),
            'kra_pin' => $this->faker->numerify('KRA-######'),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.stores.update', $store), $payload);

        $response->assertStatus(302);
        $response->assertRedirect(route('backoffice.stores.show', $store));

        $this->assertDatabaseHas('stores', [
            ...$payload,
        ]);
    }

    public function test_backoffice_stores_update_route_with_payment_methods(): void
    {
        $this->withoutExceptionHandling();

        $store = Store::factory()->create();

        $paymentMethods = PaymentMethod::factory()->count($paymentMethodsCount = 2)->create();

        $methods = $paymentMethods->map(fn (PaymentMethod $method) => [
            'id' => $method->id,
            'phone_number' => $this->faker->phoneNumber(),
            'paybill_number' => $this->faker->numerify('######'),
            'account_number' => $this->faker->numerify('ACC-######'),
            'till_number' => $this->faker->numerify('######'),
            'account_name' => $this->faker->name(),
        ])->all();

        $payload = [
            'name' => $this->faker->company(),
            'short_name' => str($this->faker->lexify('???'))->upper()->value(),
            'address' => $this->faker->address(),
            'location' => $this->faker->city(),
            'phone' => $this->faker->phoneNumber(),
            'paybill' => $this->faker->numerify('######'),
            'account_number' => $this->faker->numerify('ACC-######'),
            'till' => $this->faker->numerify('######'),
            'kra_pin' => $this->faker->numerify('KRA-######'),
            'payment_methods' => $methods,
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.stores.update', $store), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.stores.show', $store));

        $this->assertDatabaseHas('stores', [
            'id' => $store->id,
            'name' => $payload['name'],
            'short_name' => $payload['short_name'],
            'phone' => $payload['phone'],
            'paybill' => $payload['paybill'],
            'account_number' => $payload['account_number'],
            'till' => $payload['till'],
            'kra_pin' => $payload['kra_pin'],
        ]);

        $store->refresh();

        collect($methods)->each(function ($method) use ($store) {

            $this->assertDatabaseHas('payment_method_store', [
                'store_id' => $store->id,
                'payment_method_id' => $method['id'],
                'phone_number' => $method['phone_number'],
                'paybill_number' => $method['paybill_number'],
                'account_number' => $method['account_number'],
                'till_number' => $method['till_number'],
                'account_name' => $method['account_name'],
            ]);
        });

        $this->assertCount($paymentMethodsCount, $store->paymentMethods);
    }

    public function test_backoffice_stores_destroy_route(): void
    {
        $store = Store::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.stores.destroy', $store));

        $response->assertStatus(302);
        $response->assertRedirect(route('backoffice.stores.index'));

        $this->assertSoftDeleted('stores', [
            'id' => $store->id,
            'name' => $store->name,
            'address' => $store->address,
            'location' => $store->location,
        ]);
    }

    public function test_backoffice_stores_destroy_route_forever(): void
    {
        $store = Store::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.stores.destroy', [$store, 'forever' => true]));

        $response->assertStatus(302);
        $response->assertRedirect(route('backoffice.stores.index'));

        $this->assertDatabaseMissing('stores', [
            'id' => $store->id,
            'name' => $store->name,
            'address' => $store->address,
            'location' => $store->location,
        ]);
    }

    public function test_backoffice_stores_export_route(): void
    {
        Excel::fake();

        Store::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.stores.export'));

        $response->assertStatus(200);

        Excel::assertDownloaded(Store::getExportFileName());
    }

    public function test_backoffice_stores_import_route_get_method(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.stores.import'));

        $response->assertOk();
    }

    public function test_backoffice_stores_import_route_post_method(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->user)->post(route('backoffice.stores.import'), [
            'file' => \Illuminate\Http\UploadedFile::fake()->create('stores.xlsx', 1024),
        ]);

        $response->assertStatus(302);
    }
}

<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Enums\PaymentMethodFieldOption;
use App\Models\Enums\PaymentStatus;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class PaymentMethodControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-payment-methods',
            'create-payment-methods',
            'update-payment-methods',
            'delete-payment-methods',
            'import-payment-methods',
            'export-payment-methods',
        ]);
    }

    public function test_backoffice_payment_methods_index_route()
    {
        $this->withoutExceptionHandling();

        PaymentMethod::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.payment-methods.index'));

        $response->assertOk();

        $response->assertInertia(
            fn ($inertia) => $inertia
                ->component('backoffice/payment-methods/IndexPage')
                ->has('methods.data', 2)
                ->has('params')
        );
    }

    public function test_backoffice_payment_methods_create_route()
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.payment-methods.create'));

        $response->assertOk();

        $response->assertInertia(
            fn ($inertia) => $inertia
                ->component('backoffice/payment-methods/CreatePage')
                ->has('fields')
                ->has('statuses')
        );
    }

    public function test_backoffice_payment_methods_store_route()
    {
        $this->withoutExceptionHandling();

        $data = [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.payment-methods.store'), $data);

        $response->assertRedirect(route('backoffice.payment-methods.index'));

        $this->assertDatabaseHas('payment_methods', [
            'name' => $data['name'],
            'description' => $data['description'],
        ]);
    }

    public function test_backoffice_payment_methods_store_route_for_paybill()
    {
        $this->withoutExceptionHandling();

        $properties = [
            PaymentMethodFieldOption::PAYBILL_NUMBER->value => '247247',
            PaymentMethodFieldOption::ACCOUNT_NUMBER->value => '838888',
            PaymentMethodFieldOption::ACCOUNT_NAME->value => 'Kisii Computer Store',
        ];

        $data = [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'required_fields' => [
                PaymentMethodFieldOption::PAYBILL_NUMBER->value,
                PaymentMethodFieldOption::ACCOUNT_NUMBER->value,
                PaymentMethodFieldOption::ACCOUNT_NAME->value,
            ],
            'default_payment_status' => PaymentStatus::UNPAID->value,
            ...$properties,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.payment-methods.store'), $data);

        $response->assertRedirect(route('backoffice.payment-methods.index'));

        $this->assertDatabaseHas('payment_methods', [
            'name' => $data['name'],
            'description' => $data['description'],
            'default_payment_status' => $data['default_payment_status'],
            ...$properties,
        ]);

        $paymentMethod = PaymentMethod::query()->where('name', $data['name'])->first();

        $this->assertNotNull($paymentMethod);

        $this->assertEquals($paymentMethod['required_fields'], $paymentMethod->required_fields);
        $this->assertEquals($paymentMethod['properties'], $properties);
    }

    public function test_backoffice_payment_methods_show_route()
    {
        $paymentMethod = PaymentMethod::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.payment-methods.show', $paymentMethod));

        $response->assertOk();

        $response->assertInertia(
            fn ($inertia) => $inertia
                ->component('backoffice/payment-methods/ShowPage')
                ->has('method')
        );
    }

    public function test_backoffice_payment_methods_edit_route()
    {
        $paymentMethod = PaymentMethod::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.payment-methods.edit', $paymentMethod));

        $response->assertOk();

        $response->assertInertia(
            fn ($inertia) => $inertia
                ->component('backoffice/payment-methods/EditPage')
                ->has('method')
                ->has('fields')
                ->has('statuses')
        );
    }

    public function test_backoffice_payment_methods_update_route()
    {
        $this->withoutExceptionHandling();

        $paymentMethod = PaymentMethod::factory()->create();

        $data = [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.payment-methods.update', $paymentMethod), $data);

        $response->assertRedirect(route('backoffice.payment-methods.index'));

        $this->assertDatabaseHas('payment_methods', [
            'id' => $paymentMethod->id,
            'name' => $data['name'],
            'description' => $data['description'],
        ]);
    }

    public function test_backoffice_payment_methods_update_route_for_paybill()
    {
        $this->withoutExceptionHandling();

        $paymentMethod = PaymentMethod::factory()->create();

        $properties = [
            PaymentMethodFieldOption::PAYBILL_NUMBER->value => '247247',
            PaymentMethodFieldOption::ACCOUNT_NUMBER->value => '838888',
            PaymentMethodFieldOption::ACCOUNT_NAME->value => 'Kisii Computer Store',
        ];

        $data = [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'required_fields' => [
                PaymentMethodFieldOption::PAYBILL_NUMBER->value,
                PaymentMethodFieldOption::ACCOUNT_NUMBER->value,
                PaymentMethodFieldOption::ACCOUNT_NAME->value,
            ],
            'default_payment_status' => PaymentStatus::UNPAID->value,
            ...$properties,
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.payment-methods.update', $paymentMethod), $data);

        $response->assertRedirect(route('backoffice.payment-methods.index'));

        $this->assertDatabaseHas('payment_methods', [
            'id' => $paymentMethod->id,
            'name' => $data['name'],
            'description' => $data['description'],
            'default_payment_status' => $data['default_payment_status'],
            ...$properties,
        ]);

        $paymentMethod->refresh();

        $this->assertEquals($paymentMethod['required_fields'], $paymentMethod->required_fields);
        $this->assertEquals($paymentMethod['properties'], $properties);
    }

    public function test_backoffice_payment_methods_destroy_route()
    {
        $this->withoutExceptionHandling();

        $paymentMethod = PaymentMethod::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.payment-methods.destroy', $paymentMethod));

        $response->assertRedirect(route('backoffice.payment-methods.index'));

        $this->assertSoftDeleted('payment_methods', [
            'id' => $paymentMethod->id,
        ]);
    }

    public function test_backoffice_payment_methods_export_route()
    {
        $this->withoutExceptionHandling();

        Excel::fake();

        PaymentMethod::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.payment-methods.export'));

        $response->assertOk();

        Excel::assertDownloaded(PaymentMethod::getExportFilename());
    }

    public function test_backoffice_payment_methods_import_route()
    {
        $this->withoutExceptionHandling();

        Excel::fake();

        $file = UploadedFile::fake()->create('payment_methods.xlsx');

        $response = $this->actingAs($this->user)->post(route('backoffice.payment-methods.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('backoffice.payment-methods.index'));

        $response->assertSessionHasNoErrors();
    }
}

<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Enums\PaymentStatus;
use App\Models\Individual;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-payments',
            'create-payments',
            'read-payments',
            'update-payments',
            'delete-payments',
            'export-payments',
            'import-payments',
        ]);
    }

    public function test_backoffice_payments_index_route(): void
    {

        Payment::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.payments.index'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('backoffice/payments/IndexPage')
                ->has('payments.data', 2)
                ->has('stores')
        );
    }

    public function test_backoffice_payments_create_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.payments.create'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('backoffice/payments/CreatePage')
                ->has('methods')
                ->has('statuses')
        );
    }

    public function test_backoffice_payments_store_route(): void
    {

        $paymentMethod = PaymentMethod::factory()->create(['name' => 'Cash']);

        $data = [
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'payment_method' => $paymentMethod->id,
            'status' => $this->faker->randomElement(PaymentStatus::options()),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.payments.store'), $data);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.payments.index'));

        $this->assertDatabaseHas('payments', [
            'amount' => $data['amount'],
            'payment_method_id' => $data['payment_method'],
            'status' => $data['status'],
            'author_user_id' => $this->user->id,
        ]);
    }

    public function test_backoffice_payments_show_route(): void
    {
        $payment = Payment::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.payments.show', $payment));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('backoffice/payments/ShowPage')
                ->has('payment')
        );
    }

    public function test_backoffice_payments_edit_route(): void
    {
        $payment = Payment::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.payments.edit', $payment));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('backoffice/payments/EditPage')
                ->has('methods')
                ->has('statuses')
                ->has('payment')
        );
    }

    public function test_backoffice_payments_update_route(): void
    {
        $payment = Payment::factory()->create();

        $payable = Order::factory()->create();

        $payer = Individual::factory()->create();

        $paymentMethod = PaymentMethod::factory()->create(['name' => 'Card']);

        $data = [
            'payable' => $payable->id,
            'payer' => $payer->id,
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'payment_method' => $paymentMethod->id,
            'status' => $this->faker->randomElement(PaymentStatus::options()),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.payments.update', $payment), $data);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.payments.show', $payment));

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'payable_id' => $payable->id,
            'payer_id' => $payer->id,
            'amount' => $data['amount'],
            'payment_method_id' => $data['payment_method'],
            'status' => $data['status'],
        ]);
    }

    public function test_backoffice_payments_destroy_route(): void
    {
        $payment = Payment::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.payments.destroy', $payment));

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.payments.index'));

        $this->assertSoftDeleted('payments', [
            'id' => $payment->id,
        ]);
    }

    public function test_backoffice_payments_export_route(): void
    {
        Excel::fake();

        Payment::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.payments.export'));

        $response->assertStatus(200);

        Excel::assertDownloaded(Payment::getExportFilename());
    }

    public function test_backoffice_payments_import_route(): void
    {

        $response = $this->actingAs($this->user)->get(route('backoffice.payments.import'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('backoffice/payments/ImportPage')
        );
    }

    public function test_backoffice_payments_import_route_proscess(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->user)->post(route('backoffice.payments.import'), [
            'file' => UploadedFile::fake()->create('payments.xlsx', 1024)->size(1024)->mimeType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ]);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.payments.index'));
    }
}

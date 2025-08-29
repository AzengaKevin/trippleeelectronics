<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Enums\TransactionMethod;
use App\Models\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-transactions',
            'create-transactions',
            'update-transactions',
            'delete-transactions',
            'export-transactions',
            'import-transactions',
        ]);
    }

    public function test_backoffice_transactions_index_route(): void
    {
        Transaction::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.transactions.index'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('backoffice/transactions/IndexPage')
                ->has('transactions')
                ->has('transactions.data')
        );
    }

    public function test_backoffice_transactions_create_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.transactions.create'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('backoffice/transactions/CreatePage')
                ->hasAll(['statuses', 'methods'])
        );
    }

    public function test_backoffice_transactions_store_route(): void
    {
        $data = [
            'transaction_method' => $this->faker->randomElement(TransactionMethod::options()),
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'till' => $this->faker->numerify('######'),
            'paybill' => $this->faker->numerify('######'),
            'account_number' => $this->faker->numerify('######'),
            'phone_number' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
            'reference' => null,
            'fee' => $this->faker->randomFloat(2, 0, 100),
            'status' => $this->faker->randomElement(TransactionStatus::options()),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.transactions.store'), $data);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.transactions.index'));

        $this->assertDatabaseHas('transactions', [
            'amount' => $data['amount'],
            'status' => $data['status'],
            'transaction_method' => $data['transaction_method'],
            'till' => $data['till'],
            'paybill' => $data['paybill'],
            'account_number' => $data['account_number'],
            'phone_number' => $data['phone_number'],
            'reference' => $data['reference'],
            'fee' => $data['fee'],
            'author_user_id' => $this->user->id,
        ]);
    }

    public function test_backoffice_transactions_show_route(): void
    {
        $transaction = Transaction::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.transactions.show', $transaction));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('backoffice/transactions/ShowPage')
                ->has('transaction')
        );
    }

    public function test_backoffice_transactions_edit_route(): void
    {
        $transaction = Transaction::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.transactions.edit', $transaction));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('backoffice/transactions/EditPage')
                ->hasAll(['transaction', 'statuses', 'methods'])
        );
    }

    public function test_backoffice_transactions_update_route(): void
    {

        $transaction = Transaction::factory()->create();

        $data = [
            'transaction_method' => $this->faker->randomElement(TransactionMethod::options()),
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'till' => $this->faker->numerify('######'),
            'paybill' => $this->faker->numerify('######'),
            'account_number' => $this->faker->numerify('######'),
            'phone_number' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
            'reference' => $this->faker->numerify('##########'),
            'fee' => $this->faker->randomFloat(2, 0, 100),
            'status' => $this->faker->randomElement(TransactionStatus::options()),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.transactions.update', $transaction), $data);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.transactions.index'));

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => $data['amount'],
            'status' => $data['status'],
            'transaction_method' => $data['transaction_method'],
            'till' => $data['till'],
            'paybill' => $data['paybill'],
            'account_number' => $data['account_number'],
            'phone_number' => $data['phone_number'],
            'reference' => $data['reference'],
            'fee' => $data['fee'],
        ]);
    }

    public function test_backoffice_transactions_destroy_route(): void
    {
        $transaction = Transaction::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.transactions.destroy', $transaction));

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.transactions.index'));

        $this->assertSoftDeleted('transactions', [
            'id' => $transaction->id,
        ]);
    }

    public function test_backoffice_transactions_export_route(): void
    {
        Excel::fake();

        Transaction::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.transactions.export'));

        $response->assertStatus(200);

        Excel::assertDownloaded(Transaction::getExportFilename());
    }

    public function test_backoffice_transactions_import_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.transactions.import'));

        $response->assertStatus(200);

        $response->assertInertia(fn (AssertableInertia $page) => $page->component('backoffice/transactions/ImportPage'));
    }

    public function test_backoffice_transactions_process_import_route(): void
    {
        Excel::fake();

        $payload = [
            'file' => UploadedFile::fake()->create('transaxtions.xlsx'),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.transactions.import'), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.transactions.index'));
    }
}

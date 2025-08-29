<?php

namespace Tests\Feature\Imports;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionImportTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_importing_transactions_logic(): void
    {
        $path = base_path('test-data/transactions.xlsx');

        $transactionImport = new \App\Imports\TransactionImport;

        $transactionImport->import($path);

        $this->assertEquals(4, Transaction::query()->count());
    }
}

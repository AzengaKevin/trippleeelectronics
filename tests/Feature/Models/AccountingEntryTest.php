<?php

namespace Tests\Feature\Models;

use App\Models\Account;
use App\Models\AccountingEntry;
use App\Models\AccountingPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AccountingEntryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_accounting_entry_creation()
    {

        $account = Account::factory()->create();

        $accountingPeriod = AccountingPeriod::query()->create([
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
        ]);

        $attributes = [
            'account_id' => $account->id,
            'accounting_period_id' => $accountingPeriod->id,
            'amount' => $this->faker->randomFloat(2, 1, 1000) * $account->type->multiplier(),
        ];

        $accountingEntry = AccountingEntry::create($attributes);

        $this->assertDatabaseHas('accounting_entries', [
            'id' => $accountingEntry->id,
            ...$attributes,
        ]);

        $this->assertEquals($accountingEntry->account->id, $account->id);

        $this->assertEquals($accountingEntry->accountingPeriod->id, $accountingPeriod->id);
    }

    public function test_accounting_entry_creation_with_factory()
    {

        $account = Account::factory()->create();

        $accountingPeriod = AccountingPeriod::query()->create([
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
        ]);

        $accountingEntry = AccountingEntry::factory()->for($account)->for($accountingPeriod)->create();

        $this->assertDatabaseHas('accounting_entries', [
            'id' => $accountingEntry->id,
        ]);
    }
}

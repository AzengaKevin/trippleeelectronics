<?php

namespace Tests\Feature\Console\Commands;

use App\Models\AccountingPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PopulateAccountingEntriesCommandTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_populate_accounting_entries_command()
    {

        /** @var AccountingPeriod $accountingPeriod */
        $accountingPeriod = AccountingPeriod::query()->create([
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
        ]);

        $this->artisan('app:populate-accounting-entries', ['--choose' => true])
            ->expectsChoice('Choose an accounting period: ', $accountingPeriod->start_date->toDateString(), [$accountingPeriod->start_date->toDateString()])
            ->expectsOutput('Populating accounting entries...')
            ->assertExitCode(0);

        $this->assertDatabaseHas('accounting_entries', [
            'accounting_period_id' => $accountingPeriod->id,
        ]);
    }
}

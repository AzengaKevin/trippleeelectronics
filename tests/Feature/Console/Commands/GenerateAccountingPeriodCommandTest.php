<?php

namespace Tests\Feature\Console\Commands;

use App\Models\AccountingPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GenerateAccountingPeriodCommandTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_generate_accounting_periods_command()
    {
        $this->artisan('app:generate-accounting-period')
            ->expectsQuestion('Enter the start date (YYYY-MM-DD): ', '2023-01-01')
            ->expectsQuestion('Enter the end date (YYYY-MM-DD): ', '2023-12-31')
            ->expectsOutput('Generating accounting periods from 2023-01-01 to 2023-12-31...')
            ->assertExitCode(0);

        $this->assertEquals(12, AccountingPeriod::count());
    }
}

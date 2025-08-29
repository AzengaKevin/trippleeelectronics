<?php

namespace App\Console\Commands;

use App\Services\AccountingPeriodService;
use Illuminate\Console\Command;

class GenerateAccountingPeriodCommand extends Command
{
    protected $signature = 'app:generate-accounting-period';

    protected $description = 'Generate a new accounting periods base on the start and end dates';

    public function handle()
    {
        $this->info('Generating accounting periods...');

        $startDate = $this->ask('Enter the start date (YYYY-MM-DD): ');

        $endDate = $this->ask('Enter the end date (YYYY-MM-DD): ');

        app(AccountingPeriodService::class)->generateAccountingPeriods($startDate, $endDate);

        $this->info("Generating accounting periods from $startDate to $endDate...");
    }
}

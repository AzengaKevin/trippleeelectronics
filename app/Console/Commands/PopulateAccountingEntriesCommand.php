<?php

namespace App\Console\Commands;

use App\Services\AccountingEntryService;
use App\Services\AccountingPeriodService;
use Illuminate\Console\Command;

class PopulateAccountingEntriesCommand extends Command
{
    protected $signature = 'app:populate-accounting-entries {--choose}';

    protected $description = 'Populate accounting entries';

    public function handle()
    {
        if ($this->option('choose')) {

            $this->info('Choosing accounting period...');

            $accountingPeriods = app(AccountingPeriodService::class)->get(limit: 10);

            if ($accountingPeriods->isEmpty()) {
                $this->error('No accounting periods found.');

                return;
            }

            $accountingPeriodChoice = $this->choice('Choose an accounting period: ', $accountingPeriods->map(fn ($period) => $period->start_date->toDateString())->all());

            $selectedAccountingPeriod = $accountingPeriods->first(fn ($period) => $period->start_date->toDateString() == $accountingPeriodChoice);

            $this->info('Populating accounting entries for period: '.$selectedAccountingPeriod->name());

            app(AccountingEntryService::class)->populateAccountingEntriesForPeriod($selectedAccountingPeriod);
        }

        $this->info('Populating accounting entries...');
    }
}

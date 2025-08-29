<?php

namespace App\Console\Commands;

use App\Services\TaxableService;
use App\Services\TaxService;
use Illuminate\Console\Command;

class UpdateTaxablesCommand extends Command
{
    protected $signature = 'app:update-taxables';

    protected $description = 'Update taxables for all items and item variants';

    public function handle()
    {
        $this->info('Updating taxables for all items and item variants...');

        $primaryTax = app(TaxService::class)->fetchPrimaryTax();

        $taxableService = app(TaxableService::class);

        $taxableService->createItemsTaxables($primaryTax);

        $taxableService->createItemVariantsTaxables($primaryTax);

        $this->info('Taxables updated successfully.');

        return 0;
    }
}

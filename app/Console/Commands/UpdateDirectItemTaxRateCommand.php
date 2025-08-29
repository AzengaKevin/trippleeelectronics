<?php

namespace App\Console\Commands;

use App\Services\ItemService;
use App\Services\ItemVariantService;
use Illuminate\Console\Command;

class UpdateDirectItemTaxRateCommand extends Command
{
    protected $signature = 'app:update-direct-item-tax-rate';

    protected $description = 'Updated direct item tax rate';

    public function handle()
    {

        $this->info('Updating direct item tax rates...');

        app(ItemVariantService::class)->updateTaxRate();

        app(ItemService::class)->updateTaxRate();

        $this->info('Direct item tax rates updated successfully.');
    }
}

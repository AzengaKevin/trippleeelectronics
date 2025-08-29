<?php

namespace App\Console\Commands;

use App\Services\ItemService;
use App\Services\ItemVariantService;
use Illuminate\Console\Command;

class UpdateItemsQuantityCommand extends Command
{
    protected $signature = 'app:update-items-quantity';

    protected $description = 'Update the quantity of items in the database based on stock movements';

    public function handle()
    {
        $this->info('Starting to update items quantity...');

        app(ItemService::class)->updateQuantities();

        app(ItemVariantService::class)->updateQuantities();

        $this->info('Items quantity updated successfully!');

        return 0;
    }
}

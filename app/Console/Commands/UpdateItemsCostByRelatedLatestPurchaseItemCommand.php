<?php

namespace App\Console\Commands;

use App\Services\ItemService;
use App\Services\ItemVariantService;
use Illuminate\Console\Command;

class UpdateItemsCostByRelatedLatestPurchaseItemCommand extends Command
{
    protected $signature = 'app:update-items-cost-by-related-latest-purchase-item';

    protected $description = 'Update items cost by related latest purchase item';

    public function handle()
    {
        $this->info('Updating items cost by related latest purchase item...');

        app(ItemService::class)->updateCostByRelatedLatestPurchaseItem();

        app(ItemVariantService::class)->updateCostByRelatedLatestPurchaseItem();

        $this->info('Items cost updated successfully.');

        return 0;
    }
}

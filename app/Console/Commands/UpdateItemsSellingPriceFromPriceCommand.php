<?php

namespace App\Console\Commands;

use App\Services\ItemService;
use App\Services\ItemVariantService;
use Illuminate\Console\Command;

class UpdateItemsSellingPriceFromPriceCommand extends Command
{
    protected $signature = 'app:update-items-selling-price-from-price {--factor= : The factor to multiply the price by}';

    protected $description = 'Update items selling price from price';

    public function handle()
    {
        $factor = $this->option('factor');

        $factor = $factor ?: 1.1;

        $this->info("Updating items selling prices with factor: {$factor}");

        app(ItemService::class)->updateSellingPriceFromPrice($factor);

        app(ItemVariantService::class)->updateSellingPriceFromPrice($factor);

        $this->info('Items selling prices updated successfully.');

        return 0;
    }
}

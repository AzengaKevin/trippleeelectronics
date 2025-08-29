<?php

namespace App\Console\Commands;

use App\Services\StockLevelService;
use Illuminate\Console\Command;

class UpdateStockLevelsQuantityCommand extends Command
{
    protected $signature = 'app:update-stock-levels-quantity';

    protected $description = 'Update stock levels quantity based on stock movements grouped by stores and stockable items';

    public function handle()
    {
        $this->info('Updating stock levels quantity...');

        app(StockLevelService::class)->updateQuantities();

        $this->info('Updated stock levels quantities successfully.');
    }
}

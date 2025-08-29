<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Models\ItemVariant;
use Illuminate\Console\Command;

class SetSellingPricesCommand extends Command
{
    protected $signature = 'app:set-selling-prices';

    protected $description = 'Set the selling prices for products based on the current prices.';

    public function handle()
    {
        $this->info('Setting selling prices for products...');

        Item::whereNull('selling_price')
            ->orWhere('selling_price', 0)
            ->chunkById(100, function ($items) {
                foreach ($items as $item) {
                    $item->update([
                        'selling_price' => $item->price * 1.2, // Assuming a 20% markup for selling price
                    ]);
                }
            });

        ItemVariant::whereNull('selling_price')
            ->orWhere('selling_price', 0)
            ->chunkById(100, function ($variants) {
                foreach ($variants as $variant) {
                    $variant->update([
                        'selling_price' => $variant->price * 1.2, // Assuming a 20% markup for selling price
                    ]);
                }
            });

        $this->info('Selling prices have been set successfully.');

        return 0;
    }
}

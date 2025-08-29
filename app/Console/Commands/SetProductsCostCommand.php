<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Models\ItemVariant;
use Illuminate\Console\Command;

class SetProductsCostCommand extends Command
{
    protected $signature = 'app:set-products-cost';

    protected $description = 'Command description';

    public function handle()
    {
        $this->info('Setting products cost...');

        Item::whereNull('cost')
            ->orWhere('cost', 0)
            ->chunkById(100, function ($items) {
                foreach ($items as $item) {
                    $item->update([
                        'cost' => $item->price * 0.8, // Assuming a 20% cost from the price
                    ]);
                }
            });

        ItemVariant::whereNull('cost')
            ->orWhere('cost', 0)
            ->chunkById(100, function ($variants) {
                foreach ($variants as $variant) {
                    $variant->update([
                        'cost' => $variant->price * 0.8, // Assuming a 20% cost from the price
                    ]);
                }
            });

        $this->info('Products cost has been set successfully.');

        return 0;
    }
}

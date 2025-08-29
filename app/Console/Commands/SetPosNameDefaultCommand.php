<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Models\ItemVariant;
use Illuminate\Console\Command;

class SetPosNameDefaultCommand extends Command
{
    protected $signature = 'app:set-pos-name-default';

    protected $description = 'Set items and item variants default POS name from names';

    public function handle()
    {
        $this->info('Setting default POS names for items and item variants...');

        Item::whereNull('pos_name')
            ->orWhere('pos_name', '')
            ->chunkById(100, function ($items) {
                foreach ($items as $item) {
                    $item->update([
                        'pos_name' => str($item->name)->trim()->limit(40),
                    ]);
                }
            });

        // Set default POS name for ItemVariants
        ItemVariant::whereNull('pos_name')
            ->orWhere('pos_name', '')
            ->chunkById(100, function ($variants) {
                foreach ($variants as $variant) {
                    $variant->update([
                        'pos_name' => str($variant->name)->trim()->limit(40),
                    ]);
                }
            });

        $this->info('Default POS names have been set successfully.');

        return 0;
    }
}

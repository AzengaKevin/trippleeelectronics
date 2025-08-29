<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\Tax;

class TaxableService
{
    public function createItemsTaxables(Tax $tax): void
    {
        $items = Item::query()->pluck('id')->all();

        $tax->items()->sync($items);
    }

    public function createItemVariantsTaxables(Tax $tax): void
    {
        $itemVariants = ItemVariant::query()->pluck('id')->all();

        $tax->variants()->sync($itemVariants);
    }
}

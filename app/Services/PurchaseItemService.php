<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\PurchaseItem;

class PurchaseItemService
{
    public function create(array $data): PurchaseItem
    {

        $product = Item::query()->find(data_get($data, 'product')) ?? ItemVariant::query()->find(data_get($data, 'product'));

        $attributes = [
            'purchase_id' => data_get($data, 'purchase_id'),
            'item_type' => $product?->getMorphClass(),
            'item_id' => $product?->id,
            'quantity' => data_get($data, 'quantity'),
            'cost' => data_get($data, 'cost'),
        ];

        if ($product->cost !== data_get($data, 'cost')) {

            $product->update(['cost' => data_get($data, 'cost')]);
        }

        return PurchaseItem::query()->create($attributes);
    }
}

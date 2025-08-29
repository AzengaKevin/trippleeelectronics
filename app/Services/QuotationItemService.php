<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Models\CustomItem;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\QuotationItem;
use Ramsey\Uuid\Uuid;

class QuotationItemService
{
    public function create(array $data): QuotationItem
    {

        $id = data_get($data, 'product');

        $product = null;

        if (Uuid::isValid($id)) {

            $product = Item::query()->find($id) ?? ItemVariant::query()->find($id) ?? CustomItem::query()->find($id);
        } else {

            if ($id) {

                $product = CustomItem::query()->create([
                    'name' => $id,
                    'price' => data_get($data, 'price'),
                ]);
            }
        }

        if (! $product) {
            throw new CustomException('Product not found');
        }

        if (data_get($data, 'check_stock_availability') && ($product->quantity < data_get($data, 'quantity'))) {
            if ($product->getMorphClass() !== 'custom-item') {
                throw new CustomException("Insufficient {$product->name} quantity");
            }
        }

        $attributes = [
            'quotation_id' => data_get($data, 'quotation_id'),
            'item_type' => $product?->getMorphClass(),
            'item_id' => $product?->id,
            'quantity' => data_get($data, 'quantity'),
            'price' => data_get($data, 'price'),
            'taxable' => data_get($data, 'taxable', false),
            'tax_rate' => data_get($data, 'tax_rate', 0),
            'discount_rate' => data_get($data, 'discount_rate', 0),
        ];

        return QuotationItem::query()->create($attributes);
    }
}

<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Models\CustomItem;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\OrderItem;
use App\Models\StockLevel;
use Ramsey\Uuid\Uuid;

class OrderItemService
{
    public function create(array $data): OrderItem
    {
        $order = data_get($data, 'order');

        $order->loadMissing('store');

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

        if ($product->cost >= data_get($data, 'price') && $product->getMorphClass() !== 'custom-item') {

            $productCostPrice = $product->cost + 1;

            throw new CustomException("Price is too low for {$product->name}. Minimum price is {$productCostPrice}");
        }

        if (data_get($data, 'check_stock_availability') && $product->getMorphClass() !== 'custom-item') {

            if ($product->quantity < data_get($data, 'quantity')) {

                throw new CustomException("Insufficient {$product->name} quantity across all stores");
            }

            $stockLevelQuery = StockLevel::query()->where([
                ['store_id', $order->store_id],
                ['stockable_id', $product->id],
                ['stockable_type', $product->getMorphClass()],
            ]);

            if ($stockLevelQuery->doesntExist()) {
                app(StockMovementService::class)->updateStock($product);
            }

            $stockLevel = $stockLevelQuery->first();

            if (! $stockLevel || ($stockLevel->quantity < data_get($data, 'quantity'))) {

                throw new CustomException("Insufficient {$product->name} quantity in {$order->store->name}");
            }
        }

        $attributes = [
            'order_id' => $order->id,
            'item_type' => $product?->getMorphClass(),
            'item_id' => $product?->id,
            'quantity' => data_get($data, 'quantity'),
            'price' => data_get($data, 'price'),
            'taxable' => data_get($data, 'taxable', false),
            'tax_rate' => data_get($data, 'tax_rate'),
            'discount_rate' => data_get($data, 'discount_rate'),
        ];

        return OrderItem::query()->create($attributes);
    }
}

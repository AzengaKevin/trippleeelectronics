<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Models\Enums\StockMovementType;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    public function get(
        ?string $query = null,
        ?int $limit = null,
        ?string $storeId = null,
        ?array $with = ['store', 'stockable'],
        ?int $perPage = 24,
        ?string $orderBy = 'created_at',
        ?string $orderDirection = 'desc',
        ?bool $withoutStore = false,
        ?bool $withoutStockable = false,
        ?string $type = null,
    ) {
        $stockMovementQuery = StockMovement::query()
            ->when($with, function ($queryBuilder, $with) {
                $queryBuilder->with($with);
            })
            ->when($query, function ($queryBuilder, $query) {
                $queryBuilder->whereHas('stockable', function ($queryBuilder) use ($query) {

                    $dbDriver = DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

                    $caseInsensitiveNameQuery = match ($dbDriver) {
                        'mysql' => 'LOWER(name) LIKE LOWER(?)',
                        'pgsql' => 'name ILIKE ?',
                        'sqlite' => 'name LIKE ? COLLATE NOCASE',
                        default => 'name LIKE ?',
                    };

                    $queryBuilder->whereRaw($caseInsensitiveNameQuery, "%{$query}%");
                });
            })
            ->when($storeId, function ($queryBuilder, $storeId) {
                $queryBuilder->where('store_id', $storeId);
            })
            ->when($limit, function ($queryBuilder) use ($limit) {
                $queryBuilder->limit($limit);
            })
            ->when($withoutStore, function ($queryBuilder) {
                $queryBuilder->whereNull('store_id');
            })
            ->when($withoutStockable, function ($queryBuilder) {
                $queryBuilder->whereDoesntHave('stockable');
            })
            ->when($type, function ($queryBuilder, $type) {
                $queryBuilder->where('type', $type);
            });

        $stockMovementQuery->orderBy($orderBy, $orderDirection);

        return is_null($perPage)
            ? $stockMovementQuery->get()
            : $stockMovementQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data): StockMovement
    {
        return DB::transaction(function () use ($data) {

            $product = null;

            if ($productId = data_get($data, 'product')) {

                $product = Item::query()->find($productId);

                if (! $product) {
                    $product = ItemVariant::query()->find($productId);
                }
            }

            if (! $product) {
                throw new CustomException('Product not found');
            }

            $attributes = [
                'author_user_id' => data_get($data, 'author_user_id'),
                'store_id' => data_get($data, 'store'),
                'stockable_type' => $product->getMorphClass(),
                'stockable_id' => $product->id,
                'action_type' => data_get($data, 'action_type'),
                'action_id' => data_get($data, 'action_id'),
                'type' => data_get($data, 'type'),
                'quantity' => data_get($data, 'quantity'),
                'description' => data_get($data, 'description'),
                'cost_implication' => data_get($data, 'cost_implication'),
            ];

            return StockMovement::create($attributes);
        });
    }

    public function update(StockMovement $stockMovement, array $data): bool
    {
        return DB::transaction(function () use ($stockMovement, $data) {

            $product = null;

            if ($productId = data_get($data, 'product')) {

                $product = Item::query()->find($productId);

                if (! $product) {
                    $product = ItemVariant::query()->find($productId);
                }
            }

            if (! $product) {
                throw new CustomException('Product not found');
            }

            $attributes = [
                'store_id' => data_get($data, 'store'),
                'stockable_type' => data_get($data, 'stockable_type', $product->getMorphClass()),
                'stockable_id' => data_get($data, 'stockable_id', $product->id),
                'action_type' => data_get($data, 'action_type'),
                'action_id' => data_get($data, 'action_id'),
                'type' => data_get($data, 'type'),
                'quantity' => data_get($data, 'quantity'),
                'description' => data_get($data, 'description'),
                'cost_implication' => data_get($data, 'cost_implication'),
            ];

            return $stockMovement->update($attributes);
        });
    }

    public function delete(StockMovement $stockMovement, bool $forever = false): ?bool
    {
        if ($forever) {
            return $stockMovement->forceDelete();
        }

        return $stockMovement->delete();
    }

    public function importRow(array $data)
    {
        $values = [
            'store_id' => data_get($data, 'store_id'),
            'stockable_type' => data_get($data, 'stockable_type'),
            'stockable_id' => data_get($data, 'stockable_id'),
            'action_type' => data_get($data, 'action_type'),
            'action_id' => data_get($data, 'action_id'),
            'type' => data_get($data, 'type'),
            'quantity' => data_get($data, 'quantity'),
            'description' => data_get($data, 'description'),
            'cost_implication' => data_get($data, 'cost_implication'),
        ];

        StockMovement::query()->create($values);
    }

    public function updateStock($stockable)
    {

        $storeItemResults = DB::table('stock_movements')
            ->selectRaw('store_id, SUM(quantity) AS quantity_sum')
            ->where([['stockable_id', $stockable->id], ['stockable_type', $stockable->getMorphClass()]])
            ->groupBy('store_id')
            ->whereNull('stock_movements.deleted_at')
            ->get();

        $stockLevelsOkay = $storeItemResults->every(fn ($result) => $result->quantity_sum >= 0);

        if (! $stockLevelsOkay) {

            throw new CustomException('Stock levels cannot be negative');
        }

        $storeItemResults->each(function ($result) use ($stockable) {

            $attributes = [
                'stockable_id' => $stockable->id,
                'stockable_type' => $stockable->getMorphClass(),
                'store_id' => $result->store_id,
            ];

            $values = [
                'quantity' => $result->quantity_sum,
            ];

            StockLevel::query()->updateOrCreate($attributes, $values);
        });

        $itemQtySumResult = DB::table('stock_movements')
            ->selectRaw('SUM(quantity) AS quantity_sum')
            ->where([['stockable_id', $stockable->id], ['stockable_type', $stockable->getMorphClass()]])
            ->whereNull('stock_movements.deleted_at')
            ->sum('quantity');

        $itemsQtyOkay = $itemQtySumResult >= 0;

        if (! $itemsQtyOkay) {
            throw new CustomException('Item stock cannot be negative');
        }

        $stockable->update(['quantity' => $itemQtySumResult]);
    }

    public function getInitialItemsStock(
        ?Store $store = null,
        ?array $categoryIds = null,
        ?array $skipIds = null,
        ?string $brandId = null,
        ?int $perPage = 100,
        ?int $limit = null,
    ) {

        $itemStockQuery = Item::query()
            ->select('items.sku', 'items.name', 'stores.short_name as store_short_name', 'stores.name as store_name', 'items.cost', 'items.price', 'items.selling_price')
            ->addSelect([
                'quantity' => StockMovement::query()
                    ->select('stock_movements.quantity')
                    ->whereColumn('stock_movements.stockable_id', 'items.id')
                    ->whereColumn('stock_movements.store_id', 'stores.id')
                    ->where('stock_movements.type', StockMovementType::INITIAL->value)
                    ->limit(1),
            ])
            ->when($store, function ($queryBuilder) use ($store) {
                $queryBuilder->where('stores.id', $store->id);
            })
            ->when($categoryIds, function ($queryBuilder) use ($categoryIds) {
                $queryBuilder->whereIn('items.item_category_id', $categoryIds);
            })
            ->when($skipIds, function ($queryBuilder) use ($skipIds) {
                $queryBuilder->whereNotIn('items.id', $skipIds);
            })
            ->when($brandId, function ($queryBuilder) use ($brandId) {
                $queryBuilder->where('items.brand_id', $brandId);
            })
            ->when($limit, function ($queryBuilder) use ($limit) {
                $queryBuilder->limit($limit);
            })
            ->crossJoin('stores')
            ->orderBy('stores.name')
            ->orderBy('items.name');

        return is_null($perPage)
            ? $itemStockQuery->get()
            : $itemStockQuery->paginate($perPage)->withQueryString();
    }

    public function transferStock(array $data)
    {

        return DB::transaction(function () use ($data) {

            $fromStore = Store::query()->findOrFail(data_get($data, 'from'));

            $toStore = Store::query()->findOrFail(data_get($data, 'to'));

            $quantity = data_get($data, 'quantity', 1);

            $item = Item::query()->find(data_get($data, 'item')) ?? ItemVariant::query()->find(data_get($data, 'item'));

            if ($fromStore->id === $toStore->id) {
                throw new CustomException('Cannot transfer stock to the same store');
            }

            if (! $item) {
                throw new CustomException('Item not found');
            }

            // Check if the item has enough stock in the from store
            $fromStockLevel = StockLevel::query()
                ->where('store_id', $fromStore->id)
                ->where('stockable_id', $item->id)
                ->where('stockable_type', $item->getMorphClass())
                ->first();

            if (! $fromStockLevel || $fromStockLevel->quantity < $quantity) {
                throw new CustomException('Not enough stock in the from store');
            }

            StockMovement::create([
                'author_user_id' => data_get($data, 'author_user_id'),
                'store_id' => $fromStore->id,
                'stockable_type' => $item->getMorphClass(),
                'stockable_id' => $item->id,
                'type' => StockMovementType::TRANSFER,
                'quantity' => $quantity * -1,
                'description' => "Transferred {$quantity} items to {$toStore->name}",
                'cost_implication' => 0,
            ]);

            StockMovement::create([
                'author_user_id' => data_get($data, 'author_user_id'),
                'store_id' => $toStore->id,
                'stockable_type' => $item->getMorphClass(),
                'stockable_id' => $item->id,
                'type' => StockMovementType::TRANSFER,
                'quantity' => $quantity,
                'description' => "Received {$quantity} items from {$fromStore->name}",
                'cost_implication' => 0,
            ]);
        });
    }
}

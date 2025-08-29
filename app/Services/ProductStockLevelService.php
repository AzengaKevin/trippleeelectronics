<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ProductStockLevelService
{
    public function __construct(private StockLevelService $stockLevelService) {}

    public function get($product)
    {
        return $this->stockLevelService->get(productId: $product->id, perPage: null);
    }

    public function getMany($products = [], $store = null)
    {
        $dbDriver = DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $castQtyQuery = match ($dbDriver) {
            'mysql' => 'CAST(%s AS SIGNED) AS %s',
            'pgsql' => '%s::INTEGER AS %s',
            'sqlite' => 'CAST(%s AS INTEGER) AS %s',
            default => 'CAST(%s AS INTEGER) AS %s',
        };

        $itemQuery = DB::table('items')->select(
            'items.id AS product',
            'items.name AS product_name',
            'stores.id AS store_id',
            'stores.short_name AS store',
            DB::raw(sprintf($castQtyQuery, 'stock_levels.quantity', 'quantity')),
        )->crossJoin('stores')->leftJoin('stock_levels', function ($join) {
            $join->on('stock_levels.stockable_id', '=', 'items.id')
                ->on('stock_levels.store_id', '=', 'stores.id')
                ->where('stock_levels.stockable_type', '=', 'item');
        })->when($store, function ($queryBuilder) use ($store) {
            $queryBuilder->where('stores.id', '=', $store);
        })->when($products, function ($queryBuilder) use ($products) {
            $queryBuilder->whereIn('items.id', $products);
        });

        $itemVariantQuery = DB::table('item_variants')->select(
            'item_variants.id AS product',
            'item_variants.name AS product_name',
            'stores.id AS store_id',
            'stores.short_name AS store',
            DB::raw(sprintf($castQtyQuery, 'stock_levels.quantity', 'quantity')),
        )->crossJoin('stores')->leftJoin('stock_levels', function ($join) {
            $join->on('stock_levels.stockable_id', '=', 'item_variants.id')
                ->on('stock_levels.store_id', '=', 'stores.id')
                ->where('stock_levels.stockable_type', '=', 'item-variant');
        })
            ->when($store, function ($queryBuilder) use ($store) {
                $queryBuilder->where('stores.id', '=', $store);
            })->when($products, function ($queryBuilder) use ($products) {
                $queryBuilder->whereIn('item_variants.id', $products);
            });

        $query = $itemQuery->unionAll($itemVariantQuery);

        return $query->get();
    }
}

<?php

namespace App\Services;

use App\Models\StockLevel;
use Illuminate\Support\Facades\DB;

class StockLevelService
{
    public function get(
        ?string $query = null,
        ?int $limit = null,
        ?string $storeId = null,
        ?string $productId = null,
        ?array $with = ['store', 'stockable'],
        ?int $perPage = 24,
    ) {
        $stockQuery = StockLevel::query()
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
            ->when($productId, function ($queryBuilder, $productId) {
                $queryBuilder->where('stockable_id', $productId);
            })
            ->when($storeId, function ($queryBuilder, $storeId) {

                $queryBuilder->where('store_id', $storeId);
            })
            ->when($limit, function ($queryBuilder) use ($limit) {

                $queryBuilder->limit($limit);
            });

        return is_null($perPage)
            ? $stockQuery->get()
            : $stockQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data): StockLevel
    {
        $attributes = [
            'store_id' => data_get($data, 'store'),
            'stockable_type' => data_get($data, 'stockable_type'),
            'stockable_id' => data_get($data, 'stockable_id'),
            'quantity' => data_get($data, 'quantity'),
        ];

        return StockLevel::create($attributes);
    }

    public function update(StockLevel $stockLevel, array $data): bool
    {
        $attributes = [
            'store_id' => data_get($data, 'store'),
            'stockable_type' => data_get($data, 'stockable_type'),
            'stockable_id' => data_get($data, 'stockable_id'),
            'quantity' => data_get($data, 'quantity'),
        ];

        return $stockLevel->update($attributes);
    }

    public function delete(StockLevel $stockLevel, bool $forever = false): bool
    {
        if ($forever) {
            return $stockLevel->forceDelete();
        }

        return $stockLevel->delete();
    }

    public function importRow(array $data)
    {
        $attributes = [
            'store_id' => data_get($data, 'store_id'),
            'stockable_type' => data_get($data, 'stockable_type'),
            'stockable_id' => data_get($data, 'stockable_id'),
        ];

        $values = [
            'quantity' => data_get($data, 'quantity'),
        ];

        StockLevel::query()->updateOrCreate($attributes, $values);
    }

    public function updateQuantities(): void
    {
        DB::table('stock_movements')
            ->select('stockable_id', 'stockable_type', 'store_id', DB::raw('SUM(quantity) AS quantity_sum'))
            ->groupBy('store_id', 'stockable_id', 'stockable_type')
            ->orderBy('store_id', 'asc')
            ->orderBy('stockable_type', 'asc')
            ->orderBy('stockable_id', 'asc')
            ->whereNull('deleted_at')
            ->chunk(100, function ($chunk) {
                return $chunk->each(function ($item) {
                    $attributes = [
                        'stockable_id' => $item->stockable_id,
                        'stockable_type' => $item->stockable_type,
                        'store_id' => $item->store_id,
                    ];

                    $values = [
                        'quantity' => $item->quantity_sum,
                    ];

                    StockLevel::query()->updateOrCreate($attributes, $values);
                });
            });
    }
}

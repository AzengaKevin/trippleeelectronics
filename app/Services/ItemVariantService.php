<?php

namespace App\Services;

use App\Models\ItemVariant;
use Illuminate\Support\Facades\DB;

class ItemVariantService
{
    public function get(
        ?string $query = null,
        ?int $itemId = null,
        ?int $limit = null,
        ?array $with = ['item'],
        ?int $perPage = 24
    ) {
        $itemVariantQuery = ItemVariant::search($query, function ($defaultQuery) use ($with, $itemId, $limit) {
            $defaultQuery->when($with, function ($queryBuilder, $with) {
                $queryBuilder->with($with);
            });

            $defaultQuery->when($itemId, function ($queryBuilder, $itemId) {
                $queryBuilder->where('item_id', $itemId);
            });

            $defaultQuery->when($limit, function ($queryBuilder, $limit) {
                $queryBuilder->limit($limit);
            });
        });

        return is_null($perPage)
            ? $itemVariantQuery->get()
            : $itemVariantQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $attributes = [
                'author_user_id' => data_get($data, 'author_user_id'),
                'item_id' => data_get($data, 'item'),
                'attribute' => data_get($data, 'attribute'),
                'value' => data_get($data, 'value'),
                'name' => data_get($data, 'name'),
                'pos_name' => data_get($data, 'pos_name'),
                'cost' => data_get($data, 'cost'),
                'price' => data_get($data, 'price'),
                'selling_price' => data_get($data, 'selling_price'),
                'description' => data_get($data, 'description'),
                'pos_description' => data_get($data, 'pos_description'),
            ];

            $itemVariant = ItemVariant::query()->create($attributes);

            if ($image = data_get($data, 'image')) {

                $itemVariant->addMedia($image)->preservingOriginal()->toMediaCollection();
            }

            return $itemVariant->fresh();
        });
    }

    public function update(ItemVariant $itemVariant, array $data): bool
    {

        return DB::transaction(function () use ($itemVariant, $data) {

            if ($image = data_get($data, 'image')) {

                $itemVariant->clearMediaCollection();

                $itemVariant->addMedia($image)->preservingOriginal()->toMediaCollection();
            }

            $attributes = [
                'item_id' => data_get($data, 'item'),
                'attribute' => data_get($data, 'attribute'),
                'value' => data_get($data, 'value'),
                'name' => data_get($data, 'name'),
                'pos_name' => data_get($data, 'pos_name'),
                'cost' => data_get($data, 'cost'),
                'price' => data_get($data, 'price'),
                'selling_price' => data_get($data, 'selling_price'),
                'description' => data_get($data, 'description'),
                'pos_description' => data_get($data, 'pos_description'),
            ];

            return $itemVariant->update($attributes);
        });
    }

    public function delete(ItemVariant $itemVariant, bool $forever = false): bool
    {
        if ($forever) {

            $itemVariant->clearMediaCollection();

            return $itemVariant->forceDelete();
        }

        return $itemVariant->delete();
    }

    public function importRow(array $data)
    {
        $attributes = [
            'item_id' => data_get($data, 'item_id'),
            'name' => data_get($data, 'name'),
        ];

        $values = [
            'attribute' => data_get($data, 'attribute'),
            'value' => data_get($data, 'value'),
            'cost' => data_get($data, 'cost'),
            'price' => data_get($data, 'price'),
            'selling_price' => data_get($data, 'selling_price'),
            'description' => data_get($data, 'description'),
        ];

        ItemVariant::query()->updateOrCreate(
            $attributes,
            $values
        );
    }

    public function updateTaxRate(): void
    {
        ItemVariant::query()->withSum('taxes', 'rate')->chunk(100, function ($items) {

            $items->each(function ($item) {

                $item->update(['tax_rate' => $item->taxes_rate_sum]);
            });
        });
    }

    public function updateQuantities()
    {
        DB::table('stock_movements')
            ->select('stockable_id', DB::raw('SUM(quantity) as total_quantity'))
            ->where('stockable_type', 'item-variant')
            ->groupBy('stockable_id')
            ->orderBy('stockable_id', 'asc')
            ->whereNull('deleted_at') // Skip the deleted stock movements
            ->chunk(100, function ($chunk) {

                $chunk->each(function ($item) {
                    ItemVariant::query()
                        ->where('id', $item->stockable_id)
                        ->update(['quantity' => $item->total_quantity]);
                });
            });

        ItemVariant::query()->whereDoesntHave('stockMovements')->update(['quantity' => 0]);
    }

    public function updateCostByRelatedLatestPurchaseItem()
    {
        ItemVariant::query()
            ->select('item_variants.*')
            ->addSelect([
                'latest_purchase_item_cost' => function ($query) {
                    $query->select('cost')
                        ->from('purchase_items')
                        ->whereColumn('purchase_items.item_id', 'item_variants.id')
                        ->latest('created_at')
                        ->limit(1);
                },
            ])->whereHas('purchaseItems')
            ->chunk(100, function ($chunk) {

                $chunk->each(function ($item) {
                    if ($item->latest_purchase_item_cost != $item->cost) {
                        $item->update(['cost' => $item->latest_purchase_item_cost]);
                    }
                });
            });
    }

    public function updateSellingPriceFromPrice($factor)
    {
        $factor = floatval($factor);

        ItemVariant::query()
            ->chunk(100, function ($chunk) use ($factor) {

                $chunk->each(function ($itemVariant) use ($factor) {
                    $itemVariant->update([
                        'selling_price' => round($itemVariant->price * $factor, 2),
                    ]);
                });
            });
    }
}

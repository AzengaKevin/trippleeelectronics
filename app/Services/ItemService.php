<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Support\Facades\DB;

class ItemService
{
    public function get(
        ?string $query = null,
        ?int $limit = null,
        ?array $with = ['category', 'brand', 'media'],
        ?array $categoryIds = null,
        ?array $skipIds = null,
        ?string $brandId = null,
        ?int $perPage = 96,
        ?bool $withoutMedia = false,
        ?bool $outOfStock = false,
    ) {

        $itemQuery = Item::search($query, function ($defaultQuery) use ($limit, $with, $categoryIds, $skipIds, $brandId, $withoutMedia, $outOfStock) {

            $defaultQuery->when($limit, function ($query) use ($limit) {

                $query->limit($limit);
            })->when($with, function ($query) use ($with) {

                $query->with($with);
            })->when($categoryIds, function ($innerQuery, $categoryIds) {

                $innerQuery->whereIn('item_category_id', $categoryIds);
            })->when($skipIds, function ($innerQuery, $skipIds) {

                $innerQuery->whereNotIn('id', $skipIds);
            })->when($brandId, function ($innerQuery, $brandId) {

                $innerQuery->where('brand_id', $brandId);
            })->when($withoutMedia, function ($innerQuery) {

                $innerQuery->doesntHave('media');
            })->when($outOfStock, function ($innerQuery) {

                $innerQuery->where('quantity', '<=', 0);
            });
        });

        return is_null($perPage)
            ? $itemQuery->get()
            : $itemQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Item
    {
        return DB::transaction(function () use ($data) {

            $attributes = [
                'item_category_id' => data_get($data, 'category'),
                'brand_id' => data_get($data, 'brand'),
                'sku' => data_get($data, 'sku'),
                'name' => data_get($data, 'name'),
                'pos_name' => data_get($data, 'pos_name'),
                'slug' => data_get($data, 'slug', str()->slug(data_get($data, 'name'))),
                'cost' => data_get($data, 'cost'),
                'price' => data_get($data, 'price'),
                'selling_price' => data_get($data, 'selling_price'),
                'description' => data_get($data, 'description'),
                'pos_description' => data_get($data, 'pos_description'),
            ];

            $item = Item::query()->create($attributes);

            if ($image = data_get($data, 'image')) {

                $media = $item->addMedia($image)->preservingOriginal()->toMediaCollection();

                $item->update(['image_url' => $media->getFullUrl()]);
            }

            if ($images = data_get($data, 'images')) {

                collect($images)->each(function ($image) use ($item) {

                    $item->addMedia($image)->preservingOriginal()->toMediaCollection();
                });
            }

            return $item->fresh();
        });
    }

    public function update(Item $item, array $data): bool
    {
        return DB::transaction(function () use ($item, $data) {

            if ($image = data_get($data, 'image')) {

                $media = $item->addMedia($image)->preservingOriginal()->toMediaCollection();

                $data['image_url'] = $media->getFullUrl();
            }

            if ($images = data_get($data, 'images')) {

                collect($images)->each(function ($image) use ($item) {

                    $item->addMedia($image)->preservingOriginal()->toMediaCollection();
                });
            }

            $attributes = [
                'item_category_id' => data_get($data, 'category', $item->item_category_id),
                'brand_id' => data_get($data, 'brand', $item->brand_id),
                'sku' => data_get($data, 'sku', $item->sku),
                'name' => data_get($data, 'name', $item->name),
                'pos_name' => data_get($data, 'pos_name', $item->pos_name),
                'slug' => data_get($data, 'slug', $item->slug),
                'cost' => data_get($data, 'cost', $item->cost),
                'price' => data_get($data, 'price', $item->price),
                'selling_price' => data_get($data, 'selling_price', $item->selling_price),
                'image_url' => data_get($data, 'image_url', $item->image_url),
                'description' => data_get($data, 'description', $item->description),
                'pos_description' => data_get($data, 'pos_description', $item->pos_description),
            ];

            return $item->update($attributes);
        });
    }

    public function delete(Item $item, bool $forever = false): bool
    {
        if ($forever) {

            $item->clearMediaCollection();

            return $item->forceDelete();
        }

        return $item->delete();
    }

    public function importRow(array $data)
    {
        $attributes = [
            'name' => data_get($data, 'name'),
        ];

        $values = [
            'item_category_id' => data_get($data, 'item_category_id'),
            'brand_id' => data_get($data, 'brand_id'),
            'cost' => data_get($data, 'cost', 0),
            'price' => data_get($data, 'price', 0),
            'selling_price' => data_get($data, 'selling_price', 0),
            'description' => data_get($data, 'description'),
        ];

        Item::query()->updateOrCreate($attributes, $values);
    }

    public function updateTaxRate(): void
    {
        Item::query()->withSum('taxes', 'rate')->chunk(100, function ($items) {

            $items->each(function ($item) {

                $item->update(['tax_rate' => $item->taxes_sum_rate]);
            });
        });
    }

    public function updateQuantities()
    {
        DB::table('stock_movements')
            ->select('stockable_id', DB::raw('SUM(quantity) as total_quantity'))
            ->where('stockable_type', 'item')
            ->groupBy('stockable_id')
            ->orderBy('stockable_id', 'asc')
            ->whereNull('deleted_at') // Skip the deleted stock movements
            ->chunk(100, function ($chunk) {

                $chunk->each(function ($item) {

                    Item::query()
                        ->where('id', $item->stockable_id)
                        ->update(['quantity' => $item->total_quantity]);
                });
            });

        Item::query()->whereDoesntHave('stockMovements')->update(['quantity' => 0]);
    }

    public function updateCostByRelatedLatestPurchaseItem()
    {
        Item::query()
            ->select('items.*')
            ->addSelect([
                'latest_purchase_item_cost' => function ($query) {
                    $query->select('cost')
                        ->from('purchase_items')
                        ->whereColumn('purchase_items.item_id', 'items.id')
                        ->latest('created_at')
                        ->limit(1);
                },
            ])
            ->whereHas('purchaseItems')
            ->chunk(100, function ($items) {

                $items->each(function ($item) {

                    if ($item->latest_purchase_item_cost != $item->cost) {

                        $item->update(['cost' => $item->latest_purchase_item_cost]);
                    }
                });
            });
    }

    public function updateSellingPriceFromPrice($factor)
    {
        $factor = floatval($factor);

        Item::query()->chunk(100, function ($items) use ($factor) {

            $items->each(function ($item) use ($factor) {

                $item->update(['selling_price' => round($item->price * $factor, 2)]);
            });
        });
    }

    public function getInventoryValue(): float
    {
        return Item::query()->sum(DB::raw('price * quantity'));
    }
}

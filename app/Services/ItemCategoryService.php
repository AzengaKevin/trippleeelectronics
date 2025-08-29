<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemCategory;
use Illuminate\Support\Facades\DB;

class ItemCategoryService
{
    public function get(
        ?string $query = null,
        ?int $limit = null,
        ?array $with = null,
        ?bool $featured = false,
        ?string $orderBy = 'items_count_manual',
        ?string $orderDirection = 'desc',
        ?int $perPage = 24,
    ) {

        $itemCategoryQuery = ItemCategory::query()->when($query, function ($innerQuery, $query) {
            $innerQuery->where('name', 'like', "%{$query}%")
                ->orWhere('description', 'like', "{$query}");
        })->when($limit, function ($innerQuery, $limit) {

            $innerQuery->limit($limit);

        })->when($with, function ($innerQuery, $with) {

            $innerQuery->with($with);

        })->when($featured, function ($innerQuery) {

            $innerQuery->featured();

        })->orderBy($orderBy, $orderDirection);

        return is_null($perPage)
            ? $itemCategoryQuery->get()
            : $itemCategoryQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data)
    {

        return DB::transaction(function () use ($data) {

            $parent = null;

            if ($parentId = data_get($data, 'category')) {

                $parent = ItemCategory::query()->find($parentId);

            }

            $attributes = [
                'name' => data_get($data, 'name'),
                'description' => data_get($data, 'description'),
                'featured' => boolval(data_get($data, 'featured')),
                'author_user_id' => data_get($data, 'author_user_id'),
            ];

            $category = is_null($parent)
                ? ItemCategory::query()->create($attributes)
                : $parent->children()->create($attributes);

            if ($image = data_get($data, 'image')) {

                $category->addMedia($image)->preservingOriginal()->toMediaCollection();
            }

            return $category->fresh();

        });
    }

    public function update(ItemCategory $itemCategory, array $data): bool
    {
        return DB::transaction(function () use ($itemCategory, $data) {

            $attributes = [
                'name' => data_get($data, 'name'),
                'description' => data_get($data, 'description'),
                'featured' => boolval(data_get($data, 'featured')),
            ];

            $itemCategory->update($attributes);

            if ($image = data_get($data, 'image')) {

                $itemCategory->clearMediaCollection();

                $itemCategory->addMedia($image)->preservingOriginal()->toMediaCollection();
            }

            return true;
        });
    }

    public function delete(ItemCategory $itemCategory, bool $destroy = false)
    {
        if ($destroy) {

            $itemCategory->clearMediaCollection();

            $itemCategory->forceDelete();

        } else {

            $itemCategory->delete();
        }
    }

    public function importRow(array $data)
    {
        $attributes = [
            'name' => data_get($data, 'name'),
        ];

        $values = [
            'description' => data_get($data, 'description'),
            'parent_id' => data_get($data, 'parent_id'),
        ];

        ItemCategory::updateOrCreate($attributes, $values);
    }

    public function updateItemsCountManual(ItemCategory $itemCategory)
    {
        $categoryIds = ItemCategory::descendantsAndSelf($itemCategory->id)->pluck('id')->all();

        $itemsCount = Item::query()->whereIn('item_category_id', $categoryIds)->count();

        $itemCategory->update(['items_count_manual' => $itemsCount]);
    }
}

<?php

namespace App\Console\Commands\ItemCategories;

use App\Models\ItemCategory;
use App\Services\ItemCategoryService;
use Illuminate\Console\Command;

class UpdateItemsCountCommand extends Command
{
    protected $signature = 'app:item-categories:update-items-count';

    protected $description = 'Update Item Categories Manual Items Count';

    public function handle()
    {
        $itemCategoryService = app(ItemCategoryService::class);

        ItemCategory::query()->each(function (ItemCategory $category) use ($itemCategoryService) {

            $itemCategoryService->updateItemsCountManual($category);
        });

        $this->info('Item Categories Manual Items Count Updated Successfully');

    }
}

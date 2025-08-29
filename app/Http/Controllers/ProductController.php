<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Services\ItemCategoryService;
use App\Services\ItemService;
use App\Services\ServiceService;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly ItemService $itemService,
        private readonly SettingsService $settingsService,
        private readonly ItemCategoryService $itemCategoryService,
        private readonly ServiceService $serviceService,
    ) {}

    public function index(Request $request)
    {
        $params = $request->only('query');

        $items = $this->itemService->get(...$params, with: ['category', 'brand', 'media'], perPage: 48);

        $categories = $this->itemCategoryService->get(perPage: null, limit: 20)->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'image_url' => $category->getFirstMediaUrl(),
            ];
        });

        $settings = $this->settingsService->get();

        $treeCategories = ItemCategory::get()->toTree();

        $services = $this->serviceService->get(perPage: null, limit: 10)->map(function ($service) {
            return [
                'id' => $service->id,
                'title' => $service->title,
                'description' => $service->description,
                'image_url' => $service->getFirstMediaUrl(),
            ];
        });

        return inertia('products/IndexPage', [
            'products' => $items,
            'categories' => $categories,
            'settings' => $settings,
            'treeCategories' => $treeCategories,
            'services' => $services,
        ]);
    }

    public function show(Item $product)
    {
        $product->load(['category', 'brand', 'media']);

        $categories = $this->itemCategoryService->get(perPage: null, limit: 20)->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'image_url' => $category->getFirstMediaUrl(),
            ];
        });

        $settings = $this->settingsService->get();

        $treeCategories = ItemCategory::get()->toTree();

        $services = $this->serviceService->get(perPage: null, limit: 10)->map(function ($service) {
            return [
                'id' => $service->id,
                'title' => $service->title,
                'description' => $service->description,
                'image_url' => $service->getFirstMediaUrl(),
            ];
        });

        $recommendedProducts = $this->itemService->get(
            categoryIds: ItemCategory::descendantsAndSelf($product->item_category_id)->pluck('id')->toArray(),
            skipIds: [$product->id],
            perPage: null,
            limit: 16
        );

        return inertia('products/ShowPage', [
            'product' => $product,
            'settings' => $settings,
            'treeCategories' => $treeCategories,
            'services' => $services,
            'categories' => $categories,
            'recommendedProducts' => $recommendedProducts,
        ]);
    }
}

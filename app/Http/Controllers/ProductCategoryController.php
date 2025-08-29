<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\ItemCategory;
use App\Services\ItemCategoryService;
use App\Services\ItemService;
use App\Services\ServiceService;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductCategoryController extends Controller
{
    public function __construct(
        private readonly SettingsService $settingsService,
        private readonly ItemCategoryService $itemCategoryService,
        private readonly ServiceService $serviceService,
        private readonly ItemService $itemService,
    ) {}

    public function index(Request $request, string $slug)
    {

        $params = $request->only('query');

        $category = ItemCategory::where('slug', $slug)->first();

        if ($category) {

            return $this->getCategoryResponse($category, $params);
        }

        $brand = Brand::where('slug', $slug)->first();

        if ($brand) {

            return $this->getBrandResponse($brand, $params);
        }

        abort(404, 'Category page not found');
    }

    private function getCategoryResponse(ItemCategory $category, array $params)
    {
        $items = $this->itemService->get(...$params, categoryIds: ItemCategory::descendantsAndSelf($category->id)->pluck('id')->toArray(), with: ['category', 'brand', 'media']);

        return Inertia::render('ItemCategoryCategoryPage', [
            ...$this->getStandardData(),
            'category' => $category,
            'products' => $items,
            'params' => $params,
        ]);

    }

    private function getBrandResponse(Brand $brand, array $params)
    {
        $items = $this->itemService->get(...$params, brandId: $brand->id, with: ['category', 'brand', 'media']);

        return Inertia::render('BrandCategoryPage', [
            ...$this->getStandardData(),
            'brand' => $brand,
            'products' => $items,
            'params' => $params,
        ]);

    }

    private function getStandardData()
    {

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

        return [
            'categories' => $categories,
            'settings' => $settings,
            'treeCategories' => $treeCategories,
            'services' => $services,
        ];
    }
}

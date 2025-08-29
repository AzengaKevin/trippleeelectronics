<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use App\Services\BrandService;
use App\Services\CarouselService;
use App\Services\ItemCategoryService;
use App\Services\ItemService;
use App\Services\ServiceService;
use App\Services\SettingsService;
use Inertia\Inertia;

class WelcomeController extends Controller
{
    public function __construct(
        private readonly BrandService $brandService,
        private readonly CarouselService $carouselService,
        private readonly SettingsService $settingsService,
        private readonly ItemCategoryService $itemCategoryService,
        private readonly ServiceService $serviceService,
        private readonly ItemService $itemService,
    ) {}

    public function __invoke()
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

        $tree_categories = ItemCategory::get()->toTree();

        $services = $this->serviceService->get(perPage: null, limit: 10)->map(function ($service) {
            return [
                'id' => $service->id,
                'title' => $service->title,
                'description' => $service->description,
                'image_url' => $service->getFirstMediaUrl(),
            ];
        });

        $brands = $this->brandService->get(perPage: null)
            ->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'image_url' => $brand->getFirstMediaUrl(),
                ];
            });

        $carousels = $this->carouselService->get(perPage: null)->map(function ($carousel) {
            return [
                'id' => $carousel->id,
                'title' => $carousel->title,
                'slug' => $carousel->slug,
                'description' => $carousel->description,
                'orientation' => $carousel->orientation,
                'position' => $carousel->position,
                'link' => $carousel->link,
                'image_url' => $carousel->getFirstMediaUrl(),
            ];
        });

        $categories_with_products = $this->itemCategoryService->get(perPage: null, limit: 10)->map(function (ItemCategory $category) {

            $categoryIds = ItemCategory::descendantsAndSelf($category->id)->pluck('id')->all();

            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'image_url' => $category->getFirstMediaUrl(),
                'products' => $this->itemService->get(categoryIds: $categoryIds, with: ['category', 'brand', 'media'], limit: 8, perPage: null),
            ];
        });

        return Inertia::render('WelcomePage', [
            'brands' => $brands,
            'settings' => $settings,
            'categories' => $categories,
            'carousels' => $carousels,
            'tree_categories' => $tree_categories,
            'categories_with_products' => $categories_with_products,
            'services' => $services,
        ]);
    }
}

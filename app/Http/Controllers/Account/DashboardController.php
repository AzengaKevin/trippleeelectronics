<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use App\Services\ItemCategoryService;
use App\Services\ItemService;
use App\Services\ServiceService;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly SettingsService $settingsService,
        private readonly ItemCategoryService $itemCategoryService,
        private readonly ServiceService $serviceService,
        private readonly ItemService $itemService,
    ) {}

    public function __invoke(Request $request)
    {
        $user = $request->user();

        return inertia('account/DashboardPage', [
            'user' => $user,
            ...$this->getStandardData(),
        ]);
    }

    private function getStandardData(): array
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

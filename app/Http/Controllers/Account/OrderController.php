<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use App\Models\Order;
use App\Services\ItemCategoryService;
use App\Services\OrderService;
use App\Services\ServiceService;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly ItemCategoryService $itemCategoryService,
        private readonly SettingsService $settingsService,
        private readonly ServiceService $serviceService,
    ) {}

    public function index(Request $request): Response
    {
        $params = $request->only('query');

        $orders = $this->orderService->get(
            ...$params,
            user: $request->user(),
        );

        return Inertia::render('account/orders/IndexPage', [
            ...$this->getStandardData(),
            'orders' => $orders,
            'params' => $params,
        ]);
    }

    public function receipt(Order $order)
    {
        $pdfContent = $this->orderService->generateReceipt($order);

        $filename = str($order->reference)
            ->append('-')
            ->append('receipt')
            ->slug()
            ->append('.pdf')
            ->value();

        $contentDisposition = 'inline; filename="'.$filename.'"';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', $contentDisposition);
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

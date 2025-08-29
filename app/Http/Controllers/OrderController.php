<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\ItemCategoryService;
use App\Services\OrderService;
use App\Services\ServiceService;
use App\Services\SettingsService;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly ItemCategoryService $itemCategoryService,
        private readonly SettingsService $settingsService,
        private readonly ServiceService $serviceService,
    ) {}

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
}

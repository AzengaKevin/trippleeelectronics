<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Store;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request)
    {

        $params = $request->only('query', 'store', 'status', 'withOutstandingAmount', 'from', 'to');

        $filters = [...$params];

        if ($storeId = data_get($filters, 'store')) {

            $filters['store'] = Store::query()->find($storeId);
        }

        $filters['withOutstandingAmount'] = $request->boolean('withOutstandingAmount', false);

        if ($from = data_get($params, 'from')) {

            $filters['from'] = $from;
        }

        if ($to = data_get($params, 'to')) {

            $filters['to'] = $to;
        }

        $orders = $this->orderService->get(...$filters);

        return new OrderResource($orders);
    }
}

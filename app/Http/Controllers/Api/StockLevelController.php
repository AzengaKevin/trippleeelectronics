<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductStockLevelService;
use Illuminate\Http\Request;

class StockLevelController extends Controller
{
    public function __construct(private readonly ProductStockLevelService $productStockLevelService) {}

    public function index(Request $request)
    {
        $params = $request->only('products', 'store');

        $data = $this->productStockLevelService->getMany(...$params);

        return response()->json(compact('data'), 200);
    }
}

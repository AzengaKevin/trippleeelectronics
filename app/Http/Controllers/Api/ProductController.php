<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Services\ProductService;
use App\Services\ProductStockLevelService;
use App\Services\StoreService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private StoreService $storeService,
        private ProductStockLevelService $productStockLevelService
    ) {}

    public function index(Request $request)
    {
        $params = $request->only('query', 'perPage', 'limit', 'includeVariants', 'includeCustomItems');

        $products = $this->productService->get(...$params);

        return new ProductResource($products);
    }

    public function stockLevels($product)
    {
        $productModel = Item::find($product) ?? ItemVariant::find($product);

        if (! $productModel) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $stockLevels = $this->productStockLevelService->get($productModel);

        $stores = $this->storeService->get(perPage: null);

        $data = $stores->map(function ($store) use ($stockLevels) {
            $item = $stockLevels->first(fn ($stockLevel) => $stockLevel->store_id === $store->id);

            return [
                'store' => $store->short_name ?? $store->name,
                'quantity' => $item->quantity ?? 0,
            ];
        });

        return response()->json(compact('data'), 200);
    }
}

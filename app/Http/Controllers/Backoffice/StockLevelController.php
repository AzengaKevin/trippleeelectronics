<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\StockLevelExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StoreStockLevelRequest;
use App\Http\Requests\UpdateStockLevelRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\StockLevelImport;
use App\Models\Enums\StockableType;
use App\Models\StockLevel;
use App\Models\Store;
use App\Models\User;
use App\Services\ExcelService;
use App\Services\ItemService;
use App\Services\StockLevelService;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockLevelController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    private ?User $currentUser = null;

    private ?Store $currentStore = null;

    public function __construct(
        private StockLevelService $stockLevelService,
        private StoreService $storeService,
        private ItemService $itemService,
    ) {

        $this->currentUser = request()->user();

        $this->currentStore = $this->storeService->getUserStore($this->currentUser);
    }

    public function index(Request $request)
    {
        $params = $request->only('query', 'store');

        $filters = [
            'query' => data_get($params, 'query'),
            'storeId' => data_get($params, 'store'),
        ];

        $stockLevels = $this->stockLevelService->get(...$filters);

        $stores = $this->storeService->get(perPage: null)->map(fn ($store) => [
            'id' => $store->id,
            'name' => $store->name,
        ]);

        return Inertia::render('backoffice/stock-levels/IndexPage', compact('stockLevels', 'params', 'stores'));
    }

    public function create(Request $request)
    {
        $store = $request->query('store');

        if ($store) {
            $store = Store::query()->find($store);
        }

        $stores = $this->storeService->get(perPage: null)->map(fn ($store) => ['id' => $store->id, 'name' => $store->name]);

        $items = $this->itemService->get(perPage: null, with: ['variants'])->map(fn ($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'variants' => $item->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'name' => $variant->name,
            ]),
        ]);

        $stockableTypes = StockableType::labelledOptions();

        return Inertia::render('backoffice/stock-levels/CreatePage', compact('store', 'stores', 'items', 'stockableTypes'));
    }

    public function store(StoreStockLevelRequest $storeStockLevelRequest): RedirectResponse
    {
        $data = $storeStockLevelRequest->validated();

        try {
            $this->stockLevelService->create($data);

            return $this->sendSuccessRedirect('Stock Level created successfully.', route('backoffice.stock-levels.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Stock Level creation failed.', $throwable);
        }
    }

    public function show(StockLevel $stockLevel)
    {
        $stockLevel->load(['store', 'stockable']);

        return Inertia::render('backoffice/stock-levels/ShowPage', compact('stockLevel'));
    }

    public function edit(StockLevel $stockLevel)
    {
        $stockLevel->load(['store', 'stockable']);

        $stores = $this->storeService->get(perPage: null)->map(fn ($store) => ['id' => $store->id, 'name' => $store->name]);

        $items = $this->itemService->get(perPage: null, with: ['variants'])->map(fn ($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'variants' => $item->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'name' => $variant->name,
            ]),
        ]);

        $stockableTypes = StockableType::labelledOptions();

        return Inertia::render('backoffice/stock-levels/EditPage', compact('stockLevel', 'stores', 'items', 'stockableTypes'));
    }

    public function update(UpdateStockLevelRequest $updateStockLevelRequest, StockLevel $stockLevel): RedirectResponse
    {
        $data = $updateStockLevelRequest->validated();

        try {

            $this->stockLevelService->update($stockLevel, $data);

            return $this->sendSuccessRedirect('Stock Level updated successfully.', route('backoffice.stock-levels.show', $stockLevel));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Stock Level update failed.', $throwable);
        }
    }

    public function destroy(Request $request, string $stockLevel): RedirectResponse
    {
        try {
            $stockLevel = StockLevel::findOrFail($stockLevel);

            $this->stockLevelService->delete($stockLevel, $request->boolean('forever'));

            return $this->sendSuccessRedirect('Stock Level deleted successfully.', route('backoffice.stock-levels.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Stock Level deletion failed.', $throwable);
        }
    }

    public function export(Request $request)
    {
        $data = $request->only('query', 'limit');

        $export = new StockLevelExport($data);

        return $export->download(StockLevel::getExportFilename());
    }

    public function import()
    {
        return Inertia::render('backoffice/stock-levels/ImportPage');
    }

    public function processImport(ImportRequest $importRequest): RedirectResponse
    {
        $data = $importRequest->validated();

        try {
            $this->robustImport(new StockLevelImport, $data['file'], 'stockLevels', 'stockLevels');

            return $this->sendSuccessRedirect('Imported stock levels successfully.', route('backoffice.stock-levels.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Failed to import stock levels.', $throwable);
        }
    }
}

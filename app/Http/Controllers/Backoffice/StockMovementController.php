<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\InitialStockMovementExport;
use App\Exports\StockMovementExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StoreStockMovementRequest;
use App\Http\Requests\TransferStockRequest;
use App\Http\Requests\UpdateStockMovementRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\InitialStockMovementImport;
use App\Imports\StockMovementImport;
use App\Models\Brand;
use App\Models\Enums\StockMovementType;
use App\Models\ItemCategory;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;
use App\Services\BrandService;
use App\Services\ExcelService;
use App\Services\ItemCategoryService;
use App\Services\ItemService;
use App\Services\StockMovementService;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockMovementController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    private ?User $currentUser = null;

    private ?Store $currentStore = null;

    public function __construct(
        private StockMovementService $stockMovementService,
        private StoreService $storeService,
        private ItemService $itemService,
        private ItemCategoryService $itemCategoryService,
        private BrandService $brandService,
    ) {
        $this->currentUser = request()->user();

        $this->currentStore = $this->storeService->getUserStore($this->currentUser);
    }

    public function index(Request $request)
    {
        $params = $request->only('query', 'store', 'withoutStore', 'type', 'withoutStockable');

        $filters = [
            'query' => data_get($params, 'query'),
            'storeId' => data_get($params, 'store'),
        ];

        $filters['withoutStore'] = $request->boolean('withoutStore', false);

        $filters['withoutStockable'] = $request->boolean('withoutStockable', false);

        if ($type = data_get($params, 'type')) {

            $filters['type'] = $type;
        }

        $stockMovements = $this->stockMovementService->get(...$filters);

        $stores = $this->storeService->get(perPage: null)->map(fn ($store) => [
            'id' => $store->id,
            'name' => $store->name,
        ]);

        $types = StockMovementType::labelledOptions();

        activity()->log('Viewed stock movements');

        return Inertia::render('backoffice/stock-movements/IndexPage', compact('stockMovements', 'params', 'stores', 'types'));
    }

    public function create(Request $request)
    {
        $store = $request->query('store');

        if ($store) {
            $store = Store::query()->find($store);
        }

        $stores = $this->storeService->get(perPage: null)->map(fn ($store) => ['id' => $store->id, 'name' => $store->name]);

        $stockMovementTypes = StockMovementType::manualLabelledOptions();

        activity()->log('Viewed create stock movement page');

        return Inertia::render('backoffice/stock-movements/CreatePage', compact('store', 'stores', 'stockMovementTypes'));
    }

    public function store(StoreStockMovementRequest $storeStockMovementRequest): RedirectResponse
    {
        $data = $storeStockMovementRequest->validated();

        try {
            $this->stockMovementService->create([
                ...$data,
                'author_user_id' => $this->currentUser?->id,
            ]);

            activity()->log('Created stock movement');

            return $this->sendSuccessRedirect('Stock Movement created successfully.', route('backoffice.stock-movements.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Stock Movement creation failed.', $throwable);
        }
    }

    public function show(StockMovement $stockMovement)
    {
        $stockMovement->load(['store:id,name', 'stockable:id,name', 'robustStockable:id,name', 'action:id,reference', 'author:id,name']);

        activity()
            ->performedOn($stockMovement)
            ->log('Viewed stock movement details');

        return Inertia::render('backoffice/stock-movements/ShowPage', compact('stockMovement'));
    }

    public function edit(StockMovement $stockMovement)
    {
        $stockMovement->load(['store', 'stockable']);

        $stores = $this->storeService->get(perPage: null)->map(fn ($store) => ['id' => $store->id, 'name' => $store->name]);

        $stockMovementTypes = StockMovementType::manualLabelledOptions();

        return Inertia::render('backoffice/stock-movements/EditPage', compact('stockMovement', 'stores', 'stockMovementTypes'));
    }

    public function update(UpdateStockMovementRequest $updateStockMovementRequest, StockMovement $stockMovement): RedirectResponse
    {
        $data = $updateStockMovementRequest->validated();

        try {
            $this->stockMovementService->update($stockMovement, $data);

            activity()
                ->performedOn($stockMovement)
                ->log('Updated stock movement');

            return $this->sendSuccessRedirect('Stock Movement updated successfully.', route('backoffice.stock-movements.show', $stockMovement));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Stock Movement update failed.', $throwable);
        }
    }

    public function destroy(Request $request, string $stockMovement): RedirectResponse
    {
        try {
            $stockMovement = StockMovement::findOrFail($stockMovement);

            $this->stockMovementService->delete($stockMovement, $request->boolean('forever'));

            activity()
                ->performedOn($stockMovement)
                ->log('Deleted stock movement');

            return $this->sendSuccessRedirect('Stock Movement deleted successfully.', route('backoffice.stock-movements.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Stock Movement deletion failed.', $throwable);
        }
    }

    public function export(Request $request)
    {
        $params = $request->only('query', 'store', 'withoutStore', 'type');

        $filters = [
            'query' => data_get($params, 'query'),
            'storeId' => data_get($params, 'store'),
        ];

        $filters['withoutStore'] = $request->boolean('withoutStore', false);

        if ($type = data_get($params, 'type')) {

            $filters['type'] = $type;
        }

        $export = new StockMovementExport($params);

        activity()->log('Exported stock movements');

        return $export->download(StockMovement::getExportFilename());
    }

    public function import()
    {
        return Inertia::render('backoffice/stock-movements/ImportPage');
    }

    public function processImport(ImportRequest $importRequest): RedirectResponse
    {
        $data = $importRequest->validated();

        try {
            $this->robustImport(new StockMovementImport, $data['file'], 'stockMovements', 'stockMovements');

            activity()->log('Imported stock movements');

            return $this->sendSuccessRedirect('Imported stock movements successfully.', route('backoffice.stock-movements.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Failed to import stock movements.', $throwable);
        }
    }

    public function initialItemsStock(Request $request)
    {
        $params = $request->only('store', 'category', 'brand');

        $filters = [];

        if ($category = data_get($params, 'category')) {

            $filters['categoryIds'] = ItemCategory::descendantsAndSelf($category)->pluck('id')->toArray();
        }

        if ($store = data_get($params, 'store')) {

            $store = Store::query()->find($store);

            $filters['store'] = $store;
        }

        if ($perPage = data_get($params, 'perPage')) {

            $filters['perPage'] = $perPage;
        }

        if ($brand = data_get($params, 'brand')) {

            $filters['brandId'] = $brand;
        }

        $items = $this->stockMovementService->getInitialItemsStock(...$filters);

        $categories = $this->itemCategoryService->get(perPage: null)
            ->map(fn (ItemCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
            ]);

        $brands = $this->brandService->get(perPage: null)
            ->map(fn (Brand $brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
            ]);

        $stores = $this->storeService->get(perPage: null)->map(fn ($store) => [
            'id' => $store->id,
            'name' => $store->name,
        ]);

        activity()->log('Viewed initial items stock page');

        return Inertia::render('backoffice/stock-movements/InitialItemsStockPage', compact('items', 'params', 'categories', 'brands', 'stores'));
    }

    public function exportInitialItemsStock(Request $request)
    {
        $params = $request->only('store', 'category', 'brand', 'limit', 'perPage');

        $filters = [];

        if ($limit = data_get($params, 'limit')) {

            $filters['limit'] = $limit;
        }

        if ($category = data_get($params, 'category')) {

            $filters['categoryIds'] = ItemCategory::descendantsAndSelf($category)->pluck('id')->toArray();
        }

        if ($store = data_get($params, 'store')) {

            $store = Store::query()->find($store);

            $filters['store'] = $store;
        }

        if ($perPage = data_get($params, 'perPage')) {

            $filters['perPage'] = $perPage;
        }

        if ($brand = data_get($params, 'brand')) {

            $filters['brandId'] = $brand;
        }

        $export = new InitialStockMovementExport($filters);

        $filename = str('initial-items-stock')
            ->when(data_get($filters, 'store'), fn ($str) => $str->append('-', data_get($filters, 'store')->name))
            ->when(data_get($params, 'category'), fn ($str) => $str->append('-', ItemCategory::find(data_get($params, 'category'))->slug))
            ->when(data_get($params, 'brand'), fn ($str) => $str->append('-', Brand::find(data_get($params, 'brand'))->name))
            ->slug()
            ->append('.xlsx')
            ->toString();

        activity()->log('Exported initial items stock');

        return $export->download($filename);
    }

    public function importInitialItemsStock(ImportRequest $importRequest): RedirectResponse
    {
        $data = $importRequest->validated();

        try {
            $this->robustImport(new InitialStockMovementImport, $data['file'], 'initialItemsStock', 'initialItemsStock');

            activity()
                ->withProperties(['imported' => true])
                ->log('Imported initial items stock');

            return $this->sendSuccessRedirect('Imported initial items stock successfully.', route('backoffice.stock-movements.initial-items-stock'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to import initial items stock.', $throwable);
        }
    }

    public function transfer(TransferStockRequest $transferStockRequest): RedirectResponse
    {
        $data = $transferStockRequest->validated();

        try {

            $this->stockMovementService->transferStock([
                ...$data,
                'author_user_id' => $this->currentUser?->id,
            ]);

            activity()->withProperties($data)->log('Transferred stock from store to another');

            return $this->sendSuccessRedirect('Stock transferred successfully.', route('backoffice.stock-movements.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to transfer stock.', $throwable);
        }
    }
}

<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\ItemExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\ItemImport;
use App\Models\Brand;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Services\BrandService;
use App\Services\ExcelService;
use App\Services\ItemCategoryService;
use App\Services\ItemService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ItemController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    public function __construct(
        private ItemService $itemService,
        private ItemCategoryService $itemCategoryService,
        private BrandService $brandService,
    ) {}

    public function index(Request $request)
    {
        $params = $request->only('query', 'category', 'brand', 'perPage', 'withoutMedia', 'outOfStock');

        $filters = [];

        if ($query = data_get($params, 'query')) {

            $filters['query'] = $params['query'];
        }

        if ($category = data_get($params, 'category')) {

            $filters['categoryIds'] = ItemCategory::descendantsAndSelf($category)->pluck('id')->toArray();
        }

        if ($perPage = data_get($params, 'perPage')) {

            $filters['perPage'] = $perPage;
        }

        if ($brand = data_get($params, 'brand')) {

            $filters['brandId'] = $brand;
        }

        if ($withoutMedia = data_get($params, 'withoutMedia')) {

            $filters['withoutMedia'] = $request->boolean('withoutMedia');
        }

        if ($outOfStock = data_get($params, 'outOfStock')) {

            $filters['outOfStock'] = $request->boolean('outOfStock');
        }

        $items = $this->itemService->get(...$filters);

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

        return Inertia::render('backoffice/items/IndexPage', compact('items', 'params', 'categories', 'brands'));
    }

    public function create()
    {
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

        return Inertia::render('backoffice/items/CreatePage', compact('categories', 'brands'));
    }

    public function store(StoreItemRequest $storeItemRequest): RedirectResponse
    {
        $data = $storeItemRequest->validated();

        try {

            $this->itemService->create($data);

            return $this->sendSuccessRedirect('Item created successfully.', route('backoffice.items.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect(
                'Item creation failed.',
                $throwable
            );
        }
    }

    public function show(Item $item)
    {

        $item->load(['category', 'brand']);

        return Inertia::render('backoffice/items/ShowPage', compact('item'));
    }

    public function edit(Item $item)
    {
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

        return Inertia::render('backoffice/items/EditPage', compact('item', 'categories', 'brands'));
    }

    public function update(UpdateItemRequest $updateItemRequest, Item $item): RedirectResponse
    {
        $data = $updateItemRequest->validated();

        try {

            $this->itemService->update($item, $data);

            return $this->sendSuccessRedirect('Item updated successfully.', route('backoffice.items.show', $item));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect(
                'Item update failed.',
                $throwable
            );
        }
    }

    public function destroy(Request $request, string $item): RedirectResponse
    {
        try {

            $item = Item::findOrFail($item);

            $this->itemService->delete($item, $request->boolean('forever'));

            return $this->sendSuccessRedirect('Item deleted successfully.', route('backoffice.items.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect(
                'Item deletion failed.',
                $throwable
            );
        }
    }

    public function export(Request $request)
    {
        $data = $request->only('query', 'limit', 'category', 'brand');

        $filters = [];

        if (data_get($data, 'query')) {

            $filters['query'] = $data['query'];
        }

        if ($category = data_get($data, 'category')) {

            $filters['categoryIds'] = ItemCategory::descendantsAndSelf($category)->pluck('id')->toArray();
        }

        if ($perPage = data_get($data, 'perPage')) {

            $filters['perPage'] = $perPage;
        }

        if ($brand = data_get($data, 'brand')) {

            $filters['brandId'] = $brand;
        }

        if ($limit = data_get($data, 'limit')) {

            $filters['limit'] = $limit;
        }

        $itemExport = new ItemExport($filters);

        return $itemExport->download(Item::getExportFilename());
    }

    public function import()
    {
        return Inertia::render('backoffice/items/ImportPage');
    }

    public function processImport(ImportRequest $importRequest): RedirectResponse
    {
        $data = $importRequest->validated();

        try {

            $this->robustImport(new ItemImport, $data['file'], 'items', 'items');

            return $this->sendSuccessRedirect('Imported items successfully.', route('backoffice.items.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to import items.', $throwable);
        }
    }
}

<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\ItemVariantExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StoreItemVariantRequest;
use App\Http\Requests\UpdateItemVariantRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\ItemVariantImport;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Services\ExcelService;
use App\Services\ItemService;
use App\Services\ItemVariantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ItemVariantController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    public function __construct(
        private ItemVariantService $itemVariantService,
        private ItemService $itemService,
    ) {}

    public function index(Request $request)
    {
        $params = $request->only('query', 'perPage');

        $itemVariants = $this->itemVariantService->get(...$params);

        return Inertia::render('backoffice/item-variants/IndexPage', compact('itemVariants', 'params'));
    }

    public function create(Request $request)
    {
        $item = $request->query('item');

        if ($item) {

            $item = Item::query()->find($item);
        }

        $items = $this->itemService->get(perPage: null)->map(fn ($item) => ['id' => $item->id, 'name' => $item->name]);

        return Inertia::render('backoffice/item-variants/CreatePage', compact('item', 'items'));
    }

    public function store(StoreItemVariantRequest $storeItemVariantRequest): RedirectResponse
    {
        $data = $storeItemVariantRequest->validated();

        try {

            $data['author_user_id'] = request()->user()->id;

            $this->itemVariantService->create($data);

            return $this->sendSuccessRedirect('Item Variant created successfully.', route('backoffice.item-variants.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Item Variant creation failed.', $throwable);
        }
    }

    public function show(ItemVariant $itemVariant)
    {
        $itemVariant->load(['item', 'author']);

        $itemVariant->image_url = $itemVariant->getFirstMediaUrl();

        return Inertia::render('backoffice/item-variants/ShowPage', compact('itemVariant'));
    }

    public function edit(ItemVariant $itemVariant)
    {
        $itemVariant->load(['item']);

        $items = $this->itemService->get(perPage: null)->map(fn ($item) => ['id' => $item->id, 'name' => $item->name]);

        return Inertia::render('backoffice/item-variants/EditPage', compact('itemVariant', 'items'));
    }

    public function update(UpdateItemVariantRequest $updateItemVariantRequest, ItemVariant $itemVariant): RedirectResponse
    {
        $data = $updateItemVariantRequest->validated();

        try {
            $this->itemVariantService->update($itemVariant, $data);

            return $this->sendSuccessRedirect('Item Variant updated successfully.', route('backoffice.item-variants.show', $itemVariant));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Item Variant update failed.', $throwable);
        }
    }

    public function destroy(Request $request, string $itemVariant): RedirectResponse
    {
        try {
            $itemVariant = ItemVariant::findOrFail($itemVariant);

            $this->itemVariantService->delete($itemVariant, $request->boolean('forever'));

            return $this->sendSuccessRedirect('Item Variant deleted successfully.', route('backoffice.item-variants.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Item Variant deletion failed.', $throwable);
        }
    }

    public function export(Request $request)
    {
        $data = $request->only('query', 'limit');

        $itemVariantExport = new ItemVariantExport($data);

        return $itemVariantExport->download(ItemVariant::getExportFilename());
    }

    public function import()
    {
        return Inertia::render('backoffice/item-variants/ImportPage');
    }

    public function processImport(ImportRequest $importRequest): RedirectResponse
    {
        $data = $importRequest->validated();

        try {
            $this->robustImport(new ItemVariantImport, $data['file'], 'item-variants', 'item-variants');

            return $this->sendSuccessRedirect('Imported item variants successfully.', route('backoffice.item-variants.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Failed to import item variants.', $throwable);
        }
    }
}

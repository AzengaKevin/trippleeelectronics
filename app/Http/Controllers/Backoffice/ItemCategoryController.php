<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\ItemCategoryExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StoreItemCategoryRequest;
use App\Http\Requests\UpdateItemCategoryRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\ItemCategoryImport;
use App\Models\ItemCategory;
use App\Services\ExcelService;
use App\Services\ItemCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ItemCategoryController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    public function __construct(private ItemCategoryService $itemCategoryService) {}

    public function index(Request $request)
    {

        $params = $request->only('query', 'perPage');

        $categories = $this->itemCategoryService->get(...$params, with: ['parent'])
            ->through(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'parent' => $category->parent ? $category->parent?->name : null,
                'image_url' => $category->getFirstMediaUrl(),
            ]);

        return Inertia::render('backoffice/item-categories/IndexPage', compact('categories', 'params'));
    }

    public function create()
    {
        $categories = $this->itemCategoryService->get(perPage: null);

        return Inertia::render('backoffice/item-categories/CreatePage', compact('categories'));
    }

    public function store(StoreItemCategoryRequest $storeItemCategoryRequest): RedirectResponse
    {

        $data = $storeItemCategoryRequest->validated();

        try {

            /** @var User $currentUser */
            $currentUser = request()->user();

            $data['author_user_id'] = $currentUser->id;

            $this->itemCategoryService->create($data);

            return $this->sendSuccessRedirect('Created a new category', route('backoffice.item-categories.index'));

        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to create an item category', $throwable);
        }
    }

    public function show(ItemCategory $category): Response
    {
        $category->load('author');

        $category->image_url = $category->getFirstMediaUrl();

        return Inertia::render('backoffice/item-categories/ShowPage', compact('category'));
    }

    public function edit(ItemCategory $category): Response
    {
        $categories = $this->itemCategoryService->get(perPage: null);

        return Inertia::render('backoffice/item-categories/EditPage', compact('category', 'categories'));
    }

    public function update(UpdateItemCategoryRequest $updateItemCategoryRequest, ItemCategory $category)
    {

        $data = $updateItemCategoryRequest->validated();

        try {

            $this->itemCategoryService->update($category, $data);

            return $this->sendSuccessRedirect('Updated an item category', route('backoffice.item-categories.show', $category));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to update an item category', $throwable);
        }
    }

    public function destroy(Request $request, string $category): RedirectResponse
    {
        try {

            $forever = $request->boolean('forever');

            $itemCategory = ItemCategory::query()->findOrFail($category);

            $this->itemCategoryService->delete($itemCategory, $forever);

            return $this->sendSuccessRedirect('Deleted an item category', route('backoffice.item-categories.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to delete an item category', $throwable);
        }
    }

    public function export(Request $request)
    {
        $params = $request->only('query', 'limit');

        $itemCategoryExport = new ItemCategoryExport($params);

        return $itemCategoryExport->download(ItemCategory::getExportFilename());
    }

    public function import()
    {
        return Inertia::render('backoffice/item-categories/ImportPage');
    }

    public function processImport(ImportRequest $importRequest): RedirectResponse
    {
        $data = $importRequest->validated();

        try {

            $this->robustImport(new ItemCategoryImport, $data['file'], 'item-categories', 'item-categories');

            return $this->sendSuccessRedirect('Imported item categories', route('backoffice.item-categories.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to import item categories', $throwable);
        }
    }
}

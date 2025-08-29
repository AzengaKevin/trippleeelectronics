<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\OrganizationCategoryExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StoreOrganizationCategoryRequest;
use App\Http\Requests\UpdateOrganizationCategoryRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\OrganizationCategoryImport;
use App\Models\OrganizationCategory;
use App\Services\ExcelService;
use App\Services\OrganizationCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrganizationCategoryController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    public function __construct(private OrganizationCategoryService $organizationCategoryService) {}

    public function index(Request $request)
    {
        $params = $request->only('query', 'perPage');

        $categories = $this->organizationCategoryService->get(...$params);

        return Inertia::render('backoffice/organization-categories/IndexPage', compact('categories', 'params'));
    }

    public function create()
    {
        return Inertia::render('backoffice/organization-categories/CreatePage');
    }

    public function store(StoreOrganizationCategoryRequest $storeOrganizationCategoryRequest): RedirectResponse
    {
        $data = $storeOrganizationCategoryRequest->validated();

        try {

            $data['author_user_id'] = request()->user()->id;

            $this->organizationCategoryService->create($data);

            return $this->sendSuccessRedirect('Organization category created successfully.', route('backoffice.organization-categories.index'));

        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Organization category creation failed.', $throwable);

        }
    }

    public function show(OrganizationCategory $category)
    {
        $category->load(['author']);

        return Inertia::render('backoffice/organization-categories/ShowPage', compact('category'));
    }

    public function edit(OrganizationCategory $category)
    {
        return Inertia::render('backoffice/organization-categories/EditPage', compact('category'));
    }

    public function update(UpdateOrganizationCategoryRequest $updateOrganizationCategoryRequest, OrganizationCategory $category): RedirectResponse
    {
        $data = $updateOrganizationCategoryRequest->validated();

        try {

            $this->organizationCategoryService->update($category, $data);

            return $this->sendSuccessRedirect('Organization category updated successfully.', route('backoffice.organization-categories.show', $category));

        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Organization category update failed.', $throwable);

        }
    }

    public function destroy(OrganizationCategory $category): RedirectResponse
    {
        try {

            $this->organizationCategoryService->delete($category);

            return $this->sendSuccessRedirect('Organization category deleted successfully.', route('backoffice.organization-categories.index'));

        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Organization category deletion failed.', $throwable);

        }
    }

    public function export(Request $request)
    {
        $data = $request->only('query', 'perPage');

        $orgCategoryExport = new OrganizationCategoryExport($data);

        $filename = OrganizationCategory::getExportFilename();

        return $orgCategoryExport->download($filename);
    }

    public function import()
    {
        return Inertia::render('backoffice/organization-categories/ImportPage');
    }

    public function processImport(ImportRequest $importRequest): RedirectResponse
    {
        $data = $importRequest->validated();

        try {

            $this->robustImport(new OrganizationCategoryImport, $data['file'], 'organization-categories', 'organization-categories');

            return $this->sendSuccessRedirect('Organization categories imported successfully.', route('backoffice.organization-categories.index'));

        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Organization categories import failed.', $throwable);

        }
    }
}

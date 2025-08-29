<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\OrganizationExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\OrganizationImport;
use App\Models\Organization;
use App\Models\OrganizationCategory;
use App\Services\ExcelService;
use App\Services\OrganizationCategoryService;
use App\Services\OrganizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    public function __construct(
        private OrganizationService $organizationService,
        private OrganizationCategoryService $organizationCategoryService,
    ) {}

    public function index(Request $request)
    {
        $params = $request->only('query', 'category');

        $filters = [...$params];

        if ($categoryId = data_get($filters, 'category')) {
            $filters['category'] = OrganizationCategory::query()->findOrFail($categoryId);
        }

        $organizations = $this->organizationService->get(...$filters, with: ['organizationCategory']);

        $categories = $this->organizationCategoryService->get(perPage: null)->map(fn ($category) => $category->only(['id', 'name']));

        return Inertia::render('backoffice/organizations/IndexPage', compact('organizations', 'params', 'categories'));
    }

    public function create()
    {
        $categories = $this->organizationCategoryService->get(perPage: null)->map(fn ($category) => $category->only(['id', 'name']));

        return Inertia::render('backoffice/organizations/CreatePage', compact('categories'));
    }

    public function store(StoreOrganizationRequest $storeOrganizationRequest): RedirectResponse
    {
        $data = $storeOrganizationRequest->validated();

        $data['author_user_id'] = request()->user()->id;

        try {
            $this->organizationService->create($data);

            return $this->sendSuccessRedirect('Organization created successfully.', route('backoffice.organizations.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect(
                'Organization creation failed.',
                $throwable
            );
        }
    }

    public function show(Organization $organization)
    {
        $organization->load(['organizationCategory', 'author']);

        $organization->image_url = $organization->getFirstMediaUrl();

        return Inertia::render('backoffice/organizations/ShowPage', compact('organization'));
    }

    public function edit(Organization $organization)
    {
        $categories = $this->organizationCategoryService->get(perPage: null)->map(fn ($category) => $category->only(['id', 'name']));

        return Inertia::render('backoffice/organizations/EditPage', compact('organization', 'categories'));
    }

    public function update(UpdateOrganizationRequest $updateOrganizationRequest, Organization $organization): RedirectResponse
    {
        $data = $updateOrganizationRequest->validated();

        try {
            $this->organizationService->update($organization, $data);

            return $this->sendSuccessRedirect('Organization updated successfully.', route('backoffice.organizations.show', $organization));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect(
                'Organization update failed.',
                $throwable
            );
        }
    }

    public function destroy(Organization $organization): RedirectResponse
    {
        try {
            $this->organizationService->delete($organization);

            return $this->sendSuccessRedirect('Organization deleted successfully.', route('backoffice.organizations.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect(
                'Organization deletion failed.',
                $throwable
            );
        }
    }

    public function import(): Response
    {
        return Inertia::render('backoffice/organizations/ImportPage');
    }

    public function processImport(ImportRequest $importRequest): RedirectResponse
    {
        $data = $importRequest->validated();

        try {

            $this->robustImport(new OrganizationImport, $data['file'], 'organizations', 'organizations');

            return $this->sendSuccessRedirect('Organizations imported successfully.', route('backoffice.organizations.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Organization import failed.', $throwable);
        }
    }

    public function export(Request $request)
    {
        $data = $request->only('query', 'perPage');

        $export = new OrganizationExport($data);

        $filename = Organization::getExportFilename();

        return $export->download($filename);
    }
}

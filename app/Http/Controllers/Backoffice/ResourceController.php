<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\ResourceExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StoreResourceRequest;
use App\Http\Requests\UpdateResourceRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\ResourceImport;
use App\Models\Resource;
use App\Services\ExcelService;
use App\Services\ResourceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResourceController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    public function __construct(private ResourceService $resourceService) {}

    public function index(Request $request)
    {
        $params = $request->only('query', 'perPage');

        $resources = $this->resourceService->get(...$params);

        return Inertia::render('backoffice/resources/IndexPage', compact('resources', 'params'));
    }

    public function create()
    {
        return Inertia::render('backoffice/resources/CreatePage');
    }

    public function store(StoreResourceRequest $storeResourceRequest): RedirectResponse
    {
        $data = $storeResourceRequest->validated();

        try {

            $data['author_user_id'] = request()->user()->id;

            $this->resourceService->create($data);

            return $this->sendSuccessRedirect('Resource created successfully.', route('backoffice.resources.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect(
                'Resource creation failed.',
                $throwable
            );
        }
    }

    public function show(Resource $resource)
    {
        return Inertia::render('backoffice/resources/ShowPage', compact('resource'));
    }

    public function edit(Resource $resource)
    {
        return Inertia::render('backoffice/resources/EditPage', compact('resource'));
    }

    public function update(UpdateResourceRequest $updateResourceRequest, Resource $resource): RedirectResponse
    {
        $data = $updateResourceRequest->validated();

        try {

            $this->resourceService->update($resource, $data);

            return $this->sendSuccessRedirect('Resource updated successfully.', route('backoffice.resources.show', $resource));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect(
                'Resource update failed.',
                $throwable
            );
        }
    }

    public function destroy(Request $request, string $resource): RedirectResponse
    {
        try {

            $resource = Resource::findOrFail($resource);

            $this->resourceService->delete($resource, $request->boolean('forever'));

            return $this->sendSuccessRedirect('Resource deleted successfully.', route('backoffice.resources.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect(
                'Resource deletion failed.',
                $throwable
            );
        }
    }

    public function export(Request $request)
    {

        $data = $request->only('query', 'limit');

        $resourceExport = new ResourceExport($data);

        return $resourceExport->download(Resource::getExportFilename());
    }

    public function import()
    {
        return Inertia::render('backoffice/resources/ImportPage');
    }

    public function processImport(ImportRequest $importRequest): RedirectResponse
    {
        $data = $importRequest->validated();

        try {

            $this->robustImport(new ResourceImport, $data['file'], 'resources', 'resources');

            return $this->sendSuccessRedirect('Imported resources successfully.', route('backoffice.resources.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to import resources', $throwable);
        }
    }
}

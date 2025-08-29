<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\ServiceExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\ServiceImport;
use App\Models\Service;
use App\Services\ExcelService;
use App\Services\ServiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    public function __construct(private ServiceService $serviceService) {}

    public function index(Request $request)
    {
        $params = $request->only('query', 'perPage');

        $services = $this->serviceService->get(...$params);

        return Inertia::render('backoffice/services/IndexPage', compact('services', 'params'));
    }

    public function create()
    {
        return Inertia::render('backoffice/services/CreatePage');
    }

    public function store(StoreServiceRequest $storeServiceRequest): RedirectResponse
    {
        $data = $storeServiceRequest->validated();

        try {

            $data['author_user_id'] = request()->user()->id;

            $this->serviceService->create($data);

            return $this->sendSuccessRedirect('Service created successfully.', route('backoffice.services.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect(
                'Service creation failed.',
                $throwable
            );
        }
    }

    public function show(Service $service)
    {
        $service->image_url = $service->getFirstMediaUrl();

        return Inertia::render('backoffice/services/ShowPage', compact('service'));
    }

    public function edit(Service $service)
    {
        return Inertia::render('backoffice/services/EditPage', compact('service'));
    }

    public function update(UpdateServiceRequest $updateServiceRequest, Service $service): RedirectResponse
    {
        $data = $updateServiceRequest->validated();

        try {

            $this->serviceService->update($service, $data);

            return $this->sendSuccessRedirect('Service updated successfully.', route('backoffice.services.show', $service));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect(
                'Service update failed.',
                $throwable
            );
        }
    }

    public function destroy(Request $request, string $service): RedirectResponse
    {
        try {

            $service = Service::findOrFail($service);

            $this->serviceService->delete($service, $request->boolean('forever'));

            return $this->sendSuccessRedirect('Service deleted successfully.', route('backoffice.services.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect(
                'Service deletion failed.',
                $throwable
            );
        }
    }

    public function export(Request $request)
    {

        $data = $request->only('query', 'limit');

        $serviceExport = new ServiceExport($data);

        return $serviceExport->download(Service::getExportFilename());
    }

    public function import()
    {
        return Inertia::render('backoffice/services/ImportPage');
    }

    public function processImport(ImportRequest $importRequest): RedirectResponse
    {
        $data = $importRequest->validated();

        try {

            $this->robustImport(new ServiceImport, $data['file'], 'services', 'services');

            return $this->sendSuccessRedirect('Imported services', route('backoffice.services.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to import services', $throwable);
        }
    }
}

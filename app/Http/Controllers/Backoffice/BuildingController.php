<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\BuildingExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StoreBuildingRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\BuildingImport;
use App\Models\Building;
use App\Models\Property;
use App\Services\BuildingService;
use App\Services\ExcelService;
use App\Services\PropertyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BuildingController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    public function __construct(
        private readonly BuildingService $buildingService,
        private readonly PropertyService $propertyService,
    ) {}

    public function index(Request $request): Response
    {
        $params = $request->only('query', 'property');

        $filters = [...$params];

        if ($propertyId = data_get($params, 'property')) {

            $filters['property'] = Property::query()->findOrFail($propertyId);
        }

        $properties = $this->propertyService->get(perPage: null, orderBy: 'name', orderDirection: 'asc');

        $buildings = $this->buildingService->get(...$filters);

        return Inertia::render('backoffice/buildings/IndexPage', compact('buildings', 'params', 'properties'));
    }

    public function create(): Response
    {
        $properties = $this->propertyService->get(perPage: null, orderBy: 'name', orderDirection: 'asc');

        return Inertia::render('backoffice/buildings/CreatePage', compact('properties'));
    }

    public function store(StoreBuildingRequest $storeBuildingRequest): RedirectResponse
    {
        $data = $storeBuildingRequest->validated();

        try {

            $building = $this->buildingService->create($data);

            return $this->sendSuccessRedirect("You've successfully created the building, {$building->name}", route('backoffice.buildings.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('There was an error creating the building', $throwable);
        }
    }

    public function show(Building $building): Response
    {
        $building->load('property');

        return Inertia::render('backoffice/buildings/ShowPage', compact('building'));
    }

    public function edit(Building $building): Response
    {
        $properties = $this->propertyService->get(perPage: null, orderBy: 'name', orderDirection: 'asc');

        return Inertia::render('backoffice/buildings/EditPage', compact('building', 'properties'));
    }

    public function update(StoreBuildingRequest $updateBuildingRequest, Building $building): RedirectResponse
    {
        $data = $updateBuildingRequest->validated();

        try {

            $this->buildingService->update($building, $data);

            return $this->sendSuccessRedirect("You've successfully updated the building, {$building->name}", route('backoffice.buildings.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('There was an error updating the building', $throwable);
        }
    }

    public function destroy(Building $building): RedirectResponse
    {
        try {

            $this->buildingService->delete($building);

            return $this->sendSuccessRedirect("You've successfully deleted the building, {$building->name}", route('backoffice.buildings.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('There was an error deleting the building', $throwable);
        }
    }

    public function export(Request $request)
    {
        $params = $request->only('query', 'property');

        $filters = [...$params];

        if ($propertyId = data_get($params, 'property')) {

            $filters['property'] = Property::query()->findOrFail($propertyId);
        }

        $buildingExport = new BuildingExport($filters);

        return $buildingExport->download(Building::getExportFilename());
    }

    public function import(ImportRequest $importRequest): RedirectResponse
    {

        $data = $importRequest->validated();

        try {

            $file = data_get($data, 'file');

            $this->robustImport(new BuildingImport, $file, 'buildings', 'building');

            return $this->sendSuccessRedirect("You've successfully imported the buildings", route('backoffice.buildings.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('There was an error importing the buildings', $throwable);
        }
    }
}

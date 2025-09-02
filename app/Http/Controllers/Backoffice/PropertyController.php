<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\PropertyExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Models\Property;
use App\Services\PropertyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PropertyController extends Controller
{
    use RedirectWithFeedback;

    public function __construct(private readonly PropertyService $propertyService) {}

    public function index(Request $request): Response
    {
        $params = $request->only('query');

        $properties = $this->propertyService->get(...$params);

        return Inertia::render('backoffice/properties/IndexPage', [
            'properties' => $properties,
            'params' => $params,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('backoffice/properties/CreatePage');
    }

    public function store(StorePropertyRequest $storePropertyRequest): RedirectResponse
    {
        $data = $storePropertyRequest->validated();

        try {

            $store = $this->propertyService->create($data);

            activity()
                ->performedOn($store)
                ->withProperties(['id' => $store->id])
                ->log('Property created.');

            return $this->sendSuccessRedirect("You've successfully created a property.", route('backoffice.properties.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('There was an error creating the property.', $throwable);
        }
    }

    public function show(Property $property): Response
    {
        return Inertia::render('backoffice/properties/ShowPage', [
            'property' => $property,
        ]);
    }

    public function edit(Property $property): Response
    {
        return Inertia::render('backoffice/properties/EditPage', [
            'property' => $property,
        ]);
    }

    public function update(UpdatePropertyRequest $updatePropertyRequest, Property $property): RedirectResponse
    {
        $data = $updatePropertyRequest->validated();

        try {

            $this->propertyService->update($property, $data);

            activity()
                ->performedOn($property)
                ->withProperties(['id' => $property->id])
                ->log('Property updated.');

            return $this->sendSuccessRedirect("You've successfully updated the property.", route('backoffice.properties.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('There was an error updating the property.', $throwable);
        }
    }

    public function destroy(Property $property): RedirectResponse
    {
        try {

            $this->propertyService->delete($property);

            activity()
                ->performedOn($property)
                ->withProperties(['id' => $property->id])
                ->log('Property deleted.');

            return $this->sendSuccessRedirect("You've successfully deleted the property.", route('backoffice.properties.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('There was an error deleting the property.', $throwable);
        }
    }

    public function export(Request $request)
    {
        $params = $request->only('query', 'limit');

        $propertyExport = new PropertyExport($params);

        return $propertyExport->download(Property::getExportFilename());
    }
}

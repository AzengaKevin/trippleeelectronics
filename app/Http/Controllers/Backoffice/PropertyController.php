<?php

namespace App\Http\Controllers\Backoffice;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Property;
use Illuminate\Http\Request;
use App\Services\PropertyService;
use App\Http\Controllers\Controller;
use App\Http\Responses\Concerns\RedirectWithFeedback;

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

    public function show(Property $property): Response
    {
        return Inertia::render('backoffice/properties/ShowPage', [
            'property' => $property,
        ]);
    }
}

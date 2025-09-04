<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Services\BuildingService;
use App\Services\PropertyService;
use App\Services\RoomService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    private Building $currentBuilding;

    public function __construct(
        private readonly RoomService $roomService,
        private readonly PropertyService $propertyService,
        private readonly BuildingService $buildingService,
    ) {
        $this->currentBuilding = Building::query()->first();
    }

    public function show(Request $request): Response
    {
        $params = $request->only('query');

        $buildings = $this->buildingService->get(perPage: null);

        $rooms = $this->roomService->get(...$params, building: $this->currentBuilding);

        return Inertia::render('backoffice/booking/ShowPage', [
            'currentBuilding' => $this->currentBuilding,
            'buildings' => $buildings,
            'rooms' => $rooms,
            'params' => $params,
        ]);
    }
}

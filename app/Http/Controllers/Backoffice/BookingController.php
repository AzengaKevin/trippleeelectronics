<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Reservation;
use App\Services\BookingService;
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
        private readonly BookingService $bookingService,
    ) {
        $this->currentBuilding = Building::query()->first();
    }

    public function index(Request $request): Response
    {
        $params = $request->only(['query', 'date']);

        $filters = $params;

        if (is_null(data_get($filters, 'date'))) {
            $filters['date'] = now()->toDateString();
        }

        $buildings = $this->buildingService->get(perPage: null);

        $bookings = $this->bookingService->get(...$filters, building: $this->currentBuilding, with: ['allocation.reservation.author', 'allocation.reservation.primaryIndividual']);

        return Inertia::render('backoffice/bookings/IndexPage', [
            'currentBuilding' => $this->currentBuilding,
            'buildings' => $buildings,
            'bookings' => $bookings,
            'params' => $params,
        ]);
    }

    public function create()
    {
        return Inertia::render('backoffice/bookings/CreatePage');
    }

    public function show(Reservation $reservation)
    {
        return Inertia::render('backoffice/bookings/ShowPage', [
            'reservation' => $reservation,
        ]);
    }

    public function edit(Reservation $reservation)
    {
        return Inertia::render('backoffice/bookings/EditPage', [
            'reservation' => $reservation,
        ]);
    }
}

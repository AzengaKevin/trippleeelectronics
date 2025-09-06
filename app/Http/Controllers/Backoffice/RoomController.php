<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\RoomExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\RoomImport;
use App\Models\Room;
use App\Services\BookingService;
use App\Services\BuildingService;
use App\Services\ExcelService;
use App\Services\RoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    public function __construct(
        private readonly RoomService $roomService,
        private readonly BuildingService $buildingService,
        private readonly BookingService $bookingService,
    ) {}

    public function index(Request $request): Response
    {

        $params = $request->only('query');

        $rooms = $this->roomService->get(...$params);

        return Inertia::render('backoffice/rooms/IndexPage', compact('rooms', 'params'));
    }

    public function create(): Response
    {
        return Inertia::render('backoffice/rooms/CreatePage', [
            ...$this->getFormData(),
        ]);
    }

    public function occupancy(Request $request): Response
    {
        $params = $request->only(['query', 'date']);

        $filters = $params;

        if (is_null(data_get($filters, 'date'))) {
            $filters['date'] = now()->toDateString();
        }

        $buildings = $this->buildingService->get(perPage: null);

        $bookings = $this->bookingService->get(...$filters, with: ['allocation.reservation.author', 'allocation.reservation.primaryIndividual']);

        return Inertia::render('backoffice/bookings/OccupancyPage', [
            'buildings' => $buildings,
            'bookings' => $bookings,
            'params' => $params,
        ]);
    }

    public function store(StoreRoomRequest $storeRoomRequest): RedirectResponse
    {
        $data = $storeRoomRequest->validated();

        try {

            $room = $this->roomService->create($data);

            return $this->sendSuccessRedirect("You've successfully created the room, {$room->name}", route('backoffice.rooms.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed creating a new room', $throwable);
        }
    }

    public function show(Room $room): Response
    {
        $room->load(['property', 'building', 'roomType']);

        return Inertia::render('backoffice/rooms/ShowPage', compact('room'));
    }

    public function edit(Room $room): Response
    {
        return Inertia::render('backoffice/rooms/EditPage', [
            ...$this->getFormData(),
            'room' => $room,
        ]);
    }

    public function update(UpdateRoomRequest $updateRoomRequest, Room $room): RedirectResponse
    {
        $data = $updateRoomRequest->validated();

        try {

            $this->roomService->update($room, $data);

            $room->refresh();

            return $this->sendSuccessRedirect("You've successfully updated the room, {$room->name}", route('backoffice.rooms.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect("Failed updating the room, {$room->name}", $throwable);
        }
    }

    public function destroy(Room $room): RedirectResponse
    {
        try {

            $this->roomService->delete($room);

            return $this->sendSuccessRedirect("You've successfully deleted the room, {$room->name}", route('backoffice.rooms.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect("Failed deleting the room, {$room->name}", $throwable);
        }
    }

    public function export(Request $request)
    {
        $params = $request->only('query', 'limit');

        $filename = Room::getExportFileName();

        $roomExport = new RoomExport($params);

        return $roomExport->download($filename);
    }

    public function import(ImportRequest $importRequest): RedirectResponse
    {
        $data = $importRequest->validated();

        try {

            $file = data_get($data, 'file');

            $this->robustImport(new RoomImport, $file, 'rooms', 'rooms');

            return $this->sendSuccessRedirect('You have successfully imported the rooms data', route('backoffice.rooms.index'));

        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed importing the rooms data', $throwable);
        }
    }

    private function getFormData(): array
    {
        $buildings = $this->buildingService->get(perPage: null);

        $roomTypes = Collection::empty();

        return compact('buildings', 'roomTypes');
    }
}

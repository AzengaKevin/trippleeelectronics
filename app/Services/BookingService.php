<?php

namespace App\Services;

use App\Models\Allocation;
use App\Models\Building;
use App\Models\Property;
use App\Models\Room;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function get(
        ?string $query = null,
        ?Property $property = null,
        ?Building $building = null,
        ?string $date = null,
        ?int $perPage = 24,
        ?int $limit = null,
        ?array $with = null,
        ?array $withCount = null,
    ) {

        $dbDriver = DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $daysPeriodQuery = match ($dbDriver) {
            'mysql' => 'TIMESTAMPDIFF(DAY, start, end) AS period_days',
            'pgsql' => 'EXTRACT(DAY FROM "end" - start) AS period_days',
            'sqlite' => 'CAST((julianday(end) - julianday(start)) AS INTEGER) AS period_days',
            default => 'DATEDIFF(DAY, start, [end]) AS allocation_days',
        };

        $hoursPeriodQuery = match ($dbDriver) {
            'mysql' => 'TIMESTAMPDIFF(HOUR, start, end) AS period_hours',
            'pgsql' => 'EXTRACT(HOUR FROM "end" - start) AS period_hours',
            'sqlite' => 'CAST((julianday(end) - julianday(start)) * 24 AS INTEGER) AS period_hours',
            default => 'DATEDIFF(HOUR, start, [end]) AS allocation_hours',
        };

        $bookingQuery = Room::query()->select('rooms.*')
            ->addSelect([
                'allocation_id' => Allocation::query()
                    ->select('id')
                    ->whereColumn('allocations.room_id', 'rooms.id')
                    ->when($date, function ($query, $date) {
                        return $query->whereDate('allocations.date', '=', $date)
                            ->orWhere(function ($query) use ($date) {
                                $query->whereDate('allocations.start', '>', $date)
                                    ->whereDate('allocations.end', '<=', $date);
                            });
                    })
                    ->orderBy('allocations.created_at', 'desc')
                    ->limit(1),
                'period_days' => Allocation::query()
                    ->select(DB::raw($daysPeriodQuery))
                    ->whereColumn('allocations.room_id', 'rooms.id')
                    ->orderBy('allocations.created_at', 'desc')
                    ->limit(1),
                'period_hours' => Allocation::query()
                    ->select(DB::raw($hoursPeriodQuery))
                    ->whereColumn('allocations.room_id', 'rooms.id')
                    ->orderBy('allocations.created_at', 'desc')
                    ->limit(1),
            ])
            ->when($limit, fn ($query, $limit) => $query->limit($limit))
            ->when($with, fn ($query, $with) => $query->with($with))
            ->when($withCount, fn ($query, $withCount) => $query->withCount($withCount))
            ->when($property, fn ($query) => $query->where('rooms.property_id', $property->id))
            ->when($building, fn ($query) => $query->where('rooms.building_id', $building->id));

        return is_null($perPage)
            ? $bookingQuery->get()
            : $bookingQuery->paginate($perPage);
    }

    public function bookRoom(Room $room, array $data)
    {

        // Create a reservation

        // Create an allocation to the booking

        //
    }

    public function cancelBooking(Allocation $allocation, array $data) {}
}

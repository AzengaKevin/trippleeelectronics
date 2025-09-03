<?php

namespace App\Services;

use App\Models\Building;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Support\Facades\DB;

class RoomService
{
    public function get(
        ?string $query = null,
        ?array $with = null,
        ?array $withCount = null,
        ?int $limit = null,
        ?int $perPage = 24,
        ?string $orderBy = 'created_at',
        ?string $orderDirection = 'asc',
    ) {

        $roomQuery = Room::search($query, function ($defaultQuery) use ($with, $withCount, $limit) {

            $defaultQuery->when($with, fn ($q) => $q->with($with))
                ->when($withCount, fn ($q) => $q->withCount($withCount))
                ->when($limit, fn ($q) => $q->take($limit));
        });

        $roomQuery->orderBy($orderBy, $orderDirection);

        return is_null($perPage)
            ? $roomQuery->get()
            : $roomQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Room
    {
        return DB::transaction(function () use ($data) {

            $attributes = [
                'property_id' => data_get($data, 'property'),
                'building_id' => data_get($data, 'building'),
                'floor_id' => data_get($data, 'floor'),
                'room_type_id' => data_get($data, 'room_type'),
                'name' => data_get($data, 'name'),
                'code' => data_get($data, 'code'),
                'occupancy' => data_get($data, 'occupancy'),
                'active' => data_get($data, 'active'),
                'status' => data_get($data, 'status'),
                'price' => data_get($data, 'price'),
            ];

            return Room::query()->create($attributes);
        });
    }

    public function update(Room $room, array $data): bool
    {
        return DB::transaction(function () use ($room, $data) {

            $attributes = [
                'property_id' => data_get($data, 'property', $room->property_id),
                'building_id' => data_get($data, 'building', $room->building_id),
                'floor_id' => data_get($data, 'floor', $room->floor_id),
                'room_type_id' => data_get($data, 'room_type', $room->room_type_id),
                'name' => data_get($data, 'name', $room->name),
                'code' => data_get($data, 'code', $room->code),
                'occupancy' => data_get($data, 'occupancy', $room->occupancy),
                'active' => data_get($data, 'active', $room->active),
                'status' => data_get($data, 'status', $room->status),
                'price' => data_get($data, 'price', $room->price),
            ];

            return $room->update($attributes);
        });
    }

    public function delete(Room $room): bool
    {
        return DB::transaction(function () use ($room) {
            return $room->delete();
        });
    }

    public function importRow(array $data): Room
    {
        return DB::transaction(function () use ($data) {

            $building = Building::firstWhere('code', data_get($data, 'building_code'));

            $roomType = RoomType::firstWhere('code', data_get($data, 'room_type_code'));

            $attributes = [
                'building_id' => $building?->id,
                'room_type_id' => $roomType?->id,
                'name' => data_get($data, 'name'),
                'code' => data_get($data, 'code'),
                'occupancy' => data_get($data, 'occupancy'),
                'active' => data_get($data, 'active'),
                'status' => data_get($data, 'status'),
                'price' => data_get($data, 'price'),
            ];

            return Room::query()->create($attributes);
        });
    }
}

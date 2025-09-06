<?php

namespace App\Services;

use App\Models\Allocation;

class AllocationService
{
    public function create(array $data)
    {
        $attributes = [
            'reservation_id' => data_get($data, 'reservation_id'),
            'room_type_id' => data_get($data, 'room_type'),
            'room_id' => data_get($data, 'room'),
            'date' => data_get($data, 'date'),
            'start' => data_get($data, 'start'),
            'end' => data_get($data, 'end'),
            'occupants' => data_get($data, 'occupants'),
            'discount' => data_get($data, 'discount'),
            'status' => data_get($data, 'status'),
        ];

        return Allocation::query()->create($attributes);
    }
}

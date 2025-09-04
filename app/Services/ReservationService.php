<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $attributes = [
                'author_user_id' => data_get($data, 'current_user_id'),
                'property_id' => data_get($data, 'property_id'),
                'primary_individual_id' => data_get($data, 'primary_individual_id'),
                'checkin_date' => data_get($data, 'checkin_date'),
                'checkout_date' => data_get($data, 'checkout_date'),
                'adults' => data_get($data, 'adults', 1),
                'children' => data_get($data, 'children', 0),
                'infants' => data_get($data, 'infants', 0),
            ];

            return Reservation::query()->create($attributes);
        });
    }
}

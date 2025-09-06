<?php

namespace App\Services;

use App\Models\Reservation;

class ReservationService
{
    public function __construct(
        private readonly IndividualService $individualService,
        private readonly ClientService $clientService,
        private readonly AllocationService $allocationService,
    ) {}

    public function create(array $data)
    {
        $attributes = [
            'author_user_id' => data_get($data, 'current_user_id'),
            'property_id' => data_get($data, 'property_id'),
            'primary_individual_id' => data_get($data, 'primary_individual_id'),
            'checkin_date' => data_get($data, 'checkin_date'),
            'checkout_date' => data_get($data, 'checkout_date'),
            'guests_count' => data_get($data, 'guests_count'),
            'rooms_count' => data_get($data, 'rooms_count'),
            'total_amount' => data_get($data, 'total_amount'),
            'tendered_amount' => data_get($data, 'tendered_amount'),
            'balance_amount' => data_get($data, 'balance_amount'),
        ];

        /** @var Reservation $reservation */
        $reservation = Reservation::query()->create($attributes);

        $individuals = collect(data_get($data, 'guests'))->map(fn ($item) => $this->clientService->upsert($item));

        $primaryIndividual = $individuals->first();

        $reservation->update(['primary_individual_id' => $primaryIndividual->id]);

        $reservation->individuals()->attach($individuals);

        collect(data_get($data, 'allocations'))->each(function ($item) use ($reservation) {

            $this->allocationService->create([
                ...$item,
                'reservation_id' => $reservation->id,
            ]);
        });

        return $reservation->fresh();
    }
}

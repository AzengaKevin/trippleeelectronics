<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    public function __construct(
        private readonly IndividualService $individualService,
        private readonly ClientService $clientService,
        private readonly AllocationService $allocationService,
    ) {}

    public function get(
        ?string $query = null,
        ?int $limit = null,
        ?int $perPage = 24,
        ?array $with = null,
        ?array $withCount = null,
        ?string $orderBy = 'created_at',
        ?string $orderDirection = 'desc',
    ) {

        $reservationQuery = Reservation::query();

        $reservationQuery->when($query, function ($defaultQuery, $query) {

            $dbDriver = DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

            $caseInsensitiveColumnQuery = match ($dbDriver) {
                'mysql' => 'LOWER(%s) LIKE LOWER(?)',
                'pgsql' => '%s ILIKE ?',
                'sqlite' => '%s LIKE ? COLLATE NOCASE',
                default => '%s LIKE ?',
            };

            $defaultQuery->where(function ($nestedInnerQuery) use ($query, $caseInsensitiveColumnQuery) {
                $nestedInnerQuery->whereHas('primaryIndividual', function ($level2NestedInnerQuery) use ($query, $caseInsensitiveColumnQuery) {

                    $level2NestedInnerQuery->whereRaw(sprintf($caseInsensitiveColumnQuery, 'name'), "%{$query}%")
                        ->orWhereRaw(sprintf($caseInsensitiveColumnQuery, 'phone'), "%{$query}%")
                        ->orWhereRaw(sprintf($caseInsensitiveColumnQuery, 'email'), "%{$query}%");
                })->orWhereHas('author', function ($level2NestedInnerQuery) use ($query, $caseInsensitiveColumnQuery) {

                    $level2NestedInnerQuery->whereRaw(sprintf($caseInsensitiveColumnQuery, 'name'), "%{$query}%")
                        ->orWhereRaw(sprintf($caseInsensitiveColumnQuery, 'phone'), "%{$query}%")
                        ->orWhereRaw(sprintf($caseInsensitiveColumnQuery, 'email'), "%{$query}%");
                })->orWhereRaw(sprintf($caseInsensitiveColumnQuery, 'reference'), "%{$query}%");
            });
        });

        $reservationQuery->when($limit, fn ($query) => $query->limit($limit));

        $reservationQuery->when($with, fn ($query) => $query->with($with));

        $reservationQuery->when($withCount, fn ($query) => $query->withCount($withCount));

        $reservationQuery->orderBy($orderBy, $orderDirection);

        return is_null($perPage)
            ? $reservationQuery->get()
            : $reservationQuery->paginate($perPage);
    }

    public function create(array $data): Reservation
    {

        info($data);

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

        $individuals = collect(data_get($data, 'guests'))->map(fn ($item) => $this->individualService->upsert($item));

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

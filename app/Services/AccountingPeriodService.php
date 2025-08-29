<?php

namespace App\Services;

use App\Models\AccountingPeriod;

class AccountingPeriodService
{
    public function get(
        ?int $perPage = 24,
        ?int $limit = null,
        ?string $start_date = null,
        ?string $end_date = null,
        ?array $with = null,
        ?array $withCount = null,
        ?string $orderBy = 'start_date',
        ?string $orderDirection = 'desc',
    ) {
        $accountingPeriodQuery = AccountingPeriod::query();

        $accountingPeriodQuery->when($start_date, function ($query, $startDate) {
            return $query->where('start_date', '>=', $startDate);
        });

        $accountingPeriodQuery->when($end_date, function ($query, $endDate) {
            return $query->where('end_date', '<=', $endDate);
        });

        $accountingPeriodQuery->when($with, function ($query, $with) {
            return $query->with($with);
        });

        $accountingPeriodQuery->when($withCount, function ($query, $withCount) {
            return $query->withCount($withCount);
        });

        $accountingPeriodQuery->when($limit, function ($query, $limit) {
            return $query->limit($limit);
        });

        $accountingPeriodQuery->orderBy($orderBy, $orderDirection);

        return is_null($perPage)
            ? $accountingPeriodQuery->get()
            : $accountingPeriodQuery->paginate($perPage);
    }

    public function generateAccountingPeriods(string $startDate, string $endDate): void
    {
        $startDate = \Carbon\Carbon::parse($startDate);

        $endDate = \Carbon\Carbon::parse($endDate);

        while ($startDate <= $endDate) {

            $attributes = [
                'start_date' => $startDate->copy()->startOfMonth(),
                'end_date' => $startDate->copy()->endOfMonth(),
            ];

            AccountingPeriod::query()->updateOrCreate($attributes);

            $startDate->addMonth();
        }
    }
}

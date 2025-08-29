<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Suspension;
use Illuminate\Support\Facades\DB;

class SuspensionService
{
    public function get(
        ?int $perPage = 24,
        ?int $limit = null,
        ?array $with = null,
        ?array $withCount = null,
        ?Employee $employee = null,
        ?string $orderBy = 'created_at',
        ?string $orderDir = 'desc',
    ) {

        $contractQuery = Suspension::query();

        $contractQuery->when($employee, fn ($innerQuery) => $innerQuery->where('employee_id', $employee->id));

        $contractQuery->when($with, fn ($innerQuery) => $innerQuery->with($with));

        $contractQuery->when($withCount, fn ($innerQuery) => $innerQuery->withCount($withCount));

        $contractQuery->when($limit, fn ($innerQuery) => $innerQuery->limit($limit));

        $contractQuery->orderBy($orderBy, $orderDir);

        return is_null($perPage)
            ? $contractQuery->get()
            : $contractQuery->paginate();
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $attributes = [
                'employee_id' => data_get($data, 'employee'),
                'from' => data_get($data, 'from'),
                'to' => data_get($data, 'to'),
                'reason' => data_get($data, 'reason'),
            ];

            return Suspension::query()->create($attributes);
        });
    }

    public function update(Suspension $suspension, array $data)
    {
        return DB::transaction(function () use ($suspension, $data) {

            $attributes = [
                'employee_id' => data_get($data, 'employee', $suspension->employee_id),
                'from' => data_get($data, 'from', $suspension->from),
                'to' => data_get($data, 'to', $suspension->to),
                'reason' => data_get($data, 'reason', $suspension->reason),
            ];

            return $suspension->update($attributes);
        });
    }

    public function delete(Suspension $suspension)
    {

        return $suspension->delete();
    }
}

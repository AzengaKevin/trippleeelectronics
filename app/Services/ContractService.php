<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Employee;
use App\Models\Enums\ContractStatus;
use App\Models\Enums\ContractType;
use Illuminate\Support\Facades\DB;

class ContractService
{
    public function get(
        ?int $perPage = 24,
        ?int $limit = null,
        ?array $with = null,
        ?array $withCount = null,
        ?Employee $employee = null,
        ?ContractType $contractType = null,
        ?ContractStatus $contractStatus = null,
        ?string $orderBy = 'start_date',
        ?string $orderDir = 'desc',
    ) {

        $contractQuery = Contract::query();

        $contractQuery->when($employee, fn ($innerQuery) => $innerQuery->where('employee_id', $employee->id));

        $contractQuery->when($contractType, fn ($innerQuery) => $innerQuery->where('contract_type', $contractType->value));

        $contractQuery->when($contractStatus, fn ($innerQuery) => $innerQuery->where('status', $contractStatus->value));

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
                'contract_type' => data_get($data, 'contract_type'),
                'start_date' => data_get($data, 'start_date'),
                'end_date' => data_get($data, 'end_date'),
                'salary' => data_get($data, 'salary'),
                'status' => data_get($data, 'status'),
                'responsibilities' => data_get($data, 'responsibilities'),
            ];

            return Contract::query()->create($attributes);
        });
    }

    public function update(Contract $contract, array $data)
    {
        return DB::transaction(function () use ($contract, $data) {

            $attributes = [
                'employee_id' => data_get($data, 'employee', $contract->employee_id),
                'contract_type' => data_get($data, 'contract_type', $contract->contract_type),
                'start_date' => data_get($data, 'start_date', $contract->start_date),
                'end_date' => data_get($data, 'end_date', $contract->end_date),
                'salary' => data_get($data, 'salary', $contract->salary),
                'status' => data_get($data, 'status', $contract->status),
                'responsibilities' => data_get($data, 'responsibilities', $contract->responsibilities),
            ];

            return $contract->update($attributes);
        });
    }

    public function delete(Contract $contract)
    {

        return $contract->delete();
    }
}

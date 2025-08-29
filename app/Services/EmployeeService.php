<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Enums\EmployeeDocument;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    public function __construct(private readonly SuspensionService $suspensionService) {}

    public function get(
        ?string $query = null,
        ?int $perPage = 24,
        ?int $limit = null,
        ?array $with = null,
        ?array $withCount = null,
    ) {
        $employeeQuery = Employee::search($query, function ($queryBuilder) use ($limit, $with, $withCount) {
            $queryBuilder->when($limit, function ($queryBuilder, $limit) {
                $queryBuilder->limit($limit);
            });

            $queryBuilder->when($with, function ($queryBuilder, $with) {
                $queryBuilder->with($with);
            });

            $queryBuilder->when($withCount, function ($queryBuilder, $withCount) {
                $queryBuilder->withCount($withCount);
            });
        });

        return is_null($perPage)
            ? $employeeQuery->get()
            : $employeeQuery->paginate($perPage)->withQueryString();
    }

    public function update(Employee $employee, array $data)
    {
        return DB::transaction(function () use ($employee, $data) {

            $attributes = [
                'name' => data_get($data, 'name'),
                'email' => data_get($data, 'email'),
                'phone' => data_get($data, 'phone'),
                'position' => data_get($data, 'position'),
                'department' => data_get($data, 'department'),
                'kra_pin' => data_get($data, 'kra_pin'),
                'identification_number' => data_get($data, 'identification_number'),
                'hire_date' => data_get($data, 'hire_date'),
            ];

            tap($employee)->update($attributes);

            if ($documents = data_get($data, 'documents')) {

                collect($documents)->each(function ($document) use ($employee) {

                    $file = data_get($document, 'file');

                    $type = data_get($document, 'type');

                    $employeeDocument = EmployeeDocument::tryFrom($type);

                    $filename = str($employee->name)->slug()->when($employeeDocument, fn ($str) => $str->append('-', $employeeDocument->value))->append('.')->append($file->extension())->value();

                    $name = str($employee->name)->append(' ')->append($employeeDocument->label())->value();

                    $employee->addMedia($file)->usingFileName($filename)->usingName($name)->toMediaCollection($type);
                });
            }

            return true;
        });
    }

    public function suspend(Employee $employee)
    {
        return DB::transaction(function () use ($employee) {

            $data = [
                'employee' => $employee->id,
                'from' => now()->toDateString(),
                'to' => null,
                'reason' => null,
            ];

            return $this->suspensionService->create($data);
        });
    }

    public function delete(Employee $employee)
    {
        return DB::transaction(function () use ($employee) {

            return $employee->delete();
        });
    }
}

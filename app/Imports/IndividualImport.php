<?php

namespace App\Imports;

use App\Models\Organization;
use App\Services\IndividualService;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class IndividualImport extends StringValueBinder implements OnEachRow, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, WithCustomValueBinder, WithHeadingRow, WithStartRow, WithValidation
{
    use Importable, SkipsErrors, SkipsFailures;

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            'organization' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'kra_pin' => ['nullable', 'string', 'max:255'],
            'id_number' => ['nullable', 'string', 'max:255'],
            'created_at' => ['nullable'],
            'updated_at' => ['nullable'],
            'deleted_at' => ['nullable'],
        ];
    }

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        if (! empty(data_get($data, 'organization'))) {

            $organization = Organization::query()->where('name', data_get($data, 'organization'))->first();

            $data['organization_id'] = $organization->id;
        }

        app(IndividualService::class)->importRow($data);
    }
}

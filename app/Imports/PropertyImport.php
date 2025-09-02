<?php

namespace App\Imports;

use App\Models\Property;
use App\Services\PropertyService;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
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

class PropertyImport extends StringValueBinder implements OnEachRow, SkipsOnError, SkipsOnFailure, WithCustomValueBinder, WithHeadingRow, WithStartRow, WithValidation
{
    use Importable, SkipsErrors, SkipsFailures;

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', Rule::unique(Property::class, 'name')],
            'code' => ['nullable', Rule::unique(Property::class, 'code')],
            'address' => ['nullable'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        app(PropertyService::class)->importRow($data);
    }
}

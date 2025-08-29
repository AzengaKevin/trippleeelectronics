<?php

namespace App\Imports;

use App\Services\StoreService;
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

class StoreImport extends StringValueBinder implements OnEachRow, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, WithCustomValueBinder, WithHeadingRow, WithStartRow, WithValidation
{
    use Importable, SkipsErrors, SkipsFailures;

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['required', 'string', 'max:24'],
            'phone' => ['nullable', 'string', 'max:20'],
            'paybill' => ['nullable', 'string', 'max:20'],
            'account_number' => ['nullable', 'string', 'max:20'],
            'till' => ['nullable', 'string', 'max:20'],
            'kra_pin' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        app(StoreService::class)->importRow($data);
    }
}

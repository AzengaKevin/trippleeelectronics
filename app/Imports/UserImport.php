<?php

namespace App\Imports;

use App\Services\UserService;
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

class UserImport extends StringValueBinder implements OnEachRow, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, WithCustomValueBinder, WithHeadingRow, WithStartRow, WithValidation
{
    use Importable, SkipsErrors, SkipsFailures;

    public function startRow(): int
    {
        return 2; // Skip the first row (header)
    }

    public function rules(): array
    {
        return [
            'reference' => ['nullable'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email_verified_at' => ['nullable', 'date'],
            'phone_verified_at' => ['nullable', 'date'],
            'password' => ['required', 'string', 'min:8'],
            'crated_at' => ['nullable', 'date'],
            'updated_at' => ['nullable', 'date'],
            'deleted_at' => ['nullable', 'date'],
        ];
    }

    public function onRow(Row $row)
    {

        $data = $row->toArray();

        app(UserService::class)->importRow($data);
    }
}

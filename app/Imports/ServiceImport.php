<?php

namespace App\Imports;

use App\Models\User;
use App\Services\ServiceService;
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

class ServiceImport extends StringValueBinder implements OnEachRow, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, WithCustomValueBinder, WithHeadingRow, WithStartRow, WithValidation
{
    use Importable, SkipsErrors, SkipsFailures;

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            'author_phone' => ['nullable', 'string', 'max:25'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'max:400'],
        ];
    }

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        if ($authorPhone = data_get($data, 'author_phone')) {
            $data['author_user_id'] = User::query()->where('phone', $authorPhone)->value('id');
        }

        app(ServiceService::class)->importRow($data);
    }
}

<?php

namespace App\Imports;

use App\Services\PaymentService;
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

class PaymentImport extends StringValueBinder implements OnEachRow, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, WithCustomValueBinder, WithHeadingRow, WithStartRow, WithValidation
{
    use Importable, SkipsErrors, SkipsFailures;

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            'author' => ['nullable', 'string', 'max:255'],
            'payable' => ['nullable', 'string', 'max:255'],
            'payer' => ['nullable', 'string', 'max:255'],
            'payee' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric'],
            'payment_method' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:255'],
        ];
    }

    public function onRow(Row $row)
    {

        $data = $row->toArray();

        app(PaymentService::class)->importRow($data);
    }
}

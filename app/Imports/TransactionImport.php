<?php

namespace App\Imports;

use App\Models\Enums\TransactionMethod;
use App\Models\Enums\TransactionStatus;
use App\Services\TransactionService;
use Illuminate\Validation\Rule;
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

class TransactionImport extends StringValueBinder implements OnEachRow, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, WithCustomValueBinder, WithHeadingRow, WithStartRow, WithValidation
{
    use Importable, SkipsErrors, SkipsFailures;

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            'payment' => ['nullable', 'string'],
            'amount' => ['required', 'numeric'],
            'transaction_method' => ['required', 'string', Rule::in(TransactionMethod::options())],
            'till' => ['nullable', 'string'],
            'paybill' => ['nullable', 'string'],
            'account_number' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string'],
            'reference' => ['nullable', 'string'],
            'fee' => ['nullable', 'numeric'],
            'status' => ['nullable', 'string', Rule::in(TransactionStatus::options())],
        ];
    }

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        app(TransactionService::class)->importRow($data);
    }
}

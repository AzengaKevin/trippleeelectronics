<?php

namespace App\Imports;

use App\Models\Individual;
use App\Models\Organization;
use App\Models\Store;
use App\Models\User;
use App\Services\PurchaseService;
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

class PurchaseImport extends StringValueBinder implements OnEachRow, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, WithCustomValueBinder, WithHeadingRow, WithStartRow, WithValidation
{
    use Importable, SkipsErrors, SkipsFailures;

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            'reference' => ['nullable', 'string'],
            'author_phone' => ['nullable', 'string'],
            'supplier_phone' => ['nullable', 'string'],
            'store_short_name' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric'],
            'shipping_amount' => ['nullable', 'numeric'],
            'total_amount' => ['nullable', 'numeric'],
        ];
    }

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        $supplier = null;

        if ($storeShortName = data_get($data, 'store_short_name')) {

            $data['store_id'] = Store::query()->where('short_name', trim($storeShortName))->value('id');
        }

        if ($athorPhone = data_get($data, 'author_phone')) {

            $data['author_user_id'] = User::query()->where('phone', trim($athorPhone))->value('id');
        }

        if ($supplierPhone = data_get($data, 'supplier_phone')) {

            $value = trim($supplierPhone);

            $supplier = Individual::query()->where('phone', $value)->first();

            if (! $supplier) {

                $supplier = Organization::query()->where('phone', $value)->first();
            }

            $data['supplier_id'] = $supplier?->id;

            $data['supplier_type'] = $supplier?->getMorphClass();
        }

        app(PurchaseService::class)->importRow($data);
    }
}

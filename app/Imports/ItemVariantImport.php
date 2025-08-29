<?php

namespace App\Imports;

use App\Models\Item;
use App\Services\ItemVariantService;
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

class ItemVariantImport extends StringValueBinder implements OnEachRow, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, WithCustomValueBinder, WithHeadingRow, WithStartRow, WithValidation
{
    use Importable, SkipsErrors, SkipsFailures;

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'attribute' => ['required', 'string'],
            'value' => ['required', 'string'],
            'cost' => ['required', 'numeric'],
            'price' => ['required', 'numeric'],
            'selling_price' => ['required', 'numeric'],
            'description' => ['required', 'string'],
        ];
    }

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        $item = Item::query()->where('sku', $data['sku'])->first();

        if ($item) {

            $data['item_id'] = $item->id;

            app(ItemVariantService::class)->importRow($data);
        }
    }
}

<?php

namespace App\Imports;

use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\Store;
use App\Services\StockLevelService;
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

class StockLevelImport extends StringValueBinder implements OnEachRow, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, WithCustomValueBinder, WithHeadingRow, WithStartRow, WithValidation
{
    use Importable, SkipsErrors, SkipsFailures;

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            'store_short_name' => ['required', 'string', 'exists:stores,short_name'],
            'sku' => ['required', 'string'],
            'quantity' => ['required'],
        ];
    }

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        if ($storeShortName = $data['store_short_name']) {

            $data['store_id'] = Store::where('short_name', $storeShortName)->value('id');
        }

        if ($sku = data_get($data, 'sku')) {

            $stockable = Item::where('sku', $sku)->first();

            if (! $stockable) {

                $stockable = ItemVariant::where('sku', $sku)->first();
            }

            $data['stockable_id'] = $stockable?->id;

            $data['stockable_type'] = $stockable?->getMorphClass();
        }

        app(StockLevelService::class)->importRow($data);
    }
}

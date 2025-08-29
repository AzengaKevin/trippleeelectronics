<?php

namespace App\Imports;

use App\Models\Enums\StockMovementType;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\Store;
use App\Services\StockMovementService;
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

class StockMovementImport extends StringValueBinder implements OnEachRow, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, WithCustomValueBinder, WithHeadingRow, WithStartRow, WithValidation
{
    use Importable, SkipsErrors, SkipsFailures;

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            'product_sku' => ['required', 'string'],
            'store_short_name' => ['required', 'string'],
            'store_name' => ['required', 'string'],
            'product_name' => ['required', 'string'],
            'type' => ['required', Rule::in(StockMovementType::options())],
            'quantity' => ['required', 'numeric'],
        ];
    }

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        $store = Store::query()->where('short_name', $data['store_short_name'])->orWhere('name', $data['store_name'])->firstOrFail();

        $data['store_id'] = $store?->id;

        $product = Item::where('sku', $data['product_sku'])->orWhere('name', $data['product_name'])->first() ?? ItemVariant::where('sku', $data['product_sku'])->orWhere('name', $data['product_name'])->first();

        if (! $product) {
            throw new \Exception("Product with SKU or Name '{$data['product_sku']}' not found.");
        }

        app(StockMovementService::class)->importRow([
            'store_id' => $data['store_id'],
            'stockable_type' => $product->getMorphClass(),
            'stockable_id' => $product->id,
            'type' => StockMovementType::from($data['type']),
            'quantity' => $data['quantity'],
        ]);
    }
}

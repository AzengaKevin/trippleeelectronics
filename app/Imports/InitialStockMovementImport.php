<?php

namespace App\Imports;

use App\Models\Enums\StockMovementType;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\StockMovement;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
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

class InitialStockMovementImport extends StringValueBinder implements OnEachRow, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, WithCustomValueBinder, WithHeadingRow, WithStartRow, WithValidation
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
            'store_short_name' => ['required', 'string', Rule::exists('stores', 'short_name')],
            'product_name' => ['required', 'string'],
            'store_name' => ['required', 'string', Rule::exists('stores', 'name')],
            'store_name' => ['required', 'string', Rule::exists('stores', 'name')],
            'initial_stock' => ['required', 'numeric'],
            'cost' => ['nullable', 'numeric'],
            'retail_price' => ['nullable', 'numeric'],
            'advertised_price' => ['nullable', 'numeric'],
        ];
    }

    public function onRow(Row $row)
    {

        $data = $row->toArray();

        $store = Store::query()->where('short_name', $data['store_short_name'])->orWhere('name', $data['store_name'])->firstOrFail();

        $product = Item::where('sku', $data['product_sku'])->orWhere('name', $data['product_name'])->first() ?? ItemVariant::where('sku', $data['product_sku'])->orWhere('name', $data['product_name'])->first();

        if (! $product) {
            throw new \Exception("Product with SKU or Name '{$data['product_sku']}' not found.");
        }

        DB::transaction(function () use ($data, $store, $product) {

            StockMovement::query()->updateOrCreate(
                [
                    'store_id' => $store->id,
                    'stockable_type' => $product->getMorphClass(),
                    'stockable_id' => $product->id,
                    'type' => StockMovementType::INITIAL->value,
                ],
                [
                    'quantity' => intval($data['initial_stock']),
                    'description' => 'Initial stock import',
                    'cost_implication' => 0,
                ]
            );

            $values = [
                'cost' => data_get($data, 'cost', $product->cost),
                'price' => data_get($data, 'retail_price', $product->price),
                'selling_price' => data_get($data, 'advertised_price', $product->selling_price),
            ];

            $product->fill($values);

            if ($product->isDirty(['cost', 'price', 'selling_price'])) {

                $product->save();
            }
        });
    }
}

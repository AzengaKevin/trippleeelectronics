<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\ItemCategory;
use App\Services\ItemService;
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

class ItemImport extends StringValueBinder implements OnEachRow, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, WithCustomValueBinder, WithHeadingRow, WithStartRow, WithValidation
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
            'category' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'cost' => ['nullable', 'numeric'],
            'price' => ['nullable', 'numeric'],
            'selling_price' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        if ($categoryName = data_get($data, 'category')) {

            $category = ItemCategory::where('name', $categoryName)->first();

            $data['item_category_id'] = $category?->id;
        }

        if ($brandName = data_get($data, 'brand')) {

            $brand = Brand::where('name', $brandName)->first();

            if (! $brand) {
                $brand = Brand::create(['name' => $brandName]);
            }

            $data['brand_id'] = $brand->id;
        }

        app(ItemService::class)->importRow($data);
    }
}

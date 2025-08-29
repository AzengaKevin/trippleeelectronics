<?php

namespace App\Exports;

use App\Services\ItemService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class ItemExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $data = []) {}

    public function headings(): array
    {
        return [
            'sku',
            'name',
            'pos_name',
            'category',
            'brand',
            'description',
            'pos_description',
            'cost',
            'price',
            'selling_price',
        ];
    }

    public function collection()
    {
        $items = app(ItemService::class)->get(...$this->data, perPage: null);

        return collect($items)->map(function ($item) {
            return [
                'sku' => $item->sku,
                'name' => $item->name,
                'pos_name' => $item->pos_name,
                'category' => $item->category?->name,
                'brand' => $item->brand?->name,
                'description' => $item->description,
                'pos_description' => $item->pos_description,
                'cost' => $item->cost,
                'price' => $item->price,
                'selling_price' => $item->selling_price,
            ];
        });
    }
}

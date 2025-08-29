<?php

namespace App\Exports;

use App\Services\ItemVariantService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class ItemVariantExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $data = []) {}

    public function headings(): array
    {
        return [
            'item',
            'sku',
            'name',
            'slug',
            'attribute',
            'value',
            'cost',
            'price',
            'description',
        ];
    }

    public function collection()
    {
        $itemVariants = app(ItemVariantService::class)->get(...$this->data, perPage: null);

        return collect($itemVariants)->map(function ($variant) {
            return [
                'item' => $variant->item?->name,
                'sku' => $variant->sku,
                'name' => $variant->name,
                'slug' => $variant->slug,
                'attribute' => $variant->attribute,
                'value' => $variant->value,
                'cost' => $variant->cost,
                'price' => $variant->price,
                'description' => $variant->description,
            ];
        });
    }
}

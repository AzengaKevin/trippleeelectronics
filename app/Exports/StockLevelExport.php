<?php

namespace App\Exports;

use App\Services\StockLevelService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class StockLevelExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $filters = []) {}

    public function headings(): array
    {
        return [
            'reference',
            'store',
            'stockable_type',
            'stockable',
            'quantity',
        ];
    }

    public function collection()
    {

        $stockLevels = app(StockLevelService::class)->get(...$this->filters, perPage: null);

        return $stockLevels->map(function ($stock) {
            return [
                'reference' => $stock->id,
                'store' => $stock->store?->name,
                'stockable_type' => $stock->stockable_type->value,
                'stockable' => $stock->stockable?->sku,
                'quantity' => $stock->quantity,
            ];
        });
    }
}

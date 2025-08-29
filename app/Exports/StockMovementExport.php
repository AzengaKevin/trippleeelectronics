<?php

namespace App\Exports;

use App\Services\StockMovementService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class StockMovementExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $filters = []) {}

    public function headings(): array
    {
        return [
            'product_sku',
            'store_short_name',
            'store_name',
            'product_name',
            'type',
            'quantity',
        ];
    }

    public function collection()
    {
        $stockMovements = app(StockMovementService::class)->get(...$this->filters, with: ['store', 'stockable'], perPage: null);

        return $stockMovements->map(function ($movement) {
            return [
                'product_sku' => $movement->stockable?->sku,
                'store_short_name' => $movement->store?->short_name,
                'store_name' => $movement->store?->name,
                'product_name' => $movement->stockable?->name,
                'type' => $movement->type?->value,
                'quantity' => $movement->quantity,
            ];
        });
    }
}

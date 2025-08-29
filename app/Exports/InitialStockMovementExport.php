<?php

namespace App\Exports;

use App\Services\StockMovementService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class InitialStockMovementExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
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
            'initial_stock',
            'cost',
            'retail_price',
            'advertised_price',
        ];
    }

    public function collection()
    {
        $items = app(StockMovementService::class)->getInitialItemsStock(...$this->filters, perPage: null);

        return $items->map(function ($item) {
            return [
                'product_sku' => $item->sku,
                'store_short_name' => $item->store_short_name,
                'store_name' => $item->store_name,
                'product_name' => $item->name,
                'initial_stock' => $item->quantity,
                'cost' => $item->cost,
                'retail_price' => $item->price,
                'advertised_price' => $item->selling_price,
            ];
        });
    }
}

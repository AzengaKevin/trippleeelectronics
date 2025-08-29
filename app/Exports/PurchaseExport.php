<?php

namespace App\Exports;

use App\Services\PurchaseService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class PurchaseExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $filters = []) {}

    public function headings(): array
    {
        return [
            'reference',
            'store_short_name',
            'supplier_phone',
            'author_phone',
            'amount',
            'shipping_amount',
            'total_amount',
        ];
    }

    public function collection()
    {
        $purchases = app(PurchaseService::class)->get(...$this->filters, with: ['author', 'supplier'], perPage: null);

        return $purchases->map(function ($purchase) {
            return [
                'reference' => $purchase->reference,
                'store_short_name' => $purchase->store?->short_name,
                'supplier_phone' => $purchase->supplier?->phone,
                'author_phone' => $purchase->author?->phone,
                'amount' => $purchase->amount,
                'shipping_amount' => $purchase->shipping_amount,
                'total_amount' => $purchase->total_amount,
            ];
        });
    }
}

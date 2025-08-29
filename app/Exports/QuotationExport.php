<?php

namespace App\Exports;

use App\Services\QuotationService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class QuotationExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $filters = []) {}

    public function headings(): array
    {
        return [
            'reference',
            'customer_phone',
            'author_phone',
            'amount',
            'shipping_amount',
            'tax_amount',
            'discount_amount',
            'total_amount',
        ];
    }

    public function collection()
    {
        $quotations = app(QuotationService::class)->get(...$this->filters, with: ['customer', 'author'], perPage: null);

        return $quotations->map(function ($quotation) {
            return [
                'reference' => $quotation->reference,
                'customer_phone' => $quotation->customer?->phone ?? null,
                'author_phone' => $quotation->author?->phone ?? null,
                'amount' => $quotation->amount,
                'shipping_amount' => $quotation->shipping_amount,
                'tax_amount' => $quotation->tax_amount,
                'discount_amount' => $quotation->discount_amount,
                'total_amount' => $quotation->total_amount,
            ];
        });
    }
}

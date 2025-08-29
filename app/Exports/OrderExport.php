<?php

namespace App\Exports;

use App\Services\OrderService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class OrderExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $filters = []) {}

    public function headings(): array
    {
        return [
            'reference',
            'customer_type',
            'customer_reference',
            'amount',
            'shipping_amount',
            'tax_amount',
            'discout_amount',
            'total_amount',
            'tendered_amount',
            'balance_amount',
            'status',
        ];
    }

    public function collection()
    {
        $orders = app(OrderService::class)->get(...$this->filters, perPage: null);

        return $orders->map(function ($order) {
            return [
                'reference' => $order->reference,
                'customer_type' => $order->customer?->getMorphClass(),
                'customer_reference' => $order->customer?->id,
                'amount' => $order->amount,
                'shipping_amount' => $order->shipping_amount,
                'tax_amount' => $order->tax_amount,
                'discount_amount' => $order->discount_amount,
                'total_amount' => $order->total_amount,
                'tendered_amount' => $order->tendered_amount,
                'balance_amount' => $order->balance_amount,
                'status' => $order->status?->value,
            ];
        });
    }
}

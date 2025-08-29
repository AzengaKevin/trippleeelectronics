<?php

namespace App\Exports;

use App\Services\PaymentService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class PaymentExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $data = []) {}

    public function headings(): array
    {
        return [
            'author',
            'payable',
            'payer',
            'payee',
            'amount',
            'payment_method',
            'status',
        ];
    }

    public function collection()
    {

        $results = app(PaymentService::class)->get(...$this->data, with: ['author', 'payable', 'payer', 'payee'], perPage: null);

        return $results->map(function ($payment) {
            return [
                'author' => $payment->author?->name,
                'payable' => $payment->payable?->reference,
                'payer' => $payment->payer?->phone,
                'payee' => $payment->payee?->phone,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method?->value,
                'status' => $payment->status?->value,
            ];
        });
    }
}

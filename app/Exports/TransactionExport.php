<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Services\TransactionService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class TransactionExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $data = []) {}

    public function headings(): array
    {
        return [
            'author',
            'payment',
            'amount',
            'transaction_method',
            'status',
            'till',
            'paybill_number',
            'account_number',
            'phone_number',
            'reference',
            'local_reference',
            'fee',
        ];
    }

    public function collection()
    {
        $results = app(TransactionService::class)->get(...$this->data, with: ['author', 'payment'], perPage: null);

        return $results->map(function (Transaction $transaction) {
            return [
                'author' => $transaction->author?->name,
                'payment' => $transaction->payment?->reference,
                'amount' => $transaction->amount,
                'transaction_method' => $transaction->transaction_method?->value,
                'status' => $transaction->status?->value,
                'till' => $transaction->till,
                'paybill_number' => $transaction->paybill_number,
                'account_number' => $transaction->account_number,
                'phone_number' => $transaction->phone_number,
                'reference' => $transaction->reference,
                'local_reference' => $transaction->local_reference,
                'fee' => $transaction->fee,
            ];
        });
    }
}

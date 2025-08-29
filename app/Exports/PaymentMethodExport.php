<?php

namespace App\Exports;

use App\Services\PaymentMethodService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class PaymentMethodExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $data = []) {}

    public function headings(): array
    {
        return [
            'name',
            'description',
        ];
    }

    public function collection()
    {

        $results = app(PaymentMethodService::class)->get(...$this->data, perPage: null);

        return $results->map(function ($paymentMethod) {
            return [
                'name' => $paymentMethod->name,
                'description' => $paymentMethod->description,
            ];
        });
    }
}

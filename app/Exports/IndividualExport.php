<?php

namespace App\Exports;

use App\Services\IndividualService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class IndividualExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $data = []) {}

    public function headings(): array
    {
        return [
            'reference',
            'organization',
            'name',
            'username',
            'email',
            'phone',
            'address',
            'kra_pin',
            'id_number',
        ];
    }

    public function collection()
    {
        $individuals = app(IndividualService::class)->get(...$this->data, perPage: null);

        return collect($individuals)->map(function ($individual) {
            return [
                'reference' => $individual->id,
                'organization' => $individual->organization?->name,
                'name' => $individual->name,
                'username' => $individual->username,
                'email' => $individual->email,
                'phone' => $individual->phone,
                'address' => $individual->address,
                'kra_pin' => $individual->kra_pin,
                'id_number' => $individual->id_number,
            ];
        });
    }
}

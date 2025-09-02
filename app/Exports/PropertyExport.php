<?php

namespace App\Exports;

use App\Services\PropertyService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class PropertyExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private readonly array $data = []) {}

    public function headings(): array
    {
        return [
            'name',
            'code',
            'address',
            'active',
        ];
    }

    public function collection()
    {

        $results = app(PropertyService::class)->get(...$this->data);

        return $results->map(fn ($item) => $item->only('name', 'code', 'address', 'active'));
    }
}

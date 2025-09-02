<?php

namespace App\Exports;

use App\Services\BuildingService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class BuildingExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private readonly array $data = []) {}

    public function headings(): array
    {
        return [
            'property_code',
            'name',
            'code',
            'active',
        ];
    }

    public function collection()
    {
        $results = app(BuildingService::class)->get(...$this->data, with: ['property']);

        return $results->map(fn ($item) => [
            'property_code' => $item->property?->code,
            'name' => $item->name,
            'code' => $item->code,
            'active' => $item->active,
        ]);
    }
}

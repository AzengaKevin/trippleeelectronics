<?php

namespace App\Exports;

use App\Models\Service;
use App\Services\ServiceService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class ServiceExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private $data = []) {}

    public function headings(): array
    {
        return [
            'title',
            'description',
        ];
    }

    public function collection()
    {
        $services = app(ServiceService::class)->get(...$this->data, perPage: null);

        return $services->map(fn (Service $service) => $service->only('title', 'description'));
    }
}

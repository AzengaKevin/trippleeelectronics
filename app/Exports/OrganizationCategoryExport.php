<?php

namespace App\Exports;

use App\Services\OrganizationCategoryService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class OrganizationCategoryExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $data = []) {}

    public function headings(): array
    {
        return [
            'name',
        ];
    }

    public function collection()
    {
        $organizationCategories = app(OrganizationCategoryService::class)->get(...$this->data, perPage: null);

        return collect($organizationCategories)->map(function ($organizationCategory) {
            return [
                'name' => $organizationCategory->name,
            ];
        });
    }
}

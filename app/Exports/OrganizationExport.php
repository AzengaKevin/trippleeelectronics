<?php

namespace App\Exports;

use App\Services\OrganizationService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class OrganizationExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $data = []) {}

    public function headings(): array
    {
        return [
            'category',
            'name',
            'email',
            'phone',
            'address',
            'kra_pin',
        ];
    }

    public function collection()
    {
        $organizations = app(OrganizationService::class)->get(...$this->data, with: ['organizationCategory'], perPage: null);

        return collect($organizations)->map(function ($organization) {
            return [
                'category' => $organization->category?->name,
                'name' => $organization->name,
                'email' => $organization->email,
                'phone' => $organization->phone,
                'address' => $organization->address,
                'kra_pin' => $organization->kra_pin,
            ];
        });
    }
}

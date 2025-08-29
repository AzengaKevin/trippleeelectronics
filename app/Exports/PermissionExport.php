<?php

namespace App\Exports;

use App\Services\PermissionService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class PermissionExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $filters = []) {}

    public function headings(): array
    {
        return [
            'name',
            'guard_name',
        ];
    }

    public function collection()
    {
        $permissions = app(PermissionService::class)->get(...$this->filters, perPage: null);

        return $permissions->map(function ($permission) {
            return [
                'name' => $permission->name,
                'guard_name' => $permission->guard_name,
            ];
        });
    }
}

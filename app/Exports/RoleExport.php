<?php

namespace App\Exports;

use App\Services\RoleService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class RoleExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $filters = []) {}

    public function headings(): array
    {
        return [
            'name',
            'permissions',
        ];
    }

    public function collection()
    {
        $roles = app(RoleService::class)->get(...$this->filters, perPage: null, with: ['permissions']);

        return $roles->map(function ($role) {
            return [
                'name' => $role->name,
                'permissions' => implode('|', $role->permissions->pluck('name')->all()),
            ];
        });
    }
}

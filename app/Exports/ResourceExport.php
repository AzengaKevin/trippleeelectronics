<?php

namespace App\Exports;

use App\Services\ResourceService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class ResourceExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $data = []) {}

    public function headings(): array
    {
        return [
            'name',
            'route_name',
            'icon',
            'order',
            'description',
            'is_active',
            'count',
            'required_permission',
            'morph_class',
        ];
    }

    public function collection()
    {
        $resources = app(ResourceService::class)->get(...$this->data, perPage: null);

        return collect($resources)->map(function ($resource) {
            return [
                'name' => $resource->name,
                'route_name' => $resource->route_name,
                'icon' => $resource->icon,
                'order' => $resource->order,
                'description' => $resource->description,
                'is_active' => $resource->is_active,
                'count' => $resource->count,
                'required_permission' => $resource->required_permission,
                'morph_class' => $resource->morph_class,
            ];
        });
    }
}

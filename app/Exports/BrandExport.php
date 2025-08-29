<?php

namespace App\Exports;

use App\Services\BrandService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class BrandExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $data = []) {}

    public function headings(): array
    {
        return [
            'reference',
            'name',
            'slug',
            'description',
            'items_count_manual',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }

    public function collection()
    {

        $brands = app(BrandService::class)->get(...$this->data, perPage: null);

        return collect($brands)->map(function ($brand) {
            return [
                'reference' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'description' => $brand->description,
                'items_count_manual' => $brand->items_count_manual,
                'created_at' => $brand->created_at,
                'updated_at' => $brand->updated_at,
                'deleted_at' => $brand->deleted_at,
            ];
        });
    }
}

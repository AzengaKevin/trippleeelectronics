<?php

namespace App\Exports;

use App\Services\ItemCategoryService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class ItemCategoryExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $data = []) {}

    public function headings(): array
    {
        return [
            'parent_name',
            'name',
            'description',
        ];
    }

    public function collection()
    {

        $categories = app(ItemCategoryService::class)->get(...$this->data, with: ['parent'], perPage: null);

        return $categories->map(fn ($itemCategory) => [
            'parent_name' => $itemCategory->parent?->name,
            'name' => $itemCategory->name,
            'description' => $itemCategory->description,
        ]);

    }
}

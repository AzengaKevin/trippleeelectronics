<?php

namespace App\Exports;

use App\Services\CarouselService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class CarouselExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $data = []) {}

    public function headings(): array
    {
        return [
            'orientation',
            'position',
            'link',
            'title',
            'description',
            'active',
        ];
    }

    public function collection()
    {
        $carousels = app(CarouselService::class)->get(...$this->data, perPage: null);

        return $carousels->map(fn ($carousel) => [
            'orientation' => $carousel->orientation?->value,
            'position' => $carousel->position?->value,
            'link' => $carousel->link,
            'title' => $carousel->title,
            'description' => $carousel->description,
            'active' => $carousel->active,
        ]);
    }
}

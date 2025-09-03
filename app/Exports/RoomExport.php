<?php

namespace App\Exports;

use App\Services\RoomService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class RoomExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private readonly array $data = []) {}

    public function headings(): array
    {
        return [
            'building_code',
            'room_type_code',
            'name',
            'code',
            'occupancy',
            'active',
            'price',
            'status',
        ];
    }

    public function collection()
    {
        $results = app(RoomService::class)->get(...$this->data, perPage: null);

        return $results->map(fn ($item) => [
            'building_code' => $item->building?->code,
            'room_type_code' => $item->roomType?->code,
            'name' => $item->name,
            'code' => $item->code,
            'occupancy' => $item->occupancy,
            'active' => $item->active,
            'price' => $item->price,
            'status' => $item->status,
        ]);
    }
}

<?php

namespace App\Exports;

use App\Services\StoreService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class StoreExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $data = []) {}

    public function headings(): array
    {
        return [
            'reference',
            'name',
            'address',
            'location',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }

    public function collection()
    {
        $stores = app(StoreService::class)->get(...$this->data, perPage: null);

        return collect($stores)->map(function ($store) {
            return [
                'reference' => $store->id,
                'name' => $store->name,
                'address' => $store->address,
                'location' => $store->location,
                'created_at' => $store->created_at,
                'updated_at' => $store->updated_at,
                'deleted_at' => $store->deleted_at,
            ];
        });
    }
}

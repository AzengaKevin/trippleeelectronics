<?php

namespace App\Imports;

use App\Models\Building;
use App\Models\RoomType;
use App\Services\RoomService;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class RoomImport extends StringValueBinder implements OnEachRow, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, WithCustomValueBinder, WithHeadingRow, WithStartRow, WithValidation
{
    use Importable, SkipsErrors, SkipsFailures;

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            'building' => ['nullable', Rule::exists(Building::class, 'code')],
            'room_type' => ['nullable', Rule::exists(RoomType::class, 'code')],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'occupancy' => ['nullable', 'integer', 'min:1'],
            'active' => ['nullable', 'boolean'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:255'],
        ];

    }

    public function onRow(Row $row): void
    {
        $data = $row->toArray();

        app(RoomService::class)->importRow($data);
    }
}

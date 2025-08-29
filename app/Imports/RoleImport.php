<?php

namespace App\Imports;

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
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleImport extends StringValueBinder implements OnEachRow, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, WithCustomValueBinder, WithHeadingRow, WithStartRow, WithValidation
{
    use Importable, SkipsErrors, SkipsFailures;

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'permissions' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        $name = data_get($data, 'name');

        $role = Role::query()->updateOrCreate(compact('name'));

        if ($permissions = data_get($data, 'permissions')) {

            $permissionNames = collect(explode($permissions, '|'))->map(fn ($item) => trim($item))->all();

            $role->permissions()->sync(Permission::query()->whereIn('name', $permissionNames)->pluck('id')->all());
        }
    }
}

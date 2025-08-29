<?php

namespace App\Exports;

use App\Services\UserService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class UserExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use Exportable;

    public function __construct(private array $data = []) {}

    public function headings(): array
    {
        return [
            'reference',
            'name',
            'email',
            'phone',
            'email_verified_at',
            'phone_verified_at',
            'password',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }

    public function collection()
    {
        $users = app(UserService::class)->get(...$this->data, perPage: null);

        return $users->map(function ($user) {
            return [
                'reference' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'email_verified_at' => $user->email_verified_at,
                'phone_verified_at' => $user->phone_verified_at,
                'password' => $user->password,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'deleted_at' => $user->deleted_at,
            ];
        });

    }
}

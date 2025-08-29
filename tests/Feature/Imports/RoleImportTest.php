<?php

namespace Tests\Feature\Imports;

use App\Imports\RoleImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class RoleImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_import_roles()
    {

        $titlesRow = [
            'name',
            'permissions',
        ];

        $data = [
            [
                'name' => 'admin',
                'permissions' => 'create|edit|delete|view',
            ],
            [
                'name' => 'editor',
                'permissions' => 'edit|view',
            ],
            [
                'name' => 'viewer',
                'permissions' => 'view',
            ],
        ];

        $filename = str('roles')->append('-')->append(now()->format('Y-m-d-H-i-s'))->append('.csv');

        $filePath = $this->createTestCsvFile($filename, [
            $titlesRow,
            ...$data,
        ]);

        $this->assertFileExists($filePath);

        try {

            $import = new RoleImport;

            $import->import($filePath);
        } finally {

            collect($data)->each(function ($row) {
                $this->assertDatabaseHas('roles', [
                    'name' => data_get($row, 'name'),
                ]);
            });

            unlink($filePath);
        }
    }
}

<?php

namespace Tests\Feature\Imports;

use App\Imports\PermissionImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class PermissionImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_permission_import_logic(): void
    {
        $titlesRow = [
            'name',
        ];

        $data = [
            [
                'name' => 'view_users',
            ],
            [
                'name' => 'edit_users',
            ],
        ];

        $filename = 'permissions'.now()->timestamp.'.csv';

        $filePath = $this->createTestCsvFile($filename, [
            $titlesRow,
            ...$data,
        ]);

        $this->assertFileExists($filePath);

        try {
            $import = new PermissionImport;

            $import->import($filePath);

            collect($data)->each(function ($row) {
                $this->assertDatabaseHas('permissions', [
                    'name' => $row['name'],
                ]);
            });
        } catch (\Exception $e) {
            $this->fail('Permission import failed: '.$e->getMessage());
        }
    }
}

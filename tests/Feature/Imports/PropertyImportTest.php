<?php

namespace Tests\Feature\Imports;

use App\Imports\PropertyImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class PropertyImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_property_import_logic(): void
    {

        $titlesRow = [
            'name',
            'code',
            'address',
            'active',
        ];

        $data = [
            [
                'name' => 'Property 1',
                'code' => 'CODE-0001',
                'address' => 'Address 1',
                'active' => true,
            ],
            [
                'name' => 'Property 2',
                'code' => 'CODE-0002',
                'address' => 'Address 2',
                'active' => false,
            ],
        ];

        $filename = str('resources')->append('-')->append(now()->format('Y-m-d'))->append('.csv')->value();

        $filePath = $this->createTestCsvFile($filename, [
            $titlesRow,
            ...$data,
        ]);

        $this->assertFileExists($filePath);

        try {

            $propertyImport = new PropertyImport;

            $propertyImport->import($filePath);

            $this->assertDatabaseCount('properties', count: count($data));

        } catch (\Throwable $throwable) {

            collect($data)->each(function ($item) {
                $this->assertDatabaseHas('resources', [
                    'name' => data_get($item, 'name'),
                    'code' => data_get($item, 'code'),
                    'address' => data_get($item, 'address'),
                    'active' => data_get($item, 'active'),
                ]);
            });

            unlink($filePath);
        }
    }
}

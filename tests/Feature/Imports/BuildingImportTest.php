<?php

namespace Tests\Feature\Imports;

use App\Imports\BuildingImport;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class BuildingImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_import_a_building(): void
    {

        $property = Property::factory()->create(['code' => 'CODE-0001']);

        $titlesRow = [
            'property_code',
            'name',
            'code',
            'active',
        ];

        $data = [
            [
                'property_code' => $property->code,
                'name' => 'Building 1',
                'code' => 'B-0001',
                'active' => true,
            ],
            [
                'property_code' => $property->code,
                'name' => 'Building 2',
                'code' => 'B-0002',
                'active' => false,
            ],
        ];

        $filename = str('buildings')->append('-')->append(now()->format('Y-m-d'))->append('.csv')->value();

        $filePath = $this->createTestCsvFile($filename, [
            $titlesRow,
            ...$data,
        ]);

        $this->assertFileExists($filePath);

        try {

            $buildingImport = new BuildingImport;

            $buildingImport->import($filePath);

            $this->assertDatabaseCount('buildings', count: count($data));

        } catch (\Throwable $throwable) {

            collect($data)->each(function ($item) {
                $this->assertDatabaseHas('buildings', [
                    'name' => data_get($item, 'name'),
                    'code' => data_get($item, 'code'),
                    'active' => data_get($item, 'active') ? 1 : 0,
                ]);
            });

            throw $throwable;
        }
    }
}

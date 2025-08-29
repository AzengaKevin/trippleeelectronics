<?php

namespace Tests\Feature\Imports;

use App\Imports\OrganizationCategoryImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class OrganizationCategoryImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_organization_category_import_logic(): void
    {
        $titlesRow = [
            'name',
        ];

        $data = [
            [
                'name' => $this->faker->sentence(),
            ],
            [
                'name' => $this->faker->sentence(),
            ],
        ];

        $filename = 'organization_categories'.now()->timestamp.'.csv';

        $filePath = $this->createTestCsvFile($filename, [
            $titlesRow,
            ...$data,
        ]);

        $this->assertFileExists($filePath);

        try {
            $import = new OrganizationCategoryImport;

            $import->import($filePath);

            foreach ($data as $row) {
                $this->assertDatabaseHas('organization_categories', [
                    'name' => $row['name'],
                ]);
            }
        } finally {
            unlink($filePath);
        }
    }
}

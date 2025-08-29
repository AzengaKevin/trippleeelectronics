<?php

namespace Tests\Feature\Imports;

use App\Imports\IndividualImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class IndividualImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_individual_import_logic(): void
    {
        $titlesRow = [
            'name',
            'email',
            'phone',
            'address',
        ];

        $data = [
            [
                'name' => $this->faker->name(),
                'email' => $this->faker->unique()->safeEmail(),
                'phone' => $this->faker->phoneNumber(),
                'address' => $this->faker->address(),
            ],
            [
                'name' => $this->faker->name(),
                'email' => $this->faker->unique()->safeEmail(),
                'phone' => $this->faker->phoneNumber(),
                'address' => $this->faker->address(),
            ],
        ];

        $filename = 'individuals'.now()->timestamp.'.csv';

        $filePath = $this->createTestCsvFile($filename, [
            $titlesRow,
            ...$data,
        ]);

        $this->assertFileExists($filePath);

        try {

            $import = new IndividualImport;

            $import->import($filePath);

            foreach ($data as $row) {
                $this->assertDatabaseHas('individuals', [
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'phone' => $row['phone'],
                    'address' => $row['address'],
                ]);
            }

        } catch (\Exception $e) {
            $this->fail("Import failed: {$e->getMessage()}");
        } finally {
            $this->deleteTestCsvFile($filename);
        }
    }
}

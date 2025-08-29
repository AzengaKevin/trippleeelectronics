<?php

namespace Tests\Feature\Imports;

use App\Imports\OrganizationImport;
use App\Models\OrganizationCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class OrganizationImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_organization_import_logic(): void
    {

        $category = OrganizationCategory::factory()->create();

        $titlesRow = [
            'category_name',
            'name',
            'email',
            'phone',
            'address',
            'kra_pin',
        ];

        $data = [
            [
                'category_name' => $category->name,
                'name' => $this->faker->company(),
                'email' => $this->faker->unique()->safeEmail(),
                'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
                'address' => $this->faker->address(),
                'kra_pin' => str('P')->append($this->faker->unique()->numerify('#########'))->append($this->faker->randomLetter())->upper()->value(),
            ],
            [
                'category_name' => $category->name,
                'name' => $this->faker->company(),
                'email' => $this->faker->unique()->safeEmail(),
                'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
                'address' => $this->faker->address(),
                'kra_pin' => str('P')->append($this->faker->unique()->numerify('#########'))->append($this->faker->randomLetter())->upper()->value(),
            ],
        ];

        $filename = 'organizations'.now()->timestamp.'.csv';

        $filePath = $this->createTestCsvFile($filename, [
            $titlesRow,
            ...$data,
        ]);

        $this->assertFileExists($filePath);

        try {
            $import = new OrganizationImport;

            $import->import($filePath);

            foreach ($data as $row) {
                $this->assertDatabaseHas('organizations', [
                    'name' => data_get($row, 'name'),
                    'email' => data_get($row, 'email'),
                    'phone' => data_get($row, 'phone'),
                    'address' => data_get($row, 'address'),
                    'kra_pin' => data_get($row, 'kra_pin'),
                    'organization_category_id' => $category->id,
                ]);
            }
        } finally {
            unlink($filePath);
        }
    }
}

<?php

namespace Tests\Feature\Imports;

use App\Imports\CarouselImport;
use App\Models\Enums\OrientationOption;
use App\Models\Enums\PositionOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class CarouselImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_carousel_import_logic(): void
    {

        $titlesRow = [
            'title',
            'orientation',
            'position',
            'link',
            'description',
            'active',
        ];

        $data = [
            [
                'title' => $this->faker->sentence(),
                'orientation' => $this->faker->randomElement(OrientationOption::options()),
                'position' => $this->faker->randomElement(PositionOption::options()),
                'link' => $this->faker->url(),
                'description' => $this->faker->sentence(),
                'active' => $this->faker->boolean(),
            ],
            [
                'title' => $this->faker->sentence(),
                'orientation' => $this->faker->randomElement(OrientationOption::options()),
                'position' => $this->faker->randomElement(PositionOption::options()),
                'link' => $this->faker->url(),
                'description' => $this->faker->sentence(),
                'active' => $this->faker->boolean(),
            ],
        ];

        $filename = 'carousels'.now()->timestamp.'.csv';

        $filePath = $this->createTestCsvFile($filename, [
            $titlesRow,
            ...$data,
        ]);

        $this->assertFileExists($filePath);

        try {

            $import = new CarouselImport;

            $import->import($filePath);

            $this->assertDatabaseCount('carousels', count($data));
        } finally {
            unlink($filePath);
        }
    }
}

<?php

namespace Tests\Feature\Imports;

use App\Imports\ServiceImport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class ServiceImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_services_import(): void
    {

        $user = User::factory()->create();

        $titlesRow = [
            'author_phone',
            'title',
            'description',
        ];

        $data = [
            [
                'author_phone' => $user->phone,
                'title' => $this->faker->sentence(),
                'description' => $this->faker->sentence(),
            ],
            [
                'author_phone' => $user->phone,
                'title' => $this->faker->sentence(),
                'description' => $this->faker->sentence(),
            ],
        ];

        $filename = str('services')->append('-')->append(now()->toDateString())->append('.csv')->value();

        $filePath = $this->createTestCsvFile($filename, [
            $titlesRow,
            ...$data,
        ]);

        $this->assertFileExists($filePath);

        try {

            $import = new ServiceImport;

            $import->import($filePath);

        } finally {

            collect($data)->each(function ($row) use ($user) {
                $this->assertDatabaseHas('services', [
                    'title' => data_get($row, 'title'),
                    'description' => data_get($row, 'description'),
                    'author_user_id' => $user->id,
                ]);
            });

            unlink($filePath);
        }
    }
}

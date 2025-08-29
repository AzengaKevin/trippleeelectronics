<?php

namespace Tests\Feature\Imports;

use App\Imports\StoreImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class StoreImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_store_import()
    {
        $titlesRow = [
            'name',
            'short_name',
            'phone',
            'paybill',
            'account_number',
            'till',
            'kra_pin',
            'address',
            'location',
        ];

        $data = [
            [
                'name' => $this->faker->name(),
                'short_name' => str($this->faker->lexify('???'))->upper()->value(),
                'phone' => $this->faker->phoneNumber(),
                'paybill' => $this->faker->numerify('######'),
                'account_number' => $this->faker->numerify('ACC-######'),
                'till' => $this->faker->numerify('######'),
                'kra_pin' => $this->faker->numerify('KRA-######'),
                'address' => $this->faker->streetAddress(),
                'location' => $this->faker->city(),
            ],
            [
                'name' => $this->faker->name(),
                'short_name' => str($this->faker->lexify('???'))->upper()->value(),
                'phone' => $this->faker->phoneNumber(),
                'paybill' => $this->faker->numerify('######'),
                'account_number' => $this->faker->numerify('ACC-######'),
                'till' => $this->faker->numerify('######'),
                'kra_pin' => $this->faker->numerify('KRA-######'),
                'address' => $this->faker->streetAddress(),
                'location' => $this->faker->city(),
            ],
            [
                'name' => $this->faker->name(),
                'short_name' => str($this->faker->lexify('???'))->upper()->value(),
                'phone' => $this->faker->phoneNumber(),
                'paybill' => $this->faker->numerify('######'),
                'account_number' => $this->faker->numerify('ACC-######'),
                'till' => $this->faker->numerify('######'),
                'kra_pin' => $this->faker->numerify('KRA-######'),
                'address' => $this->faker->streetAddress(),
                'location' => $this->faker->city(),
            ],
        ];

        $filename = str('stores-')->append(now()->format('Y-m-d-H'))->append('.csv')->value();

        $filePath = $this->createTestCsvFile($filename, [
            $titlesRow,
            ...$data,
        ]);

        try {
            $import = new StoreImport;

            $import->import($filePath);

        } finally {

            collect($data)->each(function ($row) {
                $this->assertDatabaseHas('stores', [
                    'name' => $row['name'],
                    'short_name' => $row['short_name'],
                    'phone' => $row['phone'],
                    'paybill' => $row['paybill'],
                    'account_number' => $row['account_number'],
                    'till' => $row['till'],
                    'kra_pin' => $row['kra_pin'],
                    'address' => $row['address'],
                    'location' => $row['location'],
                ]);
            });

            unlink($filePath);
        }
    }
}

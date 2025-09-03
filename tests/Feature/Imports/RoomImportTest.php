<?php

namespace Tests\Feature\Imports;

use App\Imports\RoomImport;
use App\Models\Building;
use App\Models\Enums\RoomStatus;
use App\Models\Property;
use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class RoomImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_room_import_logic(): void
    {

        $building = Building::factory()->for(Property::factory())->create();

        $roomType = RoomType::factory()->create();

        $titlesRow = [
            'building_code',
            'room_type_code',
            'name',
            'code',
            'occupancy',
            'active',
            'price',
            'status',
        ];

        $data = [
            [
                'building_code' => $building->code,
                'room_type_code' => $roomType->code,
                'name' => $this->faker->sentence(),
                'code' => $this->faker->lexify('ROOM-???'),
                'occupancy' => $this->faker->numberBetween(1, 4),
                'active' => $this->faker->boolean(),
                'price' => $this->faker->randomFloat(2, 100, 1000),
                'status' => $this->faker->randomElement(RoomStatus::options()),
            ],
            [
                'building_code' => $building->code,
                'room_type_code' => $roomType->code,
                'name' => $this->faker->sentence(),
                'code' => $this->faker->lexify('ROOM-???'),
                'occupancy' => $this->faker->numberBetween(1, 4),
                'active' => $this->faker->boolean(),
                'price' => $this->faker->randomFloat(2, 100, 1000),
                'status' => $this->faker->randomElement(RoomStatus::options()),
            ],
        ];

        $filename = str('rooms')->append('-')->append(now()->format('Y-m-d'))->append('.csv')->value();

        $filePath = $this->createTestCsvFile($filename, [
            $titlesRow,
            ...$data,
        ]);

        $this->assertFileExists($filePath);

        try {

            $roomImport = new RoomImport;

            $roomImport->import($filePath);

            $this->assertDatabaseCount('rooms', count: count($data));

        } catch (\Throwable $throwable) {

            collect($data)->each(function ($item) use ($building, $roomType) {
                $this->assertDatabaseHas('rooms', [
                    'building_id' => $building->id,
                    'room_type_id' => $roomType->id,
                    'name' => data_get($item, 'name'),
                    'code' => data_get($item, 'code'),
                    'price' => data_get($item, 'price'),
                    'status' => data_get($item, 'status'),
                    'active' => data_get($item, 'active') ? 1 : 0,
                ]);
            });

            throw $throwable;
        }

    }
}

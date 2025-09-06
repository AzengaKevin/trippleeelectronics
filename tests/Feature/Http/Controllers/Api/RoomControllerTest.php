<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Building;
use App\Models\Property;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class RoomControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_api_rooms_index_route(): void
    {
        $property = Property::factory()->create();

        $building = Building::factory()->for($property, 'property')->create();

        Room::factory()->for($building, 'building')->count($roomsCount = 3)->create();

        $response = $this->getJson(route('api.rooms.index'));

        $response->assertStatus(200);

        $response->assertJson(
            fn (AssertableJson $json) => $json->has('data', $roomsCount, fn ($json) => $json->has('name')->has('price')->has('id')->has('occupancy')->has('active')->has('status')->etc())->etc()
        );
    }
}

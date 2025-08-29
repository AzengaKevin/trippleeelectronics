<?php

namespace Tests\Feature\Models;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_creating_a_new_service_record(): void
    {
        $attributes = [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
        ];

        Service::query()->create($attributes);

        $this->assertDatabaseHas('services', $attributes);

    }

    public function test_creating_a_new_service_record_from_service(): void
    {
        $service = Service::factory()->create();

        $this->assertNotNull($service);

    }

    public function test_service_author_relationship_method(): void
    {
        $service = Service::factory()->for(User::factory(), 'author')->create();

        $this->assertInstanceOf(\App\Models\User::class, $service->author);

        $this->assertEquals($service->author_user_id, $service->author->id);
    }
}

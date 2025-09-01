<?php

namespace Tests\Feature\Models;

use App\Models\SourceChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SourceChannelTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_source_channel_records(): void
    {
        $attributes = [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'code' => $this->faker->lexify('CODE-???'),
        ];

        $sourceChannel = SourceChannel::query()->create($attributes);

        $this->assertDatabaseHas('source_channels', [
            'id' => $sourceChannel->id,
            ...$attributes,
        ]);
    }

    public function test_creating_a_new_factory(): void
    {
        $sourceChannel = SourceChannel::factory()->create();

        $this->assertDatabaseHas('source_channels', [
            'id' => $sourceChannel->id,
        ]);
    }
}

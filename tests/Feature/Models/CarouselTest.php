<?php

namespace Tests\Feature\Models;

use App\Models\Carousel;
use App\Models\Enums\OrientationOption;
use App\Models\Enums\PositionOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CarouselTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_carousel_record(): void
    {

        $attributes = [
            'title' => $this->faker->sentence(),
            'position' => $this->faker->randomElement(PositionOption::options()),
            'orientation' => $this->faker->randomElement(OrientationOption::options()),
            'link' => $this->faker->url(),
            'description' => $this->faker->paragraph(),
            'active' => $this->faker->boolean(),
        ];

        Carousel::query()->create($attributes);

        $this->assertDatabaseHas('carousels', [
            'title' => $attributes['title'],
            'position' => $attributes['position'],
            'orientation' => $attributes['orientation'],
            'link' => $attributes['link'],
            'description' => $attributes['description'],
            'active' => $attributes['active'],
        ]);

    }

    public function test_creating_a_new_carousel_record_from_factory(): void
    {
        $carousel = Carousel::factory()->create();

        $this->assertNotNull($carousel);
    }

    public function test_carousel_author_relationship(): void
    {
        $carousel = Carousel::factory()->for(User::factory(), 'author')->create();

        $this->assertNotNull($carousel->author);

        $this->assertEquals($carousel->author_user_id, $carousel->author->id);
    }
}

<?php

namespace Tests\Feature\Models;

use App\Models\Individual;
use App\Models\Note;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_note(): void
    {

        $property = Property::factory()->create();

        $user = User::factory()->create();

        $reservation = Reservation::factory()->for($property)->for(Individual::factory(), 'primaryIndividual')->for($user, 'author')->create();

        $attributes = [
            'author_user_id' => $user->id,
            'notable_id' => $reservation->id,
            'notable_type' => $reservation->getMorphClass(),
            'content' => $this->faker->realText(),
        ];

        $note = Note::query()->create($attributes);

        $this->assertNotNull($note);

        $this->assertNotNull($note->author);

        $this->assertNotNull($note->notable);

        $this->assertInstanceOf(Reservation::class, $note->notable);

        $this->assertDatabaseHas('notes', $attributes);
    }

    public function test_create_a_note_from_factory(): void
    {

        $property = Property::factory()->create();

        $user = User::factory()->create();

        $reservation = Reservation::factory()->for($property)->for(Individual::factory(), 'primaryIndividual')->for($user, 'author')->create();

        $note = Note::factory()->for($user, 'author')->for($reservation, 'notable')->create();

        $this->assertNotNull($note);
        $this->assertNotNull($note->author);
        $this->assertNotNull($note->notable);
        $this->assertInstanceOf(Reservation::class, $note->notable);
    }
}

<?php

namespace Tests\Feature\Models;

use App\Models\Enums\ReservationStatus;
use App\Models\Individual;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\SourceChannel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_reservation(): void
    {

        $user = User::factory()->create();

        $property = Property::factory()->create();

        $individual = Individual::factory()->create();

        $sourceChannel = SourceChannel::factory()->create();

        $attributes = [
            'author_user_id' => $user->id,
            'property_id' => $property->id,
            'primary_individual_id' => $individual->id,
            'source_channel_id' => $sourceChannel->id,
            'status' => $this->faker->randomElement(ReservationStatus::options()),
            'checkin_date' => $this->faker->date(),
            'checkout_date' => $this->faker->date(),
            'adults' => $this->faker->numberBetween(1, 5),
            'children' => $this->faker->numberBetween(0, 5),
            'infants' => $this->faker->numberBetween(0, 5),
        ];

        $reservation = Reservation::query()->create($attributes);

        $this->assertNotNull($reservation);

        $this->assertNotNull($reservation->reference);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'reference' => $reservation->reference,
            'author_user_id' => $attributes['author_user_id'],
            'property_id' => $attributes['property_id'],
            'primary_individual_id' => $attributes['primary_individual_id'],
            'source_channel_id' => $attributes['source_channel_id'],
            'status' => $attributes['status'],
            'checkin_date' => $attributes['checkin_date'],
            'checkout_date' => $attributes['checkout_date'],
            'adults' => $attributes['adults'],
            'children' => $attributes['children'],
            'infants' => $attributes['infants'],
        ]);
    }

    public function test_creating_a_new_reservation_via_factory(): void
    {
        $reservation = Reservation::factory()->for(User::factory(), 'author')->for(Property::factory(), 'property')->for(Individual::factory(), 'primaryIndividual')->for(SourceChannel::factory(), 'sourceChannel')->create();

        $this->assertNotNull($reservation);

        $this->assertNotNull($reservation->reference);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'reference' => $reservation->reference,
            'author_user_id' => $reservation->author_user_id,
            'property_id' => $reservation->property_id,
            'primary_individual_id' => $reservation->primary_individual_id,
            'source_channel_id' => $reservation->source_channel_id,
            'status' => $reservation->status,
            'checkin_date' => $reservation->checkin_date,
            'checkout_date' => $reservation->checkout_date,
            'adults' => $reservation->adults,
            'children' => $reservation->children,
            'infants' => $reservation->infants,
        ]);
    }
}

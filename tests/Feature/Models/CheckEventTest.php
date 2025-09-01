<?php

namespace Tests\Feature\Models;

use App\Models\CheckEvent;
use App\Models\Individual;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CheckEventTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_check_event(): void
    {

        $property = Property::factory()->create();

        $reservation = Reservation::factory()->for($property)->for(Individual::factory(), 'primaryIndividual')->for(User::factory(), 'author')->create();

        $user = User::factory()->create();

        $checkEvent = CheckEvent::factory()->for($reservation)->for($user, 'author')->create();

        $this->assertNotNull($checkEvent);

        $this->assertNotNull($checkEvent->reservation);

        $this->assertNotNull($checkEvent->author);
    }
}

<?php

namespace Tests\Feature\Models;

use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_a_new_contact_can_be_created(): void
    {
        $attributes = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'phone' => $this->faker->phoneNumber(),
            'message' => $this->faker->paragraph(),
        ];

        $contact = Contact::query()->create($attributes);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'phone' => $attributes['phone'],
            'message' => $attributes['message'],
        ]);
    }

    public function test_creating_a_new_contact__from_factory(): void
    {
        $contact = Contact::factory()->create();

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'name' => $contact->name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'message' => $contact->message,
        ]);
    }
}

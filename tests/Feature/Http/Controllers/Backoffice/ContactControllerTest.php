<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-contacts',
            'create-contacts',
            'update-contacts',
            'read-contacts',
            'delete-contacts',
            'export-contacts',
            'import-contacts',
        ]);
    }

    public function test_backoffice_contacts_index_route(): void
    {
        Contact::factory()->count($contactsCount = 2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.contacts.index'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $assertableInertia) => $assertableInertia
                ->component('backoffice/contacts/IndexPage')
                ->has(
                    'contacts',
                    fn (AssertableInertia $assertableInertia) => $assertableInertia
                        ->has('data', $contactsCount)
                        ->has('links')
                        ->etc()
                )
        );
    }

    public function test_backoffice_contacts_show_route(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.contacts.show', $contact));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $assertableInertia) => $assertableInertia
                ->component('backoffice/contacts/ShowPage')
                ->has('contact', fn (AssertableInertia $assertableInertia) => $assertableInertia->where('id', $contact->id)->etc())
        );
    }

    public function test_backoffice_contacts_destroy_route(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.contacts.destroy', $contact));

        $response->assertStatus(302);

        $this->assertSoftDeleted('contacts', ['id' => $contact->id]);
    }
}

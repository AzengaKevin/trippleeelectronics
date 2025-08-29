<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class ItemMediaControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    private Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-items',
        ]);

        $this->item = Item::factory()->create();
    }

    public function test_backoffice_items_media_index_route(): void
    {

        $response = $this->actingAs($this->user)->get(route('backoffice.items.media.index', $this->item->id));

        $response->assertOk();

        $response->assertInertia(fn ($res) => $res->component('backoffice/items/media/IndexPage')->has('item')->has('media'));
    }
}

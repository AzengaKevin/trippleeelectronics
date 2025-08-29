<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class MediaControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-media',
            'create-media',
            'update-media',
            'delete-media',
        ]);
    }

    public function test_backoffice_media_index_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.media.index'));

        $response->assertOk();

        $response->assertInertia(fn ($res) => $res->component('backoffice/media/IndexPage')->has('media'));
    }
}

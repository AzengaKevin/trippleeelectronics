<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
        ]);
    }

    public function test_backoffice_notifications_index_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.notifications.index'));

        $response->assertOk();

        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/notifications/IndexPage')
                ->has('notifications')
                ->has('params')
        );
    }
}

<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: ['access-backoffice']);
    }

    public function test_backoffice_dashboard_route(): void
    {

        $response = $this->actingAs($this->user)->get(route('backoffice.dashboard'));

        $response->assertSuccessful();

        $response->assertInertia(function (AssertableInertia $page) {

            $page->has('auth');
        });
    }
}

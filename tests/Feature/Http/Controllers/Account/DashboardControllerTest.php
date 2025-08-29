<?php

namespace Tests\Feature\Http\Controllers\Account;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_account_dashboard_route()
    {
        $response = $this->actingAs($this->user)->get(route('account.dashboard'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('account/DashboardPage')
                ->has('user')
                ->where('user.id', $this->user->id)
                ->hasAll(['settings', 'categories', 'treeCategories', 'services'])
        );
    }
}

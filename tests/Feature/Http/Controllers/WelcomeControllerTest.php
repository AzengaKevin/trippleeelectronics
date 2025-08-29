<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomeControllerTest extends TestCase
{
    use RefreshDatabase;

    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_welcome_route_happy()
    {
        $response = $this->actingAs($this->user)->get(route('welcome'));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->component('WelcomePage')->has('brands')->has('settings')->has('tree_categories')->has('carousels')->has('categories_with_products')->has('brands')->has('categories'));
    }
}

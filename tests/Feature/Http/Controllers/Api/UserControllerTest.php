<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_api_users_index_route(): void
    {
        User::factory()->count($usersCount = 2)->create();

        $response = $this->getJson(route('api.users.index'));

        $response->assertOk()
            ->assertJsonCount($usersCount, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }
}

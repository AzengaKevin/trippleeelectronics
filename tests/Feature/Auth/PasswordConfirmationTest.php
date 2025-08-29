<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_confirm_password_screen_can_be_rendered()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('password.confirm'));

        $response->assertStatus(200);
    }

    public function test_password_is_not_confirmed_with_invalid_password()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('password.confirm.store'), [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
    }
}

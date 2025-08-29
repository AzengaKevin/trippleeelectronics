<?php

namespace Tests\Feature\Http\Controllers\Account;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_account_profile_show_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('account.profile.show'));

        $response->assertOk();

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('account/profile/ShowPage')
                ->has(
                    'user',
                    fn (AssertableInertia $page) => $page
                        ->where('id', $this->user->id)
                        ->etc()
                )
        );
    }

    public function test_update_profile_information_route()
    {
        $this->withoutExceptionHandling();

        $payload = [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => str('+254')->append(fake()->randomElement([1, 7]))->append(fake()->numberBetween(10000000, 99999999))->value(),
            'dob' => Carbon::parse($this->faker->date())->format('Y-m-d'),
            'address' => $this->faker->address(),
        ];

        $response = $this->actingAs($this->user)->put(route('user-profile-information.update'), $payload);

        $response->assertRedirect();

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => $payload['name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'],
            // 'dob' => Carbon::parse($payload['dob'])->format('Y-m-d'),
            'address' => $payload['address'],
        ]);
    }

    public function test_update_profile_information_route_with_avatar()
    {
        Storage::fake('public');

        $payload = [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => str('+254')->append(fake()->randomElement([1, 7]))->append(fake()->numberBetween(10000000, 99999999))->value(),
            'dob' => Carbon::parse($this->faker->date())->format('Y-m-d'),
            'address' => $this->faker->address(),
            'avatar' => $avatar = UploadedFile::fake()->image('avatar.jpg', 300, 300)->size(500),
        ];

        $response = $this->actingAs($this->user)->put(route('user-profile-information.update'), $payload);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => $payload['name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'],
            // 'dob' => Carbon::parse($payload['dob'])->format('Y-m-d'),
            'address' => $payload['address'],
        ]);

        $avatar = $this->user->fresh()->getFirstMedia('avatar');

        $this->assertNotNull($avatar);

    }

    public function test_user_password_update_route()
    {
        $this->withoutExceptionHandling();

        $payload = [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => $newPassword = 'new-password',
        ];

        $response = $this->actingAs($this->user)->put(route('user-password.update'), $payload);

        $response->assertSessionHasNoErrors();

        $response->assertRedirect();

        $this->assertTrue(Hash::check($newPassword, $this->user->fresh()->password));
    }

    public function test_user_password_update_route_with_a_4_digit_password()
    {
        $this->withoutExceptionHandling();

        $payload = [
            'current_password' => 'password',
            'password' => '1234',
            'password_confirmation' => '1234',
        ];

        $response = $this->actingAs($this->user)->put(route('user-password.update'), $payload);

        $response->assertSessionHasNoErrors();

        $response->assertRedirect();

        $this->assertTrue(Hash::check('1234', $this->user->fresh()->password));
    }

    public function test_user_password_update_route_with_invalid_current_password()
    {
        $payload = [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ];

        $response = $this->actingAs($this->user)->put(route('user-password.update'), $payload);

        $response->assertSessionHasErrors();

        $this->assertFalse(Hash::check('new-password', $this->user->fresh()->password));
    }
}

<?php

namespace Tests\Feature\Models;

use App\Models\Account;
use App\Models\Enums\AccountType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_account(): void
    {

        $user = User::factory()->create();

        $attributes = [
            'author_user_id' => $user->id,
            'name' => 'Test Account',
            'type' => $this->faker->randomElement(AccountType::options()),
        ];

        $account = Account::query()->create($attributes);

        $this->assertInstanceOf(Account::class, $account);

        $this->assertDatabaseHas('accounts', $attributes);

        $this->assertModelExists($account);

        $this->assertEquals($user->id, $account->author->id);
    }

    public function test_creating_an_account_from_factory(): void
    {
        $user = User::factory()->create();

        $account = Account::factory()->create([
            'author_user_id' => $user->id,
        ]);

        $this->assertInstanceOf(Account::class, $account);
        $this->assertModelExists($account);
        $this->assertEquals($user->id, $account->author->id);
    }
}

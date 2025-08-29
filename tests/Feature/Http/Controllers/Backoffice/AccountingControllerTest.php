<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\AccountingPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class AccountingControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(
            permissions: [
                'access-backoffice',
                'browse-accounting-periods',
                'create-accounting-periods',
                'update-accounting-periods',
                'delete-accounting-periods',
            ]
        );
    }

    public function test_backoffice_accounting_index_route(): void
    {
        AccountingPeriod::query()->create([
            'start_date' => '2023-01-01',
            'end_date' => '2023-01-31',
        ]);

        $response = $this->actingAs($this->user)->get(route('backoffice.accounting.index'));

        $response->assertOk();

        $response->assertInertia(
            fn (AssertableInertia $assertableInertia) => $assertableInertia
                ->component('backoffice/accounting/IndexPage')
                ->has('periods.data', 1)
                ->where('periods.data.0.start_date', '2023-01-01')
                ->where('periods.data.0.end_date', '2023-01-31')
        );
    }

    public function test_backoffice_accounting_show_route(): void
    {
        $period = AccountingPeriod::query()->create([
            'start_date' => '2023-01-01',
            'end_date' => '2023-01-31',
        ]);

        $response = $this->actingAs($this->user)->get(route('backoffice.accounting.show', $period));

        $response->assertOk();

        $response->assertInertia(
            fn (AssertableInertia $assertableInertia) => $assertableInertia
                ->component('backoffice/accounting/ShowPage')
                ->has('entries')
                ->where('period.id', $period->id)
                ->where('period.start_date', '2023-01-01')
                ->where('period.end_date', '2023-01-31')
        );
    }
}

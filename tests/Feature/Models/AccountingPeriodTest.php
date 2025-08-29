<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AccountingPeriodTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_last_month_accounting_period(): void
    {
        $attributes = [
            'start_date' => now()->subMonth()->startOfMonth(),
            'end_date' => now()->subMonth()->endOfMonth(),
        ];

        $accountingPeriod = \App\Models\AccountingPeriod::create($attributes);

        $this->assertDatabaseHas('accounting_periods', [
            'id' => $accountingPeriod->id,
            'start_date' => $attributes['start_date'],
            'end_date' => $attributes['end_date'],
        ]);
    }
}

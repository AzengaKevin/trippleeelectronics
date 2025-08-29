<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Enums\AccountType;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $data = collect([
            [
                'name' => 'Sales Revenue | Orders Income',
                'type' => AccountType::REVENUE,
            ],
            [
                'name' => 'Inventory',
                'type' => AccountType::ASSET,
            ],
            [
                'name' => 'Accounts Payable',
                'type' => AccountType::LIABILITY,
            ],
            [
                'name' => 'Cost of Goods | Purchase Expenses',
                'type' => AccountType::EXPENSE,
            ],
            [
                'name' => 'Accounts Receivable',
                'type' => AccountType::ASSET,
            ],
            [
                'name' => 'Wage Bill',
                'type' => AccountType::EXPENSE,
            ],
            [
                'name' => 'Other Bills',
                'type' => AccountType::EXPENSE,
            ],
        ]);

        $data->each(function ($item) {

            $attributes = ['name' => data_get($item, 'name')];

            $values = ['type' => data_get($item, 'type')];

            Account::query()->updateOrCreate($attributes, $values);
        });
    }
}

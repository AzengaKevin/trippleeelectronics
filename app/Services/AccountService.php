<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Enums\AccountType;

class AccountService
{
    public function get(
        ?int $perPage = 24,
        ?int $limit = null,
        ?array $with = null,
        ?array $withCount = null,
        ?string $orderBy = 'created_at',
        ?string $orderDirection = 'asc',
    ) {
        $accountQuery = Account::query();

        $accountQuery->when($with, function ($query, $with) {
            return $query->with($with);
        });

        $accountQuery->when($withCount, function ($query, $withCount) {
            return $query->withCount($withCount);
        });

        $accountQuery->when($limit, function ($query, $limit) {
            return $query->limit($limit);
        });

        $accountQuery->orderBy($orderBy, $orderDirection);

        return is_null($perPage)
            ? $accountQuery->get()
            : $accountQuery->paginate($perPage);
    }

    public function fetchSalesRevenueAccount(string $name = 'Sales Revenue | Orders Income'): Account
    {
        return Account::query()->updateOrCreate(
            ['name' => $name],
            ['type' => AccountType::REVENUE]
        );
    }

    public function fetchInventoryAccount(string $name = 'Inventory'): Account
    {
        return Account::query()->updateOrCreate(
            ['name' => $name],
            ['type' => AccountType::ASSET]
        );
    }

    public function fetchAccountsPayableAccount(string $name = 'Accounts Payable'): Account
    {
        return Account::query()->updateOrCreate(
            ['name' => $name],
            ['type' => AccountType::LIABILITY]
        );
    }

    public function fetchCostOfGoodsAccount(string $name = 'Cost of Goods | Purchase Expenses'): Account
    {
        return Account::query()->updateOrCreate(
            ['name' => $name],
            ['type' => AccountType::EXPENSE]
        );
    }

    public function fetchAccountsReceivableAccount(string $name = 'Accounts Receivable'): Account
    {
        return Account::query()->updateOrCreate(
            ['name' => $name],
            ['type' => AccountType::ASSET]
        );
    }

    public function fetchWageBillAccount(string $name = 'Wage Bill'): Account
    {
        return Account::query()->updateOrCreate(
            ['name' => $name],
            ['type' => AccountType::EXPENSE]
        );
    }

    public function fetchOtherBillsAccount(string $name = 'Other Bills'): Account
    {
        return Account::query()->updateOrCreate(
            ['name' => $name],
            ['type' => AccountType::EXPENSE]
        );
    }
}

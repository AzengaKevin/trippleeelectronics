<?php

namespace App\Services;

use App\Models\AccountingEntry;
use App\Models\AccountingPeriod;

class AccountingEntryService
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly ItemService $itemService,
        private readonly OrderService $orderService,
        private readonly PurchaseService $purchaseService,
        private readonly AccountingPeriodService $accountingPeriodService,
        private readonly AccountService $accountService,
    ) {}

    public function get(
        ?AccountingPeriod $accountingPeriod = null,
        ?int $perPage = 24,
        ?int $limit = null,
        ?array $with = null,
        ?array $withCount = null,
        ?string $orderBy = 'created_at',
        ?string $orderDirection = 'desc',
    ) {
        $accountingEntryQuery = AccountingEntry::query();

        $accountingEntryQuery->when(
            $accountingPeriod,
            fn ($query) => $query->where('accounting_period_id', $accountingPeriod->id)
        );

        $accountingEntryQuery->when(
            $with,
            fn ($query) => $query->with($with)
        );

        $accountingEntryQuery->when(
            $withCount,
            fn ($query) => $query->withCount($withCount)
        );

        $accountingEntryQuery->when(
            $limit,
            fn ($query) => $query->limit($limit)
        );

        $accountingEntryQuery->orderBy($orderBy, $orderDirection);

        return is_null($perPage)
            ? $accountingEntryQuery->get()
            : $accountingEntryQuery->paginate($perPage);
    }

    public function populateAccountingEntriesForPeriod(AccountingPeriod $accountingPeriod)
    {
        $this->populateSalesRevenueEntry($accountingPeriod);

        $this->populateInventoryEntry($accountingPeriod);

        $this->populateAccountsPayableEntry($accountingPeriod);

        $this->populatePurchaseExpensesEntry($accountingPeriod);

        $this->populateAccountsReceivableEntry($accountingPeriod);
    }

    public function populateSalesRevenueEntry(AccountingPeriod $accountingPeriod): void
    {
        $account = $this->accountService->fetchSalesRevenueAccount();

        $salesRevenueAmount = $this->paymentService->getAccountingPeriodSalesRevenue($accountingPeriod);

        $attributes = [
            'account_id' => $account->id,
            'accounting_period_id' => $accountingPeriod->id,
        ];

        $values = [
            'amount' => $salesRevenueAmount * $account->type->multiplier(),
        ];

        AccountingEntry::query()->updateOrCreate($attributes, $values);
    }

    public function populateInventoryEntry(AccountingPeriod $accountingPeriod): void
    {

        $account = $this->accountService->fetchInventoryAccount();

        $inventoryAmount = $this->itemService->getInventoryValue();

        $attributes = [
            'account_id' => $account->id,
            'accounting_period_id' => $accountingPeriod->id,
        ];

        $values = [
            'amount' => $inventoryAmount * $account->type->multiplier(),
        ];

        AccountingEntry::query()->updateOrCreate($attributes, $values);
    }

    public function populateAccountsPayableEntry(AccountingPeriod $accountingPeriod): void
    {
        $account = $this->accountService->fetchAccountsPayableAccount();

        $totalPurchases = $this->purchaseService->getTotalPurchases(from: $accountingPeriod->start_date->toDateString(), to: $accountingPeriod->end_date->toDateString());

        $purchaseExpensesAmount = $this->paymentService->getAccountingPeriodPurchaseExpenses($accountingPeriod);

        $accountsPayableAmount = $totalPurchases - $purchaseExpensesAmount;

        $attributes = [
            'account_id' => $account->id,
            'accounting_period_id' => $accountingPeriod->id,
        ];

        $values = [
            'amount' => $accountsPayableAmount * $account->type->multiplier(),
        ];

        AccountingEntry::query()->updateOrCreate($attributes, $values);
    }

    public function populatePurchaseExpensesEntry(AccountingPeriod $accountingPeriod): void
    {

        $account = $this->accountService->fetchCostOfGoodsAccount();

        $purchaseExpensesAmount = $this->paymentService->getAccountingPeriodPurchaseExpenses($accountingPeriod);

        $attributes = [
            'account_id' => $account->id,
            'accounting_period_id' => $accountingPeriod->id,
        ];

        $values = [
            'amount' => $purchaseExpensesAmount * $account->type->multiplier(),
        ];

        AccountingEntry::query()->updateOrCreate($attributes, $values);
    }

    public function populateAccountsReceivableEntry(AccountingPeriod $accountingPeriod): void
    {

        $account = $this->accountService->fetchAccountsReceivableAccount();

        $totalSales = $this->orderService->getTotalSales(from: $accountingPeriod->start_date->toDateString(), to: $accountingPeriod->end_date->toDateString());

        $salesRevenueAmount = $this->paymentService->getAccountingPeriodSalesRevenue($accountingPeriod);

        $accountsReceivableAmount = $totalSales - $salesRevenueAmount;

        $attributes = [
            'account_id' => $account->id,
            'accounting_period_id' => $accountingPeriod->id,
        ];

        $values = [
            'amount' => $accountsReceivableAmount * $account->type->multiplier(),
        ];

        AccountingEntry::query()->updateOrCreate($attributes, $values);
    }
}

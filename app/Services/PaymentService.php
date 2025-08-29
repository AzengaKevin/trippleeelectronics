<?php

namespace App\Services;

use App\Models\AccountingPeriod;
use App\Models\Enums\PaymentStatus;
use App\Models\Individual;
use App\Models\Order;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private readonly PaymentMethodService $paymentMethodService,
    ) {}

    public function get(
        ?string $query = null,
        ?array $with = null,
        ?array $withCount = null,
        ?int $limit = null,
        ?int $perPage = 24,
        ?Model $payable = null,
        ?Store $store = null,
        ?string $orderBy = 'created_at',
        ?string $orderDirection = 'desc',
    ) {

        $paymentQuery = Payment::search($query, function ($defaultQuery) use ($query, $with, $withCount, $limit, $payable, $store) {

            $defaultQuery->when($query, function ($queryBuilder) use ($query) {

                $queryBuilder->orWhere(function ($queryBuilder) use ($query) {
                    $queryBuilder->whereHas('payer', function ($queryBuilder) use ($query) {
                        $queryBuilder->where('name', 'like', "%{$query}%");
                    })->orWhereHas('payee', function ($queryBuilder) use ($query) {
                        $queryBuilder->where('name', 'like', "%{$query}%");
                    })->orWhereHas('payable', function ($queryBuilder) use ($query) {
                        $queryBuilder->where('reference', 'like', "%{$query}%");
                    })->orWhereHas('author', function ($queryBuilder) use ($query) {
                        $queryBuilder->where('name', 'like', "%{$query}%");
                    });
                });
            });

            $defaultQuery->when($payable, function ($queryBuilder) use ($payable) {
                $queryBuilder->where('payable_id', $payable->id)
                    ->where('payable_type', $payable->getMorphClass());
            });

            $defaultQuery->when($store, function ($queryBuilder) use ($store) {
                $queryBuilder->whereHas('payable', function ($queryBuilder) use ($store) {
                    $queryBuilder->where('store_id', $store->id);
                });
            });

            $defaultQuery->when($with, function ($queryBuilder) use ($with) {
                $queryBuilder->with($with);
            });

            $defaultQuery->when($withCount, function ($queryBuilder) use ($withCount) {
                $queryBuilder->withCount($withCount);
            });

            $defaultQuery->when($limit, function ($queryBuilder) use ($limit) {
                $queryBuilder->limit($limit);
            });
        });

        $paymentQuery->orderBy($orderBy, $orderDirection);

        return is_null($perPage)
            ? $paymentQuery->get()
            : $paymentQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Payment
    {
        return DB::transaction(function () use ($data) {

            $payable = null;
            $payer = null;
            $payee = null;

            if ($payableId = data_get($data, 'payable')) {

                $payable = Order::query()->find($payableId) ?? Purchase::query()->find($payableId);
            }

            if ($payerId = data_get($data, 'payer')) {

                $payer = Individual::query()->find($payerId) ?? Organization::query()->find($payerId);
            }

            if ($payeeId = data_get($data, 'payee')) {

                $payee = Individual::query()->find($payeeId) ?? Organization::query()->find($payeeId);
            }

            $attributes = [
                'author_user_id' => data_get($data, 'author_user_id'),
                'payable_id' => $payable?->id,
                'payable_type' => $payable?->getMorphClass(),
                'payer_id' => $payer?->id,
                'payer_type' => $payer?->getMorphClass(),
                'payee_id' => $payee?->id,
                'payee_type' => $payee?->getMorphClass(),
                'payment_method_id' => data_get($data, 'payment_method_id'),
                'phone_number' => data_get($data, 'phone_number'),
                'status' => $data['status'],
                'amount' => $data['amount'],
            ];

            /** @var Payment $payment */
            $payment = Payment::query()->create($attributes);

            $payment->loadMissing('paymentMethod');

            $isMpesa = $this->paymentMethodService->isMpesaPaymentMethod($payment->paymentMethod);

            if ($isMpesa && $payment->payable_type === 'order') {

                $payment->update(['status' => PaymentStatus::UNPAID]);
            }

            return $payment->fresh();
        });
    }

    public function update(Payment $payment, array $data): Payment
    {

        $payable = null;
        $payer = null;
        $payee = null;

        if ($payableId = data_get($data, 'payable')) {

            $payable = Order::query()->find($payableId) ?? Purchase::query()->find($payableId);
        }

        if ($payerId = data_get($data, 'payer')) {

            $payer = Individual::query()->find($payerId) ?? Organization::query()->find($payerId);
        }

        if ($payeeId = data_get($data, 'payee')) {

            $payee = Individual::query()->find($payeeId) ?? Organization::query()->find($payeeId);
        }

        $attributes = [
            'payable_id' => $payable?->id ?? $payment->payable_id,
            'payable_type' => $payable?->getMorphClass() ?? $payment->payable_type,
            'payer_id' => $payer?->id ?? $payment->payer_id,
            'payer_type' => $payer?->getMorphClass() ?? $payment->payer_type,
            'payee_id' => $payee?->id ?? $payment->payee_id,
            'payee_type' => $payee?->getMorphClass() ?? $payment->payee_type,
            'payment_method' => data_get($data, 'payment_method', $payment->payment_method),
            'payment_method_id' => data_get($data, 'payment_method_id', $payment->payment_method_id),
            'phone_number' => data_get($data, 'phone_number', $payment->phone_number),
            'status' => data_get($data, 'status', $payment->status),
            'amount' => data_get($data, 'amount', $payment->amount),
        ];

        return tap($payment)->update($attributes);
    }

    public function delete(Payment $payment, bool $forever = false): bool
    {
        if ($forever) {

            return $payment->forceDelete();
        }

        return $payment->delete();
    }

    public function importRow(array $data): Payment
    {

        $author = null;
        $payable = null;
        $payer = null;
        $payee = null;

        if ($authorName = data_get($data, 'author')) {

            $author = User::query()->where('name', $authorName)->first();
        }

        if ($payableRef = data_get($data, 'payable')) {

            $payable = Order::query()->where('reference', $payableRef)->first() ?? Purchase::query()->where('reference', $payableRef)->first();
        }

        if ($payerPhone = data_get($data, 'payer')) {

            $payer = Individual::query()->where('phone', $payerPhone)->first() ?? Organization::query()->where('phone', $payerPhone)->first();
        }

        if ($payeePhone = data_get($data, 'payee')) {

            $payee = Individual::query()->where('phone', $payeePhone)->first() ?? Organization::query()->where('phone', $payeePhone)->first();
        }

        $attributes = [
            'author_user_id' => $author?->id,
            'payable_id' => $payable?->id,
            'payable_type' => $payable?->getMorphClass(),
            'payer_id' => $payer?->id,
            'payer_type' => $payer?->getMorphClass(),
            'payee_id' => $payee?->id,
            'payee_type' => $payee?->getMorphClass(),
            'payment_method' => data_get($data, 'payment_method'),
            'status' => data_get($data, 'status'),
            'amount' => data_get($data, 'amount'),
        ];

        return Payment::create($attributes);
    }

    public function createOrderPayment(Order $order, array $data): Payment
    {
        $order->loadMissing('customer');

        $attributes = [
            'author_user_id' => data_get($data, 'author_user_id'),
            'payable' => $order->id,
            'payer' => $order->customer?->id,
            'payment_method_id' => data_get($data, 'payment_method_id'),
            'phone_number' => data_get($data, 'phone_number', $order->customer?->phone),
            'amount' => data_get($data, 'amount'),
            'status' => PaymentStatus::PAID,
        ];

        return $this->create($attributes);
    }

    public function createPurchasePayment(Purchase $purchase, array $data): Payment
    {
        $purchase->loadMissing('supplier');

        $attributes = [
            'author_user_id' => data_get($data, 'author_user_id'),
            'payable' => $purchase->id,
            'payee' => $purchase->supplier?->id,
            'payment_method_id' => data_get($data, 'payment_method_id'),
            'amount' => data_get($data, 'amount'),
            'status' => PaymentStatus::PAID,
        ];

        return $this->create($attributes);
    }

    public function getAccountingPeriodSalesRevenue(AccountingPeriod $accountingPeriod): float
    {
        return DB::table('payments')
            ->where('payable_type', 'order')
            ->where('status', PaymentStatus::PAID)
            ->whereBetween('created_at', [$accountingPeriod->start_date, $accountingPeriod->end_date])
            ->sum('amount');
    }

    public function getAccountingPeriodPurchaseExpenses(AccountingPeriod $accountingPeriod): float
    {
        return DB::table('payments')
            ->where('payable_type', 'purchase')
            ->where('status', PaymentStatus::PAID)
            ->whereBetween('created_at', [$accountingPeriod->start_date, $accountingPeriod->end_date])
            ->sum('amount');
    }
}

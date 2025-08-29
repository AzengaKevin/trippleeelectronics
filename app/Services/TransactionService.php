<?php

namespace App\Services;

use App\Models\Enums\PaymentStatus;
use App\Models\Enums\TransactionStatus;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function __construct(private MpesaService $mpesaService) {}

    public function get(
        ?string $query = null,
        ?string $status = null,
        ?string $method = null,
        ?int $perPage = 24,
        ?array $with = null,
        ?int $limit = null,
        ?array $withCount = null,
        ?string $orderBy = 'created_at',
        ?string $orderDirection = 'desc'
    ) {
        $transactioQuery = Transaction::search($query, function ($defaultQuery) use ($with, $withCount, $limit, $status, $method) {

            $defaultQuery->when($with, function ($query) use ($with) {
                $query->with($with);
            });

            $defaultQuery->when($withCount, function ($query) use ($withCount) {
                $query->withCount($withCount);
            });

            $defaultQuery->when($limit, function ($query) use ($limit) {
                $query->limit($limit);
            });

            $defaultQuery->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            });

            $defaultQuery->when($method, function ($query) use ($method) {
                $query->where('transaction_method', $method);
            });
        });

        $transactioQuery->orderBy($orderBy, $orderDirection);

        return is_null($perPage) ? $transactioQuery->get() : $transactioQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data)
    {
        $payment = null;

        if ($paymentRef = data_get($data, 'payment')) {

            $payment = Payment::query()->find($paymentRef);
        }

        $attributes = [
            'payment_id' => $payment->id ?? null,
            'amount' => data_get($data, 'amount'),
            'fee' => data_get($data, 'fee'),
            'transaction_method' => data_get($data, 'transaction_method'),
            'author_user_id' => data_get($data, 'author_user_id'),
            'status' => data_get($data, 'status', TransactionStatus::PENDING),
            'till' => data_get($data, 'till'),
            'paybill' => data_get($data, 'paybill'),
            'account_number' => data_get($data, 'account_number'),
            'phone_number' => data_get($data, 'phone_number'),
            'reference' => data_get($data, 'reference'),
        ];

        return Transaction::query()->create($attributes);
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        $attributes = [
            'payment_id' => data_get($data, 'payment_id', $transaction->payment_id),
            'amount' => data_get($data, 'amount', $transaction->amount),
            'fee' => data_get($data, 'fee', $transaction->fee),
            'transaction_method' => data_get($data, 'transaction_method', $transaction->transaction_method),
            'status' => data_get($data, 'status', $transaction->status),
            'till' => data_get($data, 'till', $transaction->till),
            'paybill' => data_get($data, 'paybill', $transaction->paybill),
            'account_number' => data_get($data, 'account_number', $transaction->account_number),
            'phone_number' => data_get($data, 'phone_number', $transaction->phone_number),
            'reference' => data_get($data, 'reference', $transaction->reference),
        ];

        $transaction->update($attributes);

        return $transaction;
    }

    public function delete(Transaction $transaction, bool $forever = false)
    {
        if ($forever) {

            return $transaction->forceDelete();
        }

        return $transaction->delete();
    }

    public function importRow(array $data): Transaction
    {
        $attributes = [
            'payment_id' => data_get($data, 'payment_id'),
            'amount' => data_get($data, 'amount'),
            'fee' => data_get($data, 'fee'),
            'transaction_method' => data_get($data, 'transaction_method'),
            'status' => data_get($data, 'status', TransactionStatus::PENDING),
            'till' => data_get($data, 'till'),
            'paybill' => data_get($data, 'paybill'),
            'account_number' => data_get($data, 'account_number'),
            'phone_number' => data_get($data, 'phone_number'),
        ];

        return Transaction::query()->create($attributes);
    }

    public function updateTransactionStatus(Transaction $transaction): void
    {
        $this->mpesaService->saloonCheckTransactionStatus($transaction);
    }

    public function markTransactionAsCompleted($checkoutRequestID)
    {
        DB::transaction(function () use ($checkoutRequestID) {

            $transaction = Transaction::query()->where('data->CheckoutRequestID', $checkoutRequestID)->first();

            if ($transaction) {

                $transaction->update([
                    'status' => TransactionStatus::COMPLETED,
                ]);

                $transaction->loadMissing('payment');

                if ($transaction->payment) {

                    $transaction->payment->update([
                        'status' => PaymentStatus::PAID,
                    ]);
                }
            }
        });
    }

    public function createPaymentTransaction(Payment $payment, array $data): Transaction
    {
        $attributes = [
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'author_user_id' => data_get($data, 'author_user_id'),
            'transaction_method' => data_get($data, 'transaction_method'),
            'status' => data_get($data, 'status', TransactionStatus::PENDING),
            'till' => data_get($data, 'till'),
            'paybill' => data_get($data, 'paybill'),
            'account_number' => data_get($data, 'account_number'),
            'phone_number' => data_get($data, 'phone'),
        ];

        return Transaction::query()->create($attributes);
    }
}

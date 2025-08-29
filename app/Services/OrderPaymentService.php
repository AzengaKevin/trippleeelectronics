<?php

namespace App\Services;

use App\Models\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderPaymentService
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    public function get(
        ?Store $store = null,
        ?User $author = null,
        ?string $status = null,
        ?string $to = null,
        ?string $from = null,
        ?string $paymentStatus = null,
        ?int $limit = null,
        ?array $with = null,
        ?int $perPage = 24,
        ?string $orderBy = 'created_at',
        ?string $orderDirection = 'desc',
    ) {
        $paymentQuery = Payment::query();

        $paymentQuery->whereExists('payable', function ($queryBuilder) {
            $queryBuilder->where('payable_type', 'order')->whereNull('deleted_at');
        });

        $paymentQuery->when($store, function ($queryBuilder) use ($store) {
            $queryBuilder->whereHas('payable', function ($queryBuilder) use ($store) {
                $queryBuilder->where('store_id', $store->id);
            });
        });
        $paymentQuery->when($author, function ($queryBuilder) use ($author) {
            $queryBuilder->whereHas('payable', function ($queryBuilder) use ($author) {
                $queryBuilder->where('author_user_id', $author->id);
            });
        });

        $paymentQuery->when($status, function ($queryBuilder) use ($status) {
            $queryBuilder->whereHas('payable', function ($queryBuilder) use ($status) {
                $queryBuilder->where('order_status', $status);
            });
        });

        $paymentQuery->when($paymentStatus, function ($queryBuilder) use ($paymentStatus) {
            $queryBuilder->where('status', $paymentStatus);
        });

        $paymentQuery->when($limit, function ($queryBuilder) use ($limit) {
            $queryBuilder->limit($limit);
        });

        $paymentQuery->when($with, function ($queryBuilder) use ($with) {
            $queryBuilder->with($with);
        });

        $paymentQuery->when($to, function ($queryBuilder) use ($to) {
            $queryBuilder->whereHas('payable', function ($queryBuilder) use ($to) {
                $queryBuilder->whereDate('created_at', '<=', $to);
            });
        });

        $paymentQuery->when($from, function ($queryBuilder) use ($from) {
            $queryBuilder->whereHas('payable', function ($queryBuilder) use ($from) {
                $queryBuilder->whereDate('created_at', '>=', $from);
            });
        });

        $paymentQuery->orderBy($orderBy, $orderDirection);

        $paymentQuery->status(PaymentStatus::PAID);

        return is_null($perPage)
            ? $paymentQuery->get()
            : $paymentQuery->paginate($perPage)->withQueryString();
    }

    public function getRaw(
        ?Store $store = null,
        ?User $author = null,
        ?string $status = null,
        ?string $to = null,
        ?string $from = null,
    ) {
        return DB::table('payments')
            ->select([
                'payment_methods.id AS payment_method_id',
                'payment_method_store.phone_number',
                'payment_method_store.paybill_number',
                'payment_method_store.account_number',
                'payment_method_store.till_number',
                'payment_method_store.account_name',
                DB::raw('COUNT(payments.id) AS payments_count'),
                DB::raw('SUM(payments.amount) AS payments_amount_sum'),
            ])
            ->leftJoin('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->leftJoin('orders', function ($join) {
                $join->on('payments.payable_id', '=', 'orders.id')->where('payments.payable_type', 'order');
            })
            ->leftJoin('payment_method_store', function ($join) {
                $join->on('payment_methods.id', '=', 'payment_method_store.payment_method_id')
                    ->on('orders.store_id', '=', 'payment_method_store.store_id');
            })
            ->when($to, function ($queryBuilder) use ($to) {
                $queryBuilder->whereDate('orders.created_at', '<=', $to);
            })
            ->when($from, function ($queryBuilder) use ($from) {
                $queryBuilder->whereDate('orders.created_at', '>=', $from);
            })
            ->when($store, function ($queryBuilder) use ($store) {
                $queryBuilder->where('orders.store_id', $store->id);
            })
            ->when($author, function ($queryBuilder, $author) {
                $queryBuilder->where('payments.author_user_id', $author?->id);
            })
            ->when($status, function ($queryBuilder) use ($status) {
                $queryBuilder->where('orders.order_status', $status);
            })
            ->where('payments.status', PaymentStatus::PAID->value)
            ->whereNull('orders.deleted_at')
            ->groupBy(
                'payment_methods.id',
                'payment_method_store.phone_number',
                'payment_method_store.paybill_number',
                'payment_method_store.account_number',
                'payment_method_store.till_number',
                'payment_method_store.account_name',
            )
            ->get();
    }
}

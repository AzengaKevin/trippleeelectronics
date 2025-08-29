<?php

namespace App\Services;

use App\Models\Enums\PaymentStatus;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(
        private readonly PurchaseItemService $purchaseItemService,
        private readonly ClientService $clientService,
    ) {}

    public function get(
        ?string $query = null,
        ?int $perPage = 24,
        ?int $limit = null,
        ?Store $store = null,
        ?array $with = ['supplier', 'store'],
        ?bool $withoutStore = false,
        ?string $orderBy = 'created_at',
        ?string $orderDir = 'desc',
    ) {
        $purchaseQuery = Purchase::query();

        $purchaseQuery->select('purchases.*');

        $purchaseQuery->addSelect([
            'paid_amount' => DB::table('payments')
                ->selectRaw('SUM(payments.amount)')
                ->whereColumn('payments.payable_id', 'purchases.id')
                ->where('payments.payable_type', 'purchase')
                ->where('payments.status', PaymentStatus::PAID->value)
                ->limit(1),
        ]);

        $purchaseQuery->when($query, function ($defaultQuery, $query) {

            $defaultQuery->whereHas('supplier', function ($innerQuery) use ($query) {

                $innerQuery->where('name', 'like', "%{$query}%");
            });

            $defaultQuery->orWhereHas('author', function ($innerQuery) use ($query) {

                $innerQuery->where('name', 'like', "%{$query}%");
            });

            $defaultQuery->orWhere('reference', 'like', "%{$query}%");
        });

        $purchaseQuery->when($store, function ($defaultQuery, $store) {
            $defaultQuery->where('store_id', $store->id);
        });

        $purchaseQuery->when($withoutStore, function ($defaultQuery) {
            $defaultQuery->whereNull('store_id');
        });

        $purchaseQuery->when($with, function ($defaultQuery, $with) {

            $defaultQuery->with($with);
        });

        $purchaseQuery->when($limit, function ($defaultQuery, $limit) {

            $defaultQuery->limit($limit);
        });

        $purchaseQuery->orderBy($orderBy, $orderDir);

        return is_null($perPage)
            ? $purchaseQuery->get()
            : $purchaseQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Purchase
    {

        return DB::transaction(function () use ($data) {

            $supplier = $this->clientService->upsert(data_get($data, 'supplier', []));

            $purchaseAttributes = [
                'author_user_id' => data_get($data, 'author_user_id'),
                'store_id' => data_get($data, 'store'),
                'supplier_id' => $supplier?->id,
                'supplier_type' => $supplier?->getMorphClass(),
                'amount' => data_get($data, 'amount'),
                'shipping_amount' => data_get($data, 'shipping_amount'),
                'total_amount' => data_get($data, 'total_amount'),
            ];

            $purchase = Purchase::query()->create($purchaseAttributes);

            collect($data['items'])->each(function ($item) use ($purchase) {

                $this->purchaseItemService->create([
                    ...$item,
                    'purchase_id' => $purchase->id,
                ]);
            });

            return $purchase;
        });
    }

    public function update(Purchase $purchase, array $data): bool
    {

        return DB::transaction(function () use ($purchase, $data) {

            $purchase->items()->forceDelete();

            collect($data['items'])->each(function ($item) use ($purchase) {

                $this->purchaseItemService->create([
                    ...$item,
                    'purchase_id' => $purchase->id,
                ]);
            });

            $supplier = $this->clientService->upsert(data_get($data, 'supplier', []));

            $attributes = [
                'store_id' => data_get($data, 'store'),
                'supplier_id' => $supplier?->id,
                'supplier_type' => $supplier?->getMorphClass(),
                'amount' => data_get($data, 'amount'),
                'shipping_amount' => data_get($data, 'shipping_amount'),
                'total_amount' => data_get($data, 'total_amount'),
            ];

            return $purchase->update($attributes);
        });
    }

    public function delete(Purchase $purchase, bool $forever = false): ?bool
    {
        return $forever ? $purchase->forceDelete() : $purchase->delete();
    }

    public function importRow(array $data)
    {
        $values = [
            'author_user_id' => data_get($data, 'author_user_id'),
            'supplier_type' => data_get($data, 'supplier_type'),
            'supplier_id' => data_get($data, 'supplier_id'),
            'amount' => data_get($data, 'amount'),
            'shipping_amount' => data_get($data, 'shipping_amount'),
            'total_amount' => data_get($data, 'total_amount'),
            'store_id' => data_get($data, 'store_id'),
        ];

        $purchase = Purchase::where('reference', data_get($data, 'reference'))->first();

        if ($purchase) {

            $purchase->update($values);
        } else {

            Purchase::query()->create($values);
        }
    }

    public function getTotalPurchases(
        ?Store $store = null,
        ?User $author = null,
        ?string $to = null,
        ?string $from = null
    ): float {
        return Purchase::query()
            ->when($author, function ($defaultQuery, $author) {
                $defaultQuery->where('author_user_id', $author->id);
            })
            ->when($store, function ($defaultQuery, $store) {
                $defaultQuery->where('store_id', $store->id);
            })
            ->when($to, function ($defaultQuery, $to) {
                $defaultQuery->whereDate('created_at', '<=', $to);
            })
            ->when($from, function ($defaultQuery, $from) {
                $defaultQuery->whereDate('created_at', '>=', $from);
            })
            ->sum('total_amount');
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DealService
{
    public function get(
        ?string $query = null,
        ?string $client = null,
        ?int $limit = null,
        ?int $perPage = 24,
    ) {

        $dbDriver = DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $caseInsensitiveNameQuery = match ($dbDriver) {
            'mysql' => 'LOWER(reference) LIKE LOWER(?)',
            'pgsql' => 'reference ILIKE ?',
            'sqlite' => 'reference LIKE ? COLLATE NOCASE',
            default => 'reference LIKE ?',
        };

        $orderQuery = DB::table('orders')
            ->when($query, function ($queryBuilder, $query) use ($caseInsensitiveNameQuery) {
                $queryBuilder->whereRaw($caseInsensitiveNameQuery, ["%{$query}%"]);
            })
            ->when($client, function ($queryBuilder, $client) {
                $queryBuilder->where('customer_id', $client);
            })
            ->select(
                'id',
                'reference',
                'customer_type AS client_type',
                'customer_id AS client_id',
                'store_id',
                'total_amount',
                'author_user_id',
                'created_at',
                DB::raw("'order' AS type")
            );

        $purchaseQuery = DB::table('purchases')
            ->when($query, function ($queryBuilder, $query) use ($caseInsensitiveNameQuery) {
                $queryBuilder->whereRaw($caseInsensitiveNameQuery, ["%{$query}%"]);
            })
            ->when($client, function ($queryBuilder, $client) {
                $queryBuilder->where('supplier_id', $client);
            })
            ->select(
                'id',
                'reference',
                'supplier_type AS client_type',
                'supplier_id AS client_id',
                'store_id',
                'total_amount',
                'author_user_id',
                'created_at',
                DB::raw("'purchase' AS type")
            );

        $dealsQuery = $orderQuery->unionAll($purchaseQuery)->orderBy('created_at', 'desc')
            ->when($limit, function ($queryBuilder) use ($limit) {
                $queryBuilder->limit($limit);
            });

        return is_null($perPage)
            ? $dealsQuery->get()
            : $dealsQuery->paginate($perPage)->withQueryString();
    }
}

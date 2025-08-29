<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ProductService
{
    public function get(
        ?string $query = null,
        ?int $limit = null,
        ?int $perPage = 40,
        bool $includeVariants = false,
        bool $includeCustomItems = false,

    ) {

        $dbDriver = DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $caseInsensitiveNameQuery = match ($dbDriver) {
            'mysql' => 'LOWER(name) LIKE LOWER(?)',
            'pgsql' => 'name ILIKE ?',
            'sqlite' => 'name LIKE ? COLLATE NOCASE',
            default => 'name LIKE ?',
        };

        $itemQuery = DB::table('items')
            ->when($query, function ($queryBuilder, $query) use ($caseInsensitiveNameQuery) {
                $queryBuilder->whereRaw($caseInsensitiveNameQuery, ["%{$query}%"]);
            })
            ->whereNull('deleted_at')
            ->select(
                'id',
                'sku',
                'name',
                'slug',
                'cost',
                'price',
                'tax_rate',
                'quantity',
                'pos_name',
                DB::raw("'item' as type")
            );

        $itemVariantQuery = DB::table('item_variants')
            ->when($query, function ($queryBuilder, $query) use ($caseInsensitiveNameQuery) {
                $queryBuilder->whereRaw($caseInsensitiveNameQuery, ["%{$query}%"]);
            })->whereNull('deleted_at')
            ->select(
                'id',
                'sku',
                'name',
                'slug',
                'cost',
                'price',
                'tax_rate',
                'quantity',
                'pos_name',
                DB::raw("'item-variant' as type")
            );

        $customItemsQuery = DB::table('custom_items')
            ->when($query, function ($queryBuilder, $query) use ($caseInsensitiveNameQuery) {
                $queryBuilder->whereRaw($caseInsensitiveNameQuery, ["%{$query}%"]);
            })
            ->select(
                'id',
                DB::raw('null as sku'),
                'name',
                DB::raw('name as slug'),
                'cost',
                'price',
                DB::raw('0 as tax_rate'),
                DB::raw('0 as quantity'),
                'pos_name',
                DB::raw("'custom-item' as type")
            );

        $productsQuery = $itemQuery->when($includeCustomItems, function ($query) use ($customItemsQuery) {
            $query->unionAll($customItemsQuery);
        })->when($includeVariants, function ($query) use ($itemVariantQuery) {
            $query->unionAll($itemVariantQuery);
        })->orderBy('name')->when($limit, function ($query) use ($limit) {
            $query->limit($limit);
        });

        return is_null($perPage)
            ? $productsQuery->get()
            : $productsQuery->paginate($perPage)->withQueryString();
    }

    public function getOutOfStock(
        ?string $query = null,
        ?int $limit = null,
        ?int $perPage = 24,
        bool $includeVariants = false,

    ) {

        $dbDriver = DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $caseInsensitiveNameQuery = match ($dbDriver) {
            'mysql' => 'LOWER(name) LIKE LOWER(?)',
            'pgsql' => 'name ILIKE ?',
            'sqlite' => 'name LIKE ? COLLATE NOCASE',
            default => 'name LIKE ?',
        };

        $itemQuery = DB::table('items')
            ->when($query, function ($queryBuilder, $query) use ($caseInsensitiveNameQuery) {
                $queryBuilder->whereRaw($caseInsensitiveNameQuery, ["%{$query}%"]);
            })
            ->where('quantity', '<=', 0)
            ->select(
                'id',
                'sku',
                'name',
                'slug',
                'cost',
                'price',
                'quantity',
                'pos_name',
                DB::raw("'item' as type")
            );

        $itemVariantQuery = DB::table('item_variants')
            ->when($query, function ($queryBuilder, $query) use ($caseInsensitiveNameQuery) {
                $queryBuilder->whereRaw($caseInsensitiveNameQuery, ["%{$query}%"]);
            })
            ->where('quantity', '<=', 0)
            ->select(
                'id',
                'sku',
                'name',
                'slug',
                'cost',
                'price',
                'quantity',
                'pos_name',
                DB::raw("'item-variant' as type")
            );

        $productsQuery = $itemQuery->when($includeVariants, function ($query) use ($itemVariantQuery) {
            $query->unionAll($itemVariantQuery);
        });

        $productsQuery->when($limit, function ($query) use ($limit) {
            $query->limit($limit);
        });

        $productsQuery->orderBy('name');

        return is_null($perPage)
            ? $productsQuery->get()
            : $productsQuery->paginate($perPage)->withQueryString();
    }
}

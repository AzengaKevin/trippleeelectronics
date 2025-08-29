<?php

namespace App\Services;

use App\Models\Enums\OrderStatus;
use App\Models\Store;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class SalesService
{
    public function __construct(
        private SettingsService $settingsService,
        private OrderPaymentService $orderPaymentService,
        private PaymentMethodService $paymentMethodService,
    ) {}

    public function getRawItems(
        ?Store $store = null,
        ?User $author = null,
        ?string $to = null,
        ?string $from = null,
        ?int $limit = null,
        ?int $perPage = 24,
        ?string $query = null,
        ?string $orderBy = 'created_at',
        ?string $orderDirection = 'desc',
    ) {

        $dbDriver = DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $caseInsensitiveNameQuery = match ($dbDriver) {
            'mysql' => 'LOWER(name) LIKE LOWER(?)',
            'pgsql' => 'name ILIKE ?',
            'sqlite' => 'name LIKE ? COLLATE NOCASE',
            default => 'name LIKE ?',
        };

        $itemSalesQuery = DB::table('items')
            ->join('order_items', function ($join) {
                $join->on('items.id', '=', 'order_items.item_id')
                    ->where('order_items.item_type', 'item');
            })
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->when($query, function ($queryBuilder, $query) use ($caseInsensitiveNameQuery) {
                $queryBuilder->whereRaw($caseInsensitiveNameQuery, ["%{$query}%"]);
            })
            ->select(
                'items.id',
                'items.name',
                'items.cost',
                'items.price',
                DB::raw('items.price - items.cost AS profit'),
                DB::raw('SUM(order_items.quantity) AS quantity'),
                DB::raw('SUM(order_items.quantity * items.cost) AS total_cost'),
                DB::raw('SUM(order_items.quantity * order_items.price) AS total_sales'),
                DB::raw('SUM(order_items.quantity * order_items.price) - SUM(order_items.quantity * items.cost) AS net_profit'),
                DB::raw('ROUND(CASE WHEN SUM(order_items.quantity * items.cost) = 0 THEN 100 ELSE ( (SUM(order_items.quantity * order_items.price) - SUM(order_items.quantity * items.cost)) / SUM(order_items.quantity * items.cost) * 100 ) END, 2) AS profit_margin'),
            )
            ->when($store, function ($queryBuilder) use ($store) {
                $queryBuilder->where('orders.store_id', $store->id);
            })->when($author, function ($queryBuilder) use ($author) {
                $queryBuilder->where('orders.author_user_id', $author->id);
            })
            ->when($to, function ($queryBuilder) use ($to) {
                $queryBuilder->whereDate('orders.created_at', '<=', $to);
            })
            ->when($from, function ($queryBuilder) use ($from) {
                $queryBuilder->whereDate('orders.created_at', '>=', $from);
            })
            ->where('orders.order_status', OrderStatus::COMPLETED->value)
            ->whereNull('orders.deleted_at')
            ->groupBy('items.id');

        $itemVariantSalesQuery = DB::table('item_variants')
            ->join('order_items', function ($join) {
                $join->on('item_variants.id', '=', 'order_items.item_id')
                    ->where('order_items.item_type', 'item-variant');
            })
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->when($query, function ($queryBuilder, $query) use ($caseInsensitiveNameQuery) {
                $queryBuilder->whereRaw($caseInsensitiveNameQuery, ["%{$query}%"]);
            })
            ->select(
                'item_variants.id',
                'item_variants.name',
                'item_variants.cost',
                'item_variants.price',
                DB::raw('item_variants.price - item_variants.cost AS profit'),
                DB::raw('SUM(order_items.quantity) AS quantity'),
                DB::raw('SUM(order_items.quantity * item_variants.cost) AS total_cost'),
                DB::raw('SUM(order_items.quantity * order_items.price) AS total_sales'),
                DB::raw('SUM(order_items.quantity * order_items.price) - SUM(order_items.quantity * item_variants.cost) AS net_profit'),
                DB::raw('ROUND(CASE WHEN SUM(order_items.quantity * item_variants.cost) = 0 THEN 100 ELSE ( (SUM(order_items.quantity * order_items.price) - SUM(order_items.quantity * item_variants.cost)) / SUM(order_items.quantity * item_variants.cost) * 100 ) END, 2) AS profit_margin'),
            )
            ->when($store, function ($queryBuilder) use ($store) {
                $queryBuilder->where('orders.store_id', $store->id);
            })->when($author, function ($queryBuilder) use ($author) {
                $queryBuilder->where('orders.author_user_id', $author->id);
            })
            ->when($to, function ($queryBuilder) use ($to) {
                $queryBuilder->whereDate('orders.created_at', '<=', $to);
            })
            ->when($from, function ($queryBuilder) use ($from) {
                $queryBuilder->whereDate('orders.created_at', '>=', $from);
            })
            ->where('orders.order_status', OrderStatus::COMPLETED->value)
            ->whereNull('orders.deleted_at')
            ->groupBy('item_variants.id');

        $customItemsSalesQuery = DB::table('custom_items')
            ->join('order_items', function ($join) {
                $join->on('custom_items.id', '=', 'order_items.item_id')
                    ->where('order_items.item_type', 'custom-item');
            })
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->when($query, function ($queryBuilder, $query) use ($caseInsensitiveNameQuery) {
                $queryBuilder->whereRaw($caseInsensitiveNameQuery, ["%{$query}%"]);
            })
            ->select(
                'custom_items.id',
                'custom_items.name',
                'custom_items.cost',
                'custom_items.price',
                DB::raw('custom_items.price - custom_items.cost AS profit'),
                DB::raw('SUM(order_items.quantity) AS quantity'),
                DB::raw('SUM(order_items.quantity * custom_items.cost) AS total_cost'),
                DB::raw('SUM(order_items.quantity * order_items.price) AS total_sales'),
                DB::raw('SUM(order_items.quantity * custom_items.price) - SUM(order_items.quantity * custom_items.cost) AS net_profit'),
                DB::raw('ROUND(CASE WHEN SUM(order_items.quantity * custom_items.cost) = 0 THEN 100 ELSE ( (SUM(order_items.quantity * order_items.price) - SUM(order_items.quantity * custom_items.cost)) / SUM(order_items.quantity * custom_items.cost) * 100 ) END, 2) AS profit_margin'),
            )
            ->when($store, function ($queryBuilder) use ($store) {
                $queryBuilder->where('orders.store_id', $store->id);
            })->when($author, function ($queryBuilder) use ($author) {
                $queryBuilder->where('orders.author_user_id', $author->id);
            })
            ->when($to, function ($queryBuilder) use ($to) {
                $queryBuilder->whereDate('orders.created_at', '<=', $to);
            })
            ->when($from, function ($queryBuilder) use ($from) {
                $queryBuilder->whereDate('orders.created_at', '>=', $from);
            })
            ->where('orders.order_status', OrderStatus::COMPLETED->value)
            ->whereNull('orders.deleted_at')
            ->groupBy('custom_items.id');

        $productsQuery = $itemSalesQuery->unionAll($customItemsSalesQuery)->unionAll($itemVariantSalesQuery)
            ->orderBy('name')->when($limit, function ($query) use ($limit) {
                $query->limit($limit);
            });

        return is_null($perPage)
            ? $productsQuery->get()
            : $productsQuery->paginate($perPage)->withQueryString();
    }

    public function generatePnlReportDetails(
        ?Store $store = null,
        ?User $author = null,
        ?string $to = null,
        ?string $from = null,
        ?int $limit = null,
        ?int $perPage = 24,
        ?string $query = null,
        ?string $orderBy = 'created_at',
        ?string $orderDirection = 'desc',
    ) {
        $items = $this->getRawItems(
            store: $store,
            author: $author,
            to: $to,
            from: $from,
            limit: $limit,
            perPage: null,
            query: $query,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        $settings = $this->settingsService->get(group: null);

        $paymentsAggregates = $this->orderPaymentService->getRaw(
            store: $store,
            author: $author,
            to: $to,
            from: $from,
            status: OrderStatus::COMPLETED->value,
        );

        $methods = $this->paymentMethodService->get(perPage: null);

        $methods = $paymentsAggregates->whereNotNull('payment_method_id')->map(function ($item) use ($methods) {

            $method = $methods->first(fn ($m) => $m->id === $item->payment_method_id);

            $labelBuilder = str($method->name);

            foreach (data_get($method, 'required_fields', []) as $field) {

                $value = $item->{$field};

                $labelBuilder = $labelBuilder->append(" : {$value}");
            }

            return [
                'id' => $method->id,
                'name' => $labelBuilder->value(),
                'count' => $item->payments_count,
                'total' => $item->payments_amount_sum,
            ];
        });

        $data = compact('items', 'settings', 'methods', 'store', 'author', 'to', 'from');

        $pdf = Pdf::setPaper('A4', 'landscape')->loadView('printouts.pnl', $data);

        $pdf->render();

        return $pdf->output();
    }
}

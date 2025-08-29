<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Models\Enums\OrderStatus;
use App\Models\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private readonly OrderPaymentService $orderPaymentService,
        private readonly OrderItemService $orderItemService,
        private readonly SettingsService $settingsService,
        private readonly ClientService $clientService,
        private readonly PaymentMethodService $paymentMethodService,
    ) {}

    public function get(
        ?string $query = null,
        ?int $perPage = 36,
        ?int $limit = null,
        ?array $with = ['store', 'customer', 'author'],
        ?Store $store = null,
        ?string $status = null,
        ?string $orderBy = 'created_at',
        ?string $orderDirection = 'desc',
        ?User $user = null,
        ?User $author = null,
        ?string $from = null,
        ?string $to = null,
        ?bool $withOutstandingAmount = false,
        ?bool $withTrashed = false,
    ) {
        $orderQuery = Order::query();

        $orderQuery->select('orders.*');

        $orderQuery->addSelect([
            'paid_amount' => DB::table('payments')
                ->selectRaw('SUM(payments.amount)')
                ->whereColumn('payments.payable_id', 'orders.id')
                ->where('payments.payable_type', 'order')
                ->where('payments.status', PaymentStatus::PAID->value)
                ->limit(1),
        ]);

        $orderQuery->when($withTrashed, function ($defaultQuery) {
            $defaultQuery->withTrashed();
        });

        $orderQuery->when($query, function ($defaultQuery, $query) {

            $dbDriver = DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

            $caseInsensitiveColumnQuery = match ($dbDriver) {
                'mysql' => 'LOWER(%s) LIKE LOWER(?)',
                'pgsql' => '%s ILIKE ?',
                'sqlite' => '%s LIKE ? COLLATE NOCASE',
                default => '%s LIKE ?',
            };

            $defaultQuery->where(function ($nestedInnerQuery) use ($query, $caseInsensitiveColumnQuery) {
                $nestedInnerQuery->whereHas('customer', function ($level2NestedInnerQuery) use ($query, $caseInsensitiveColumnQuery) {

                    $level2NestedInnerQuery->whereRaw(sprintf($caseInsensitiveColumnQuery, 'name'), "%{$query}%")
                        ->orWhereRaw(sprintf($caseInsensitiveColumnQuery, 'phone'), "%{$query}%")
                        ->orWhereRaw(sprintf($caseInsensitiveColumnQuery, 'email'), "%{$query}%");
                })->orWhereHas('author', function ($level2NestedInnerQuery) use ($query, $caseInsensitiveColumnQuery) {

                    $level2NestedInnerQuery->whereRaw(sprintf($caseInsensitiveColumnQuery, 'name'), "%{$query}%")
                        ->orWhereRaw(sprintf($caseInsensitiveColumnQuery, 'phone'), "%{$query}%")
                        ->orWhereRaw(sprintf($caseInsensitiveColumnQuery, 'email'), "%{$query}%");
                })->orWhereRaw(sprintf($caseInsensitiveColumnQuery, 'reference'), "%{$query}%");
            });
        });

        $orderQuery->when($store, function ($defaultQuery, $store) {
            $defaultQuery->where('store_id', $store->id);
        });

        $orderQuery->when($user, function ($defaultQuery, $user) {
            $defaultQuery->where('user_id', $user->id);
        });

        $orderQuery->when($with, function ($defaultQuery, $with) {
            $defaultQuery->with($with);
        });

        $orderQuery->when($limit, function ($defaultQuery, $limit) {
            $defaultQuery->limit($limit);
        });

        $orderQuery->when($status, function ($defaultQuery, $status) {
            $defaultQuery->where('order_status', $status);
        });

        $orderQuery->when($author, function ($defaultQuery, $author) {
            $defaultQuery->where('author_user_id', $author->id);
        });

        $orderQuery->when($from, function ($defaultQuery, $from) {
            $defaultQuery->whereDate('created_at', '>=', Carbon::parse($from));
        });

        $orderQuery->when($to, function ($defaultQuery, $to) {
            $defaultQuery->whereDate('created_at', '<=', Carbon::parse($to));
        });

        $orderQuery->when(boolval($withOutstandingAmount), function ($defaultQuery) {
            $defaultQuery->whereRaw('orders.total_amount > (SELECT COALESCE(SUM(payments.amount), 0) FROM payments WHERE payments.payable_id = orders.id AND payments.payable_type = ? AND payments.status = ?)', ['order', PaymentStatus::PAID->value]);
        });

        $orderQuery->orderBy($orderBy, $orderDirection);

        return is_null($perPage)
            ? $orderQuery->get()
            : $orderQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {

            $customer = $this->clientService->upsert(data_get($data, 'customer', []));

            $orderAttributes = [
                'store_id' => data_get($data, 'store'),
                'author_user_id' => data_get($data, 'author_user_id'),
                'user_id' => data_get($data, 'user_id'),
                'customer_id' => $customer?->id,
                'customer_type' => $customer?->getMorphClass(),
                'amount' => data_get($data, 'amount'),
                'shipping_amount' => data_get($data, 'shipping_amount'),
                'tax_amount' => data_get($data, 'tax_amount'),
                'discount_amount' => data_get($data, 'discount_amount'),
                'total_amount' => data_get($data, 'total_amount', data_get($data, 'amount', 0) + data_get($data, 'shipping_amount', 0) + data_get($data, 'tax_amount', 0) - data_get($data, 'discount_amount', 0)),
                'tendered_amount' => data_get($data, 'tendered_amount'),
                'balance_amount' => data_get($data, 'balance_amount'),
            ];

            $order = Order::query()->create($orderAttributes);

            collect($data['items'])->each(function ($item) use ($order, $data) {
                $this->orderItemService->create([
                    ...$item,
                    'order' => $order,
                    'check_stock_availability' => data_get($data, 'check_stock_availability'),
                ]);
            });

            return $order;
        });
    }

    public function update(Order $order, array $data): bool
    {

        return DB::transaction(function () use ($order, $data) {

            $customer = $this->clientService->upsert(data_get($data, 'customer', []));

            $attributes = [
                'store_id' => data_get($data, 'store'),
                'customer_type' => $customer?->getMorphClass(),
                'customer_id' => $customer?->id,
                'amount' => data_get($data, 'amount'),
                'shipping_amount' => data_get($data, 'shipping_amount'),
                'total_amount' => data_get($data, 'total_amount'),
                'tendered_amount' => data_get($data, 'tendered_amount'),
                'balance_amount' => data_get($data, 'balance_amount'),
            ];

            $result = $order->update($attributes);

            if ($result) {

                $order->items()->forceDelete();

                collect($data['items'])->each(function ($item) use ($order, $data) {
                    $this->orderItemService->create([
                        ...$item,
                        'order' => $order,
                        'check_stock_availability' => data_get($data, 'check_stock_availability'),
                    ]);
                });
            }

            return true;
        });
    }

    public function partialUpdate(Order $order, array $data): ?bool
    {

        return DB::transaction(function () use ($order, $data) {

            $attributes = [
                'author_user_id' => data_get($data, 'author', $order->author_user_id),
                'store_id' => data_get($data, 'store', $order->store_id),
                'order_status' => data_get($data, 'order_status', $order->order_status),
                'payment_status' => data_get($data, 'payment_status', $order->payment_status),
                'fulfillment_status' => data_get($data, 'fulfillment_status', $order->fulfillment_status),
                'shipping_status' => data_get($data, 'shipping_status', $order->shipping_status),
                'refund_status' => data_get($data, 'refund_status', $order->refund_status),
                'channel' => data_get($data, 'channel', $order->channel),
                'notes' => data_get($data, 'notes', $order->notes),
            ];

            $order->fill($attributes);

            if ($order->isDirty()) {

                return $order->save();
            }

            return false;
        });
    }

    public function delete(Order $order, bool $forever = false): ?bool
    {
        return $forever ? $order->forceDelete() : $order->delete();
    }

    public function updateStatus(Order $order, OrderStatus $status): bool
    {
        return $order->update(['order_status' => $status->value]);
    }

    public function markComplete(Order $order): bool
    {
        $totalPayment = $order->payments()->status(PaymentStatus::PAID)->sum('amount');

        if (! $order->canMarkCompleted()) {

            throw new CustomException('Only pending and processing orders can be marked as completed.');
        }

        if ($totalPayment < $order->total_amount) {

            throw new CustomException('Order cannot be marked as completed because it has not been fully paid for.');
        }

        return $this->updateStatus($order, OrderStatus::COMPLETED);
    }

    public function importRow(array $data)
    {
        $values = [
            'customer_type' => data_get($data, 'customer')?->getMorphClass(),
            'customer_id' => data_get($data, 'customer')?->id,
            'store_id' => data_get($data, 'store')?->id,
            'author_user_id' => data_get($data, 'author')?->id,
            'amount' => data_get($data, 'amount'),
            'shipping_amount' => data_get($data, 'shipping_amount'),
            'total_amount' => data_get($data, 'total_amount'),
            'tax_amount' => data_get($data, 'tax_amount'),
            'discount_amount' => data_get($data, 'discount_amount'),
            'tendered_amount' => data_get($data, 'tendered_amount'),
            'balance_amount' => data_get($data, 'balance_amount'),
            'order_status' => data_get($data, 'order_status', OrderStatus::PENDING->value),
            'fulfillment_status' => data_get($data, 'fulfillment_status', null),
            'shipping_status' => data_get($data, 'shipping_status', null),
            'refund_status' => data_get($data, 'refund_status', null),
            'channel' => data_get($data, 'channel', null),
            'refferal_code' => data_get($data, 'refferal_code', null),
            'notes' => data_get($data, 'notes', null),
        ];

        $order = Order::where('reference', data_get($data, 'reference'))->first();

        if ($order) {

            $order->fill($values);

            if ($order->isDirty()) {

                $order->save();
            }
        } else {

            Order::create($values);
        }
    }

    public function generateReceipt(Order $order)
    {
        $settings = $this->settingsService->get(group: null);

        $order->loadMissing(['items.item', 'customer', 'store', 'author', 'payments.paymentMethod', 'completePayments.paymentMethod']);

        $data = compact('order', 'settings');

        $pdf = Pdf::setPaper([0, 0, 204, 1000], 'potrait')->loadView('printouts.order-receipt', $data);

        $pdf->render();

        return $pdf->output();
    }

    public function generateInvoice(Order $order)
    {
        $settings = $this->settingsService->get(group: null);

        $order->loadMissing(['items.item', 'customer', 'store', 'author', 'payments.paymentMethod', 'completePayments.paymentMethod']);

        $data = compact('order', 'settings');

        $pdf = Pdf::setPaper('A4')->loadView('printouts.order-invoice', $data);

        $pdf->render();

        return $pdf->output();
    }

    public function generateOrdersReportsDetails(array $filters = [])
    {

        $settings = $this->settingsService->get(group: null);

        $orders = $this->get(...$filters, perPage: null, with: ['items.item', 'customer', 'store', 'author', 'payments.paymentMethod'], withTrashed: true);

        $statistics = $this->getStatistics($filters);

        $paymentsAggregates = $this->orderPaymentService->getRaw(
            store: data_get($filters, 'store'),
            author: data_get($filters, 'author'),
            status: data_get($filters, 'status'),
            to: data_get($filters, 'to'),
            from: data_get($filters, 'from')
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

        $data = compact('orders', 'settings', 'statistics', 'filters', 'methods');

        $pdf = Pdf::setPaper('A4', 'landscape')->loadView('printouts.orders-reports-details', $data);

        $pdf->render();

        return $pdf->output();
    }

    public function generateAndSaveReceipt(Order $order): void
    {

        $collection = Order::RECEIPT_MEDIA_COLLECTION;

        $latestReceipt = $order->getMedia($collection)->last();

        if ($latestReceipt && $latestReceipt->created_at?->isAfter($order->updated_at)) {
            return;
        }

        $pdfContent = $this->generateReceipt($order);

        $filename = str($order->reference)
            ->append('-')
            ->append('receipt')
            ->slug()
            ->append('.pdf')
            ->value();

        $order->clearMediaCollection($collection);

        $order->addMedia($pdfContent)->usingFileName($filename)->withCustomProperties(['generated_at' => now()])->toMediaCollection($collection);
    }

    public function getStatistics(array $data = []): array
    {
        return [
            'total_orders' => $this->getOrdersCount(...$data),
            'total_sales' => $this->getTotalSales(...$data),
            'largest_order' => $this->getLargestOrder(...$data),
            'smallest_order' => $this->getSmallestOrder(...$data),
            'total_number_of_items' => $this->getTotalNumberOfItems(...$data),
            'average_order_value' => $this->getAverageOrderValue(...$data),
            'order_status_distribution' => $this->getOrderStatusDistribution(...$data),
            'last_seven_days_orders_count' => $this->getLastSevenDaysOrdersCount(store: data_get($data, 'store'), author: data_get($data, 'author'), status: data_get($data, 'status')),
        ];
    }

    public function getTotalNumberOfItems(
        ?Store $store = null,
        ?User $author = null,
        ?string $status = null,
        ?string $to = null,
        ?string $from = null
    ): int {
        return Order::query()
            ->when($author, function ($defaultQuery, $author) {
                $defaultQuery->where('author_user_id', $author->id);
            })
            ->when($store, function ($defaultQuery, $store) {
                $defaultQuery->where('store_id', $store->id);
            })
            ->when($status, function ($defaultQuery, $status) {
                $defaultQuery->where('order_status', $status);
            })
            ->when($to, function ($defaultQuery, $to) {
                $defaultQuery->whereDate('created_at', '<=', $to);
            })
            ->when($from, function ($defaultQuery, $from) {
                $defaultQuery->whereDate('created_at', '>=', $from);
            })
            ->withCount('items')
            ->get()
            ->sum('items_count');
    }

    public function getLastSevenDaysOrdersCount(
        ?Store $store = null,
        ?User $author = null,
        ?string $status = null,
    ): array {

        $orderQuery = Order::query();

        $orderQuery->when($author, function ($defaultQuery, $author) {
            $defaultQuery->where('author_user_id', $author->id);
        });

        $orderQuery->when($store, function ($defaultQuery, $store) {
            $defaultQuery->where('store_id', $store->id);
        });

        $orderQuery->when($status, function ($defaultQuery, $status) {
            $defaultQuery->where('order_status', $status);
        });

        $results = $orderQuery
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->pluck('count', 'date')
            ->all();

        $results = collect(range(0, 6))
            ->map(fn ($day) => now()->subDays($day)->format('Y-m-d'))
            ->map(fn ($date) => ['date' => $date, 'day' => Carbon::parse($date)->dayName, 'count' => $results[$date] ?? 0])
            ->sortBy('date')
            ->all();

        return array_values($results);
    }

    public function getOrderStatusDistribution(
        ?Store $store = null,
        ?User $author = null,
        ?string $status = null,
        ?string $to = null,
        ?string $from = null
    ): array {
        $orderQuery = Order::query();

        $orderQuery->when($author, function ($defaultQuery, $author) {
            $defaultQuery->where('author_user_id', $author->id);
        });

        $orderQuery->when($store, function ($defaultQuery, $store) {
            $defaultQuery->where('store_id', $store->id);
        });

        $orderQuery->when($status, function ($defaultQuery, $status) {
            $defaultQuery->where('order_status', $status);
        });

        $orderQuery->when($to, function ($defaultQuery, $to) {
            $defaultQuery->whereDate('created_at', '<=', $to);
        });

        $orderQuery->when($from, function ($defaultQuery, $from) {
            $defaultQuery->whereDate('created_at', '>=', $from);
        });

        $results = $orderQuery
            ->selectRaw('order_status, count(*) as count')
            ->groupBy('order_status')
            ->pluck('count', 'order_status')
            ->toArray();

        return collect(OrderStatus::cases())
            ->map(function ($status) use ($results) {
                return [
                    'status' => $status->value,
                    'label' => $status->label(),
                    'count' => $results[$status->value] ?? 0,
                    'color' => $status->color(),
                ];
            })
            ->all();
    }

    public function getAverageOrderValue(
        ?Store $store = null,
        ?User $author = null,
        ?string $status = null,
        ?string $to = null,
        ?string $from = null
    ): float {
        $orderQuery = DB::table('orders');

        $orderQuery->when($author, function ($defaultQuery, $author) {
            $defaultQuery->where('author_user_id', $author->id);
        });

        $orderQuery->when($store, function ($defaultQuery, $store) {
            $defaultQuery->where('store_id', $store->id);
        });

        $orderQuery->when($status, function ($defaultQuery, $status) {
            $defaultQuery->where('order_status', $status);
        });

        $orderQuery->when($to, function ($defaultQuery, $to) {
            $defaultQuery->whereDate('created_at', '<=', $to);
        });

        $orderQuery->when($from, function ($defaultQuery, $from) {
            $defaultQuery->whereDate('created_at', '>=', $from);
        });

        $result = $orderQuery->selectRaw('AVG(total_amount) as average_order_value')->first();

        return floatval($result->average_order_value);
    }

    public function getLargestOrder(
        ?Store $store = null,
        ?User $author = null,
        ?string $status = null,
        ?string $to = null,
        ?string $from = null
    ): ?Order {
        $orderQuery = Order::query();

        $orderQuery->when($author, function ($defaultQuery, $author) {
            $defaultQuery->where('author_user_id', $author->id);
        });

        $orderQuery->when($store, function ($defaultQuery, $store) {
            $defaultQuery->where('store_id', $store->id);
        });

        $orderQuery->when($status, function ($defaultQuery, $status) {
            $defaultQuery->where('order_status', $status);
        });

        $orderQuery->when($to, function ($defaultQuery, $to) {
            $defaultQuery->whereDate('created_at', '<=', $to);
        });

        $orderQuery->when($from, function ($defaultQuery, $from) {
            $defaultQuery->whereDate('created_at', '>=', $from);
        });

        return $orderQuery->orderByDesc('total_amount')->first();
    }

    public function getSmallestOrder(
        ?Store $store = null,
        ?User $author = null,
        ?string $status = null,
        ?string $to = null,
        ?string $from = null
    ): ?Order {
        $orderQuery = Order::query();

        $orderQuery->when($author, function ($defaultQuery, $author) {
            $defaultQuery->where('author_user_id', $author->id);
        });

        $orderQuery->when($store, function ($defaultQuery, $store) {
            $defaultQuery->where('store_id', $store->id);
        });

        $orderQuery->when($status, function ($defaultQuery, $status) {
            $defaultQuery->where('order_status', $status);
        });

        $orderQuery->when($to, function ($defaultQuery, $to) {
            $defaultQuery->whereDate('created_at', '<=', $to);
        });

        $orderQuery->when($from, function ($defaultQuery, $from) {
            $defaultQuery->whereDate('created_at', '>=', $from);
        });

        return $orderQuery->orderBy('total_amount')->first();
    }

    public function getOrdersCount(
        ?Store $store = null,
        ?User $author = null,
        ?string $status = null,
        ?string $to = null,
        ?string $from = null

    ) {
        $orderQuery = Order::query();

        $orderQuery->when($author, function ($defaultQuery, $author) {
            $defaultQuery->where('author_user_id', $author->id);
        });

        $orderQuery->when($store, function ($defaultQuery, $store) {
            $defaultQuery->where('store_id', $store->id);
        });

        $orderQuery->when($status, function ($defaultQuery, $status) {
            $defaultQuery->where('order_status', $status);
        });

        $orderQuery->when($to, function ($defaultQuery, $to) {
            $defaultQuery->whereDate('created_at', '<=', $to);
        });

        $orderQuery->when($from, function ($defaultQuery, $from) {
            $defaultQuery->whereDate('created_at', '>=', $from);
        });

        return $orderQuery->count();
    }

    public function getTotalSales(
        ?Store $store = null,
        ?User $author = null,
        ?string $status = null,
        ?string $to = null,
        ?string $from = null
    ): float {
        return Order::query()
            ->when($author, function ($defaultQuery, $author) {
                $defaultQuery->where('author_user_id', $author->id);
            })
            ->when($store, function ($defaultQuery, $store) {
                $defaultQuery->where('store_id', $store->id);
            })
            ->when($status, function ($defaultQuery, $status) {
                $defaultQuery->where('order_status', $status);
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

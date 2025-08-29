<?php

namespace App\Http\Controllers\Backoffice;

use App\Exceptions\CustomException;
use App\Exports\OrderExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\PartiallyUpdateOrderRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\OrderImport;
use App\Models\Enums\OrderStatus;
use App\Models\Enums\TransactionMethod;
use App\Models\Order;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ExcelService;
use App\Services\MpesaService;
use App\Services\OrderService;
use App\Services\PaymentMethodService;
use App\Services\PaymentService;
use App\Services\ProductService;
use App\Services\SettingsService;
use App\Services\StoreService;
use App\Services\TaxService;
use App\Services\TransactionService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    private ?array $settings = null;

    private ?User $currentUser = null;

    private ?Store $currentStore = null;

    public function __construct(
        private readonly OrderService $orderService,
        private readonly StoreService $storeService,
        private readonly ProductService $productService,
        private readonly SettingsService $settingsService,
        private readonly PaymentMethodService $paymentMethodService,
        private readonly UserService $userService,
        private readonly PaymentService $paymentService,
        private readonly TransactionService $transactionService,
        private readonly MpesaService $mpesaService,
        private readonly TaxService $taxService,
    ) {
        $this->currentUser = request()->user();

        $this->currentStore = $this->storeService->getUserStore($this->currentUser);

        $this->settings = $this->settingsService->get('payment');
    }

    public function index(Request $request): Response
    {
        $params = $request->only('query', 'store', 'status', 'withOutstandingAmount', 'from', 'to');

        $filters = [...$params];

        if ($this->currentUser->hasRole('staff')) {

            $filters['store'] = $this->currentStore;
        }

        if (! is_null($storeId = data_get($filters, 'store')) && $this->currentUser->hasRole('admin')) {

            $filters['store'] = Store::query()->find($storeId);
        }

        $filters['withOutstandingAmount'] = $request->boolean('withOutstandingAmount', false);

        if ($from = data_get($params, 'from')) {

            $filters['from'] = $from;
        }

        if ($to = data_get($params, 'to')) {

            $filters['to'] = $to;
        }

        $orders = $this->orderService->get(...$filters);

        $statuses = OrderStatus::labelledOptions();

        $stores = $this->storeService->get(perPage: null)->map(fn ($store) => [
            'id' => $store->id,
            'name' => $store->name,
        ]);

        activity()->log('Viewed orders list');

        return Inertia::render('backoffice/orders/IndexPage', compact('orders', 'params', 'statuses', 'stores'));
    }

    public function create(): Response
    {
        return Inertia::render('backoffice/orders/CreatePage', $this->getOrderFormData());
    }

    public function store(StoreOrderRequest $storeOrderRequest): RedirectResponse
    {
        $data = $storeOrderRequest->validated();

        $data['author_user_id'] = request()->user()->id;

        $data['check_stock_availability'] = true;

        try {

            $order = DB::transaction(function () use ($data) {

                $order = $this->orderService->create($data);

                if ($paymentsData = data_get($data, 'payments')) {

                    collect($paymentsData)->each(function ($paymentItem) use ($order) {

                        $payment = $this->paymentService->createOrderPayment($order, [
                            ...$paymentItem,
                            'author_user_id' => request()->user()->id,
                            'payment_method_id' => data_get($paymentItem, 'payment_method', null),
                        ]);

                        $payment->loadMissing('paymentMethod');

                        $isMpesa = data_get($this->settings, 'mpesa_payment_method') === $payment->paymentMethod?->id;

                        if ($isMpesa) {

                            $phoneNumber = data_get($paymentItem, 'phone_number', $payment->phone_number);

                            if (is_null($phoneNumber)) {

                                throw new CustomException('Phone number is required for MPESA payments');
                            }

                            $transaction = $this->transactionService->createPaymentTransaction($payment, [
                                ...$paymentItem,
                                'phone' => $phoneNumber,
                                'author_user_id' => request()->user()->id,
                                'transaction_method' => data_get($paymentItem, 'transaction_method', TransactionMethod::MPESA->value),
                            ]);

                            $this->mpesaService->saloonTriggerStkPush([
                                'amount' => $payment->amount,
                                'phone' => $phoneNumber,
                                'reference' => Transaction::generateTransactionReference(),
                                'description' => "Order #{$order->reference} Payment",
                            ], $transaction);
                        }
                    });
                }

                $order->refresh();

                $order->loadSum('completePayments', 'amount');

                if ($order->complete_payments_sum_amount >= $order->total_amount) {

                    $this->orderService->markComplete($order);
                }

                return $order->fresh();
            });

            activity()
                ->performedOn($order)
                ->log("Created order with reference: {$order->reference}");

            return $this->sendSuccessRedirect('Order created successfully', route('backoffice.orders.show', $order->id));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Failed to create order', $throwable);
        }
    }

    public function show(Order $order): Response
    {
        $order->load(['items.item', 'customer', 'author', 'user', 'store', 'payments.paymentMethod']);

        $order->can_mark_completed = $order->canMarkCompleted();

        $paymentMethods = $this->paymentMethodService->get(perPage: null)
            ->map(fn ($method) => [
                'value' => $method->id,
                'label' => $method->name,
            ]);

        activity()->performedOn($order)->log("Viewed order with reference: {$order->reference}");

        return Inertia::render('backoffice/orders/ShowPage', compact('order', 'paymentMethods'));
    }

    public function edit(Order $order): Response
    {
        $order->load(['items.item', 'customer', 'author', 'user', 'store', 'payments.paymentMethod']);

        return Inertia::render('backoffice/orders/EditPage', array_merge(
            compact('order'),
            $this->getOrderFormData()
        ));
    }

    public function partialUpdate(PartiallyUpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();

        try {

            $this->orderService->partialUpdate($order, $data);

            activity()
                ->performedOn($order)
                ->log("Partially updated the order with reference: {$order->reference}");

            return $this->sendSuccessRedirect('Order updated successfully', url()->previous());
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to update order', $throwable);
        }
    }

    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();

        $data['check_stock_availability'] = true;

        try {

            $this->orderService->update($order, $data);

            activity()
                ->performedOn($order)
                ->log("Updated order with reference: {$order->reference}");

            return $this->sendSuccessRedirect('Order updated successfully', route('backoffice.orders.show', $order->id));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Failed to update order', $throwable);
        }
    }

    public function destroy(Order $order): RedirectResponse
    {
        try {

            $this->orderService->delete($order);

            activity()
                ->performedOn($order)
                ->log("Deleted order with reference: {$order->reference}");

            return $this->sendSuccessRedirect('Order deleted successfully', route('backoffice.orders.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Failed to delete order', $throwable);
        }
    }

    public function export(Request $request)
    {

        activity()->log('Exported orders');

        $data = $request->only('query', 'limit');

        $export = new OrderExport($data);

        return $export->download(Order::getExportFilename());
    }

    public function import(): Response
    {
        return Inertia::render('backoffice/orders/ImportPage');
    }

    public function processImport(ImportRequest $importRequest): RedirectResponse
    {
        $data = $importRequest->validated();

        try {

            $this->robustImport(new OrderImport, $data['file'], 'orders', 'orders');

            activity()->log("Imported orders from file: {$data['file']->getClientOriginalName()}");

            return $this->sendSuccessRedirect('Imported orders successfully.', route('backoffice.orders.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Failed to import orders.', $throwable);
        }
    }

    private function getOrderFormData(): array
    {
        /** @var User $currentUser */
        $currentUser = request()->user();

        $stores = $this->storeService->get(perPage: null, user: ['id' => $currentUser->id, 'staff' => $currentUser->hasRole('staff')])->map(fn ($store) => [
            'id' => $store->id,
            'name' => $store->name,
        ]);

        $primaryTax = $this->taxService->fetchPrimaryTax();

        return compact('stores', 'primaryTax');
    }

    public function invoice(Order $order)
    {
        activity()
            ->performedOn($order)
            ->log("Generated invoice for order with reference: {$order->reference}");

        $pdfContent = $this->orderService->generateInvoice($order);

        $filename = str($order->reference)
            ->append('-')
            ->append('invoice')
            ->slug()
            ->append('.pdf')
            ->value();

        $contentDisposition = 'inline; filename="'.$filename.'"';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', $contentDisposition);
    }

    public function receipt(Order $order)
    {
        activity()
            ->performedOn($order)
            ->log("Generated receipt for order with reference: {$order->reference}");

        $pdfContent = $this->orderService->generateReceipt($order);

        $filename = str($order->reference)
            ->append('-')
            ->append('receipt')
            ->slug()
            ->append('.pdf')
            ->value();

        $contentDisposition = 'inline; filename="'.$filename.'"';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', $contentDisposition);
    }

    public function markComplete(Order $order): RedirectResponse
    {
        try {

            activity()
                ->performedOn($order)
                ->log("Marked order with reference: {$order->reference} as completed");

            $this->orderService->markComplete($order);

            return $this->sendSuccessRedirect('Order marked as completed successfully', url()->previous());
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect($throwable->getMessage(), $throwable);
        }
    }

    public function reports(Request $request): Response
    {
        $params = $request->only('store', 'status', 'author', 'from', 'to');

        $filters = [];

        if (! is_null($this->currentStore) && $this->currentUser->hasRole('staff')) {

            $filters['store'] = $this->currentStore;
        }

        if (($storeId = data_get($params, 'store')) && $this->currentUser->hasRole('admin')) {

            $filters['store'] = Store::query()->find($storeId);
        }

        if ($authorId = data_get($params, 'author')) {

            $filters['author'] = User::query()->find($authorId);
        }

        if ($status = data_get($params, 'status')) {

            $filters['status'] = $status;
        }

        if ($from = data_get($params, 'from')) {

            $filters['from'] = $from;
        } else {

            $filters['from'] = now()->toDateString();
        }

        if ($to = data_get($params, 'to')) {

            $filters['to'] = $to;
        } else {

            $filters['to'] = now()->toDateString();
        }

        $stores = $this->storeService->get(perPage: null)->map(fn ($store) => [
            'id' => $store->id,
            'name' => $store->name,
        ]);

        $authors = $this->userService->getOfficials()->map(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
        ]);

        $statistics = $this->orderService->getStatistics($filters);

        $orderStatuses = OrderStatus::labelledOptions();

        return Inertia::render('backoffice/orders/ReportsPage', compact('stores', 'authors', 'orderStatuses', 'params', 'statistics'));
    }

    public function detailedReport(Request $request)
    {
        $params = $request->only('store', 'status', 'author', 'from', 'to');

        $filters = [];

        if (! is_null($this->currentStore) && $this->currentUser->hasRole('staff')) {

            $filters['store'] = $this->currentStore;
        }

        if (($storeId = data_get($params, 'store')) && $this->currentUser->hasRole('admin')) {

            $filters['store'] = Store::query()->find($storeId);
        }

        if ($authorId = data_get($params, 'author')) {

            $filters['author'] = User::query()->find($authorId);
        }

        if ($status = data_get($params, 'status')) {

            $filters['status'] = $status;
        }

        if ($from = data_get($params, 'from')) {

            $filters['from'] = $from;
        } else {

            $filters['from'] = now()->toDateString();
        }

        if ($to = data_get($params, 'to')) {

            $filters['to'] = $to;
        } else {

            $filters['to'] = now()->toDateString();
        }

        $pdfContent = $this->orderService->generateOrdersReportsDetails($filters);

        $filename = str('orders-reports-details')
            ->append('-')
            ->append(date('Y-m-d'))
            ->slug()
            ->append('.pdf')
            ->value();

        $contentDisposition = 'inline; filename="'.$filename.'"';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', $contentDisposition);
    }
}

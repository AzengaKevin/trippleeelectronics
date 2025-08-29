<?php

namespace App\Http\Controllers\Backoffice;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Requests\POSOrderRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Models\Enums\TransactionMethod;
use App\Models\Order;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ClientService;
use App\Services\IndividualService;
use App\Services\MpesaService;
use App\Services\OrderService;
use App\Services\PaymentMethodService;
use App\Services\PaymentService;
use App\Services\POSStoreService;
use App\Services\ProductService;
use App\Services\SettingsService;
use App\Services\StoreService;
use App\Services\TaxService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class POSController extends Controller
{
    use RedirectWithFeedback;

    private ?array $settings = null;

    private ?Store $store = null;

    private ?User $user = null;

    public function __construct(
        private readonly OrderService $orderService,
        private readonly IndividualService $individualService,
        private readonly ClientService $clientService,
        private readonly StoreService $storeService,
        private readonly ProductService $productService,
        private readonly POSStoreService $posStoreService,
        private readonly MpesaService $mpesaService,
        private readonly PaymentService $paymentService,
        private readonly TransactionService $transactionService,
        private readonly PaymentMethodService $paymentMethodService,
        private readonly SettingsService $settingsService,
        private readonly TaxService $taxService,
    ) {

        $this->user = request()->user();

        $this->store = $this->user->stores()->with('paymentMethods')->first();

        $this->settings = $this->settingsService->get('payment');

        if (is_null($this->store)) {

            $storeData = $this->posStoreService->getCurrentPOSStore();

            if ($storeData) {
                $this->store = Store::query()->with('paymentMethods')->find($storeData['id']);
            }
        }
    }

    public function show(Request $request)
    {
        $params = $request->only('query', 'reference', 'order', 'perPage');

        $stores = $this->storeService->get(perPage: null)->map(fn ($store) => $store->only(['id', 'name']));

        $order = null;

        $latestOrder = null;

        if ($request->has('order')) {

            $order = Order::query()
                ->with(['items.item', 'customer', 'author', 'payments', 'payments.transaction'])
                ->findOrFail($request->get('order'));
        }

        if ($request->has('reference')) {

            $order = Order::query()
                ->with(['items.item', 'customer', 'author', 'user', 'store', 'payments.paymentMethod', 'payments.transaction'])
                ->where('reference', $request->get('reference'))
                ->first();
        }

        if ($order) {

            $order->can_mark_completed = $order->canMarkCompleted();
        }

        if (is_null($order)) {

            $latestOrder = Order::query()
                ->with(['items.item', 'customer', 'author', 'payments.paymentMethod', 'payments.transaction'])
                ->where('store_id', $this->store?->id)
                ->latest()
                ->first();
        }

        $orders = $this->orderService->get(limit: 20, store: $this->store, perPage: null, with: ['items.item']);

        $store = $this->posStoreService->getCurrentPOSStore();

        activity()->log("Accessed the backoffice POS page {$this->store->name}", [
            'store_id' => $this->store->id,
            'order_reference' => $order?->reference,
        ]);

        return Inertia::render('backoffice/POSPage', [
            'orders' => $orders,
            'stores' => $stores,
            'params' => $params,
            'order' => $order,
            'latestOrder' => $latestOrder,
            'primaryTax' => $this->taxService->fetchPrimaryTax(),
        ]);
    }

    public function process(POSOrderRequest $posOrderRequest)
    {
        $data = $posOrderRequest->validated();

        $order = null;

        if ($reference = request()->get('reference')) {

            $order = Order::query()->where('reference', $reference)->first();
        }

        try {

            $order = DB::transaction(function () use ($data, $order, $reference) {

                $data['author_user_id'] = request()->user()->id;

                $data['check_stock_availability'] = true;

                $data['customer']['type'] = $data['customer']['type'] ?? 'individual';

                if (! $order) {

                    $order = $this->orderService->create($data);
                } else {

                    $this->orderService->update($order, $data);
                }

                $order->loadMissing('customer');

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

                if ($reference === $order->reference) {

                    activity()->log("Updated order {$order->reference} in the backoffice POS", [
                        'store_id' => $order->store_id,
                        'order_reference' => $order->reference,
                    ]);
                } else {
                    activity()->log("Created order {$order->reference} in the backoffice POS", [
                        'store_id' => $order->store_id,
                        'order_reference' => $order->reference,
                    ]);
                }

                $similarOrder = Order::query()->where([
                    ['author_user_id', $order->author_user_id],
                    ['customer_id', $order->customer_id],
                    ['order_status', $order->status],
                    ['id', '!=', $order->id],
                    ['store_id', $order->store_id],
                ])->first();

                if ($similarOrder && $order->isSimilar($similarOrder)) {

                    Log::info("Found similar order {$similarOrder->reference} for order {$order->reference}");

                    throw new CustomException('A similar order already exists');
                }

                return $order;
            });

            $redirectUrl = $order->isCompleted() ? route('backoffice.pos') : route('backoffice.pos', ['reference' => $order->reference]);

            return $this->sendSuccessRedirect('Your have successfully created an order', $redirectUrl);
        } catch (\Throwable $th) {

            return $this->sendErrorRedirect('Checkout Failed!', $th);
        }
    }
}

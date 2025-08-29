<?php

namespace App\Http\Controllers\Backoffice;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderPaymentRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Models\Enums\TransactionMethod;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\MpesaService;
use App\Services\OrderService;
use App\Services\PaymentMethodService;
use App\Services\PaymentService;
use App\Services\SettingsService;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderPaymentController extends Controller
{
    use RedirectWithFeedback;

    private ?array $settings = null;

    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly TransactionService $transactionService,
        private readonly MpesaService $mpesaService,
        private readonly PaymentMethodService $paymentMethodService,
        private readonly SettingsService $settingsService,
        private readonly OrderService $orderService,
    ) {
        $this->settings = $this->settingsService->get('payment');
    }

    public function index(Order $order)
    {
        $order->loadMissing(['payments', 'payments.author', 'payments.payer', 'payments.payee', 'payments.paymentMethod', 'customer']);

        $paymentMethods = $this->paymentMethodService->get(perPage: null)->map(fn ($paymentMethod) => [
            'value' => $paymentMethod->id,
            'label' => $paymentMethod->name,
        ]);

        activity()
            ->performedOn($order)
            ->withProperties(['order' => $order->reference])
            ->log('Viewed order payments');

        return Inertia::render('backoffice/orders/payments/IndexPage', [
            'order' => $order,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function store(StoreOrderPaymentRequest $storeOrderPaymentRequest, Order $order): RedirectResponse
    {
        $data = $storeOrderPaymentRequest->validated();

        try {

            $data['author_user_id'] = request()->user()->id;

            DB::transaction(function () use ($data, $order) {

                collect(data_get($data, 'payments'))->each(function ($paymentItem) use ($order) {

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

                $order->refresh();

                $order->loadSum('completePayments', 'amount');

                if ($order->complete_payments_sum_amount >= $order->total_amount) {

                    $this->orderService->markComplete($order);
                }

                activity()
                    ->performedOn($order)
                    ->withProperties(['order' => $order->reference])
                    ->log('Created order payment(s)');
            });

            return $this->sendSuccessRedirect('Your have successfully created an order payment', url()->previous());
        } catch (\Throwable $th) {

            return $this->sendErrorRedirect('Creating order payment Failed!', $th);
        }
    }
}

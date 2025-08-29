<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Models\Enums\TransactionMethod;
use App\Models\ItemCategory;
use App\Models\Order;
use App\Models\Transaction;
use App\Notifications\OnlineOrderReceivedNotification;
use App\Services\ItemCategoryService;
use App\Services\ItemService;
use App\Services\MpesaService;
use App\Services\OrderService;
use App\Services\PaymentMethodService;
use App\Services\PaymentService;
use App\Services\ServiceService;
use App\Services\SettingsService;
use App\Services\TransactionService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class CheckoutController extends Controller
{
    use RedirectWithFeedback;

    private ?Role $adminRole = null;

    private ?Role $staffRole = null;

    public function __construct(
        private readonly SettingsService $settingsService,
        private readonly ItemCategoryService $itemCategoryService,
        private readonly ItemService $itemService,
        private readonly ServiceService $serviceService,
        private readonly OrderService $orderService,
        private readonly MpesaService $mpesaService,
        private readonly PaymentService $paymentService,
        private readonly TransactionService $transactionService,
        private readonly PaymentMethodService $paymentMethodService,
        private readonly UserService $userService,
    ) {}

    public function show()
    {
        return inertia('checkout/ShowPage', [
            ...$this->getStandardData(),
        ]);
    }

    public function store(CheckoutRequest $checkoutRequest): RedirectResponse
    {
        $data = $checkoutRequest->validated();

        try {

            $order = DB::transaction(function () use ($data) {

                $data['customer']['type'] = data_get($data, 'customer.type') ?? 'individual';

                $data['customer']['id'] = data_get($data, 'customer.name');

                $data['total_amount'] = data_get($data, 'amount');

                $currentUser = request()->user();

                if ($currentUser) {

                    $data['author_user_id'] = $currentUser->id;

                    $data['user_id'] = $currentUser->id;
                }

                $order = $this->orderService->create($data);

                $phoneNumber = data_get($data, 'customer.phone', data_get($order, 'customer.phone'));

                $mpesaPaymentMethod = $this->paymentMethodService->getMpesaPaymentMethod();

                $payment = $this->paymentService->createOrderPayment($order, [
                    ...$data,
                    'payment_method_id' => $mpesaPaymentMethod?->id,
                ]);

                $transaction = $this->transactionService->createPaymentTransaction($payment, [
                    ...$data,
                    'phone' => $phoneNumber,
                    'transaction_method' => TransactionMethod::MPESA->value,
                ]);

                $this->mpesaService->saloonTriggerStkPush([
                    'amount' => $payment->amount,
                    'phone' => $phoneNumber,
                    'reference' => Transaction::generateTransactionReference(),
                    'description' => "Order #{$order->reference} Payment",
                ], $transaction);

                $notifiables = $this->userService->getOfficials();

                Notification::send($notifiables, new OnlineOrderReceivedNotification($order));

                return $order->fresh();
            });

            return $this->sendSuccessRedirect("Your order has been received we'll verify everything get back to you within 5 mins.", route('checkout.order-received', compact('order')));
        } catch (\Throwable $th) {

            return $this->sendErrorRedirect('Checkout Failed!', $th);
        }
    }

    public function orderReceived(Order $order): Response
    {

        $order->loadMissing('items.item');

        return Inertia::render('checkout/OrderReceivedPage', [
            'order' => $order,
            ...$this->getStandardData(),
        ]);
    }

    private function getStandardData(): array
    {

        $categories = $this->itemCategoryService->get(perPage: null, limit: 20)->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'image_url' => $category->getFirstMediaUrl(),
            ];
        });

        $settings = $this->settingsService->get();

        $treeCategories = ItemCategory::get()->toTree();

        $services = $this->serviceService->get(perPage: null, limit: 10)->map(function ($service) {
            return [
                'id' => $service->id,
                'title' => $service->title,
                'description' => $service->description,
                'image_url' => $service->getFirstMediaUrl(),
            ];
        });

        return [
            'categories' => $categories,
            'settings' => $settings,
            'treeCategories' => $treeCategories,
            'services' => $services,
        ];
    }
}

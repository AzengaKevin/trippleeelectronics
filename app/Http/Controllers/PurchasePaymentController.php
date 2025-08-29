<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchasePaymentRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Models\Purchase;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class PurchasePaymentController extends Controller
{
    use RedirectWithFeedback;

    public function __construct(private readonly PaymentService $paymentService) {}

    public function store(StorePurchasePaymentRequest $request, Purchase $purchase): RedirectResponse
    {
        $data = $request->validated();

        try {

            $data['author_user_id'] = request()->user()->id;

            DB::transaction(function () use ($data, $purchase) {

                collect(data_get($data, 'payments'))->each(function ($paymentItem) use ($purchase) {

                    $this->paymentService->createPurchasePayment($purchase, [
                        ...$paymentItem,
                        'author_user_id' => request()->user()->id,
                        'payment_method_id' => data_get($paymentItem, 'payment_method', null),
                    ]);
                });

                activity()
                    ->performedOn($purchase)
                    ->withProperties(['order' => $purchase->reference])
                    ->log('Created purchase payment(s)');
            });

            return $this->sendSuccessRedirect('Your have successfully created purchase payment(s)', url()->previous());
        } catch (\Throwable $th) {

            return $this->sendErrorRedirect('Creating purchase payment(s) Failed!', $th);
        }
    }
}

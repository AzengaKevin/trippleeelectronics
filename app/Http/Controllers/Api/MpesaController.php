<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class MpesaController extends Controller
{
    public function __construct(
        private TransactionService $transactionService,
    ) {}

    public function validate(Request $request)
    {
        activity()->withProperties($request->all())->log('Validation Request');

        return response()->json(['ResultCode' => '0', 'ResultDesc' => 'Accepted']);
    }

    public function confirm(Request $request)
    {
        activity()->withProperties($request->all())->log('Confirmation Request');

        return response()->json(['ResultCode' => '0', 'ResultDesc' => 'Accepted']);
    }

    public function timeout(Request $request)
    {
        activity()->withProperties($request->all())->log('Timeout Request');

        return response()->json(['ResultCode' => '0', 'ResultDesc' => 'Accepted']);
    }

    public function result(Request $request)
    {
        activity()->withProperties($request->all())->log('Result Request');

        return response()->json(['ResultCode' => '0', 'ResultDesc' => 'Accepted']);
    }

    public function callback(Request $request)
    {
        activity()->withProperties($request->all())->log('Callback Request');

        $resultCode = data_get($request->all(), 'Body.stkCallback.ResultCode');

        if ($resultCode != 0) {

            return response()->json(['ResultCode' => '1', 'ResultDesc' => 'Error']);
        }

        $checkoutRequestID = data_get($request->all(), 'Body.stkCallback.CheckoutRequestID');

        $this->transactionService->markTransactionAsCompleted($checkoutRequestID);

        return response()->json(['ResultCode' => '0', 'ResultDesc' => 'Accepted']);
    }
}

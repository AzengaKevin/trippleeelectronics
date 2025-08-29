<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\PaymentMethodExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StorePaymentMethodRequest;
use App\Http\Requests\UpdatePaymentMethodRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\PaymentMethodImport;
use App\Models\Enums\PaymentMethodFieldOption;
use App\Models\Enums\PaymentStatus;
use App\Models\PaymentMethod;
use App\Services\ExcelService;
use App\Services\PaymentMethodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    public function __construct(
        private readonly PaymentMethodService $paymentMethodService
    ) {}

    public function index(Request $request)
    {

        $params = $request->only('query');

        $methods = $this->paymentMethodService->get(...$params);

        return inertia('backoffice/payment-methods/IndexPage', [
            'methods' => $methods,
            'params' => $params,
        ]);
    }

    public function create()
    {

        $fields = PaymentMethodFieldOption::labelledOptions();

        $statuses = PaymentStatus::labelledOptions();

        return inertia('backoffice/payment-methods/CreatePage', compact('fields', 'statuses'));
    }

    public function store(StorePaymentMethodRequest $storePaymentMethodRequest): RedirectResponse
    {
        $data = $storePaymentMethodRequest->validated();

        try {

            $paymentMethod = $this->paymentMethodService->create($data);

            return $this->sendSuccessRedirect("You have successfully created the payment method: {$paymentMethod->name}", route('backoffice.payment-methods.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('An error occurred while creating the payment method. Please try again later.', $throwable);
        }
    }

    public function show(PaymentMethod $method)
    {
        return inertia('backoffice/payment-methods/ShowPage', [
            'method' => $method,
        ]);
    }

    public function edit(PaymentMethod $method)
    {

        $fields = PaymentMethodFieldOption::labelledOptions();

        $statuses = PaymentStatus::labelledOptions();

        return inertia('backoffice/payment-methods/EditPage', [
            'method' => $method,
            'fields' => $fields,
            'statuses' => $statuses,
        ]);
    }

    public function update(UpdatePaymentMethodRequest $updatePaymentMethodRequest, PaymentMethod $method): RedirectResponse
    {
        $data = $updatePaymentMethodRequest->validated();

        try {

            $this->paymentMethodService->update($method, $data);

            return $this->sendSuccessRedirect("You have successfully updated the payment method: {$method->name}", route('backoffice.payment-methods.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('An error occurred while updating the payment method. Please try again later.', $throwable);
        }
    }

    public function destroy(PaymentMethod $method): RedirectResponse
    {
        try {

            $this->paymentMethodService->delete($method);

            return $this->sendSuccessRedirect("You have successfully deleted the payment method: {$method->name}", route('backoffice.payment-methods.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('An error occurred while deleting the payment method. Please try again later.', $throwable);
        }
    }

    public function export(Request $request)
    {
        $params = $request->only('query');

        $paymentMethodExport = new PaymentMethodExport($params);

        $filename = PaymentMethod::getExportFilename();

        return $paymentMethodExport->download($filename);
    }

    public function import(ImportRequest $importRequest): RedirectResponse
    {
        $data = $importRequest->validated();

        try {

            $file = $data['file'];

            $import = new PaymentMethodImport;

            $this->robustImport($import, $file, 'payment-methods', 'payment-methods');

            return $this->sendSuccessRedirect('You have successfully imported the payment methods.', route('backoffice.payment-methods.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('An error occurred while importing the payment methods. Please try again later.', $throwable);
        }
    }
}

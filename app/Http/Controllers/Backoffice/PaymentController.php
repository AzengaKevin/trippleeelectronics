<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\PaymentExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\PaymentImport;
use App\Models\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Store;
use App\Models\User;
use App\Services\ExcelService;
use App\Services\PaymentMethodService;
use App\Services\PaymentService;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    private ?User $currentUser = null;

    private ?Store $currentStore = null;

    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly StoreService $storeService,
        private readonly PaymentMethodService $paymentMethodService,
    ) {

        $this->currentUser = request()->user();

        $this->currentStore = $this->storeService->getUserStore($this->currentUser);
    }

    public function index(Request $request)
    {
        $params = $request->only('query', 'store');

        $filters = [...$params];

        if ($this->currentUser->hasRole('staff')) {

            $filters['store'] = $this->currentStore;
        }

        if (! is_null($storeId = data_get($filters, 'store')) && $this->currentUser->hasRole('admin')) {

            $filters['store'] = Store::query()->find($storeId);
        }

        $payments = $this->paymentService->get(...$filters, with: ['payer', 'author', 'paymentMethod', 'payee']);

        $stores = $this->storeService->get(perPage: null)->map(fn ($store) => [
            'id' => $store->id,
            'name' => $store->name,
        ]);

        return Inertia::render('backoffice/payments/IndexPage', [
            'payments' => $payments,
            'params' => $params,
            'stores' => $stores,
        ]);
    }

    public function create()
    {
        $methods = $this->paymentMethodService->get(perPage: null)->map(fn ($method) => [
            'value' => $method->id,
            'value' => $method->name,
        ]);

        $statuses = PaymentStatus::labelledOptions();

        return Inertia::render('backoffice/payments/CreatePage', compact(
            'methods',
            'statuses',
        ));
    }

    public function store(StorePaymentRequest $storePaymentRequest)
    {
        $data = $storePaymentRequest->validated();

        try {

            $data['author_user_id'] = request()->user()->id;

            $data['payment_method_id'] = data_get($data, 'payment_method');

            $this->paymentService->create($data);

            return $this->sendSuccessRedirect('Payment created successfully', route('backoffice.payments.index'));
        } catch (\Exception $exception) {

            return $this->sendErrorRedirect('Failed to create payment', $exception);
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to create payment', $throwable);
        }
    }

    public function show(Payment $payment)
    {
        $payment->load(['author', 'payer', 'payee', 'payable']);

        return Inertia::render('backoffice/payments/ShowPage', [
            'payment' => $payment,
        ]);
    }

    public function edit(Payment $payment)
    {
        $payment->load(['author', 'payer', 'payee', 'payable']);

        $methods = $this->paymentMethodService->get(perPage: null)->map(fn ($method) => [
            'value' => $method->id,
            'value' => $method->name,
        ]);

        $statuses = PaymentStatus::labelledOptions();

        return Inertia::render('backoffice/payments/EditPage', [
            'payment' => $payment,
            'methods' => $methods,
            'statuses' => $statuses,
        ]);
    }

    public function update(UpdatePaymentRequest $updatePaymentRequest, Payment $payment)
    {
        $data = $updatePaymentRequest->validated();

        $data['payment_method_id'] = data_get($data, 'payment_method');

        try {

            $this->paymentService->update($payment, $data);

            return $this->sendSuccessRedirect('Payment updated successfully', route('backoffice.payments.show', $payment));
        } catch (\Exception $exception) {

            return $this->sendErrorRedirect('Failed to update payment', $exception);
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to update payment', $throwable);
        }
    }

    public function destroy(string $payment)
    {
        try {

            $payment = Payment::query()->withTrashed()->findOrFail($payment);

            $this->paymentService->delete($payment, request()->boolean('forever'));

            return $this->sendSuccessRedirect('Payment deleted successfully', route('backoffice.payments.index'));
        } catch (\Exception $exception) {

            return $this->sendErrorRedirect('Failed to delete payment', $exception);
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to delete payment', $throwable);
        }
    }

    public function import()
    {
        return Inertia::render('backoffice/payments/ImportPage');
    }

    public function export(Request $request)
    {
        $params = $request->only('query');

        $paymentExport = new PaymentExport($params);

        $filename = Payment::getExportFilename();

        return $paymentExport->download($filename);
    }

    public function processImport(ImportRequest $importRequest): RedirectResponse
    {
        $data = $importRequest->validated();

        try {

            $file = data_get($data, 'file');

            $this->robustImport(new PaymentImport, $file, 'payments', 'payments');

            return $this->sendSuccessRedirect('Payments imported successfully', route('backoffice.payments.index'));
        } catch (\Exception $exception) {

            return $this->sendErrorRedirect('Failed to import payments', $exception);
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to import payments', $throwable);
        }
    }
}

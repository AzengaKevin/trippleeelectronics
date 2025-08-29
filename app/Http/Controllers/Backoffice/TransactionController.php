<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\TransactionExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\TransactionImport;
use App\Models\Enums\TransactionMethod;
use App\Models\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Services\ExcelService;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    public function __construct(private readonly TransactionService $transactionService) {}

    public function index(Request $request): Response
    {
        $params = $request->only('query', 'status', 'method');

        $transactions = $this->transactionService->get(...$params, with: ['author', 'payment']);

        $methods = TransactionMethod::labelledOptions();

        $statuses = TransactionStatus::labelledOptions();

        return Inertia::render('backoffice/transactions/IndexPage', [
            'transactions' => $transactions,
            'statuses' => $statuses,
            'methods' => $methods,
            'params' => $params,
        ]);
    }

    public function create(): Response
    {
        $methods = TransactionMethod::labelledOptions();

        $statuses = TransactionStatus::labelledOptions();

        return Inertia::render('backoffice/transactions/CreatePage', [
            'methods' => $methods,
            'statuses' => $statuses,
        ]);
    }

    public function store(StoreTransactionRequest $storeTransactionRequest): RedirectResponse
    {
        $data = $storeTransactionRequest->validated();

        try {

            $data['author_user_id'] = request()->user()->id;

            $this->transactionService->create($data);

            return $this->sendSuccessRedirect('Transaction created successfully', route('backoffice.transactions.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Transaction creation failed', $throwable);
        }
    }

    public function show(Transaction $transaction): Response
    {
        $transaction->load(['author', 'payment']);

        return Inertia::render('backoffice/transactions/ShowPage', [
            'transaction' => $transaction,
        ]);
    }

    public function edit(Transaction $transaction): Response
    {
        $transaction->load(['author', 'payment']);

        $methods = TransactionMethod::labelledOptions();

        $statuses = TransactionStatus::labelledOptions();

        return Inertia::render('backoffice/transactions/EditPage', [
            'transaction' => $transaction,
            'methods' => $methods,
            'statuses' => $statuses,
        ]);
    }

    public function update(UpdateTransactionRequest $updateTransactionRequest, Transaction $transaction): RedirectResponse
    {
        $data = $updateTransactionRequest->validated();

        try {

            $this->transactionService->update($transaction, $data);

            return $this->sendSuccessRedirect('Transaction updated successfully', route('backoffice.transactions.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Transaction update failed', $throwable);
        }
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        try {

            $this->transactionService->delete($transaction, request()->boolean('forever'));

            return $this->sendSuccessRedirect('Transaction deleted successfully', route('backoffice.transactions.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Transaction deletion failed', $throwable);
        }
    }

    public function export(Request $request)
    {
        $params = $request->only('query', 'status', 'method');

        $transactionExport = new TransactionExport($params);

        $filename = Transaction::getExportFilename();

        return $transactionExport->download($filename);
    }

    public function import(): Response
    {
        return Inertia::render('backoffice/transactions/ImportPage');
    }

    public function processImport(ImportRequest $importRequest): RedirectResponse
    {
        $data = $importRequest->validated();

        try {

            $file = data_get($data, 'file');

            $this->robustImport(new TransactionImport, $file, 'transactions', 'transactions');

            return $this->sendSuccessRedirect('Transaction import successfully', route('backoffice.transactions.index'));

        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Transaction import failed', $throwable);
        }
    }
}

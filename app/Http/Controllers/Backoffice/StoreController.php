<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\StoreExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StoreStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\StoreImport;
use App\Models\Store;
use App\Services\ExcelService;
use App\Services\PaymentMethodService;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StoreController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    public function __construct(
        private readonly StoreService $storeService,
        private readonly PaymentMethodService $paymentMethodService,
    ) {}

    public function index(Request $request)
    {
        $params = $request->only('query', 'perPage');

        $stores = $this->storeService->get(...$params);

        return Inertia::render('backoffice/stores/IndexPage', compact('stores', 'params'));
    }

    public function create()
    {
        $methods = $this->paymentMethodService->get(perPage: null);

        return Inertia::render('backoffice/stores/CreatePage', compact('methods'));
    }

    public function store(StoreStoreRequest $storeStoreRequest): RedirectResponse
    {
        $data = $storeStoreRequest->validated();

        $data['author_user_id'] = request()->user()->id;

        try {
            $this->storeService->create($data);

            return $this->sendSuccessRedirect('Store created successfully.', route('backoffice.stores.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Store creation failed.', $throwable);
        }
    }

    public function show(Store $store)
    {

        $store->load(['paymentMethods']);

        return Inertia::render('backoffice/stores/ShowPage', compact('store'));
    }

    public function edit(Store $store)
    {
        $store->load(['paymentMethods']);

        $methods = $this->paymentMethodService->get(perPage: null);

        return Inertia::render('backoffice/stores/EditPage', compact('store', 'methods'));
    }

    public function update(UpdateStoreRequest $updateStoreRequest, Store $store): RedirectResponse
    {
        $data = $updateStoreRequest->validated();

        try {
            $this->storeService->update($store, $data);

            return $this->sendSuccessRedirect('Store updated successfully.', route('backoffice.stores.show', $store));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Store update failed.', $throwable);
        }
    }

    public function destroy(Request $request, string $store): RedirectResponse
    {
        try {
            $store = Store::findOrFail($store);

            $this->storeService->delete($store, $request->boolean('forever'));

            return $this->sendSuccessRedirect('Store deleted successfully.', route('backoffice.stores.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Store deletion failed.', $throwable);
        }
    }

    public function export(Request $request)
    {
        $data = $request->only('query', 'limit');

        $storeExport = new StoreExport($data);

        return $storeExport->download(Store::getExportFilename());
    }

    public function import()
    {
        return Inertia::render('backoffice/stores/ImportPage');
    }

    public function processImport(ImportRequest $importRequest): RedirectResponse
    {
        $data = $importRequest->validated();

        try {
            $this->robustImport(new StoreImport, $data['file'], 'stores', 'stores');

            return $this->sendSuccessRedirect('Imported stores', route('backoffice.stores.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Failed to import stores', $throwable);
        }
    }
}
